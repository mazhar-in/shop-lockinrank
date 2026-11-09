<?php

namespace App\Controllers;

use Flight;
use App\Database;
use App\Services\RazorpayService;
use App\Services\MailService;
use App\Services\WhatsAppService;

class CartController
{
    private static function initCart()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    public static function add()
    {
        self::initCart();
        $db = Database::getConnection();

        $productId = (int)(Flight::request()->data['product_id'] ?? 0);

        $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if ($product) {
            $_SESSION['cart'][$productId] = [
                'id' => $product['id'],
                'title' => $product['title'],
                'price' => (float)$product['price'],
                'is_free' => (bool)$product['is_free'],
                'board' => $product['board'] ?? 'CBSE',
                'grade' => $product['grade'],
                'thumbnail_url' => $product['thumbnail_url'],
                'file_format' => $product['file_format']
            ];
        }

        if (!empty($_SERVER['HTTP_HX_REQUEST'])) {
            $count = count($_SESSION['cart']);
            echo '<a href="/cart" id="nav-cart-btn" class="nav-cart-btn cart-updated" title="View Shopping Cart (' . $count . ')">'
               . '<i class="fa-solid fa-cart-shopping"></i> Cart (' . $count . ')'
               . '</a>';
            echo '<span id="mobileBottomCartBadge" hx-swap-oob="true" class="mobile-cart-badge">' . $count . '</span>';
            return;
        }

        Flight::redirect('/cart');
    }

    public static function remove($id)
    {
        self::initCart();
        unset($_SESSION['cart'][$id]);
        Flight::redirect('/cart');
    }

    public static function view()
    {
        self::initCart();
        $items = $_SESSION['cart'];

        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['price'];
        }

        Flight::render('cart', [
            'items' => $items,
            'subtotal' => $subtotal,
            'razorpayKey' => RazorpayService::getKeyId()
        ]);
    }

    /**
     * Endpoint: POST /cart/initiate-payment
     * Creates an order and initializes Razorpay checkout (or fulfills directly if ₹0 freebie)
     */
    public static function initiatePayment()
    {
        self::initCart();
        $items = $_SESSION['cart'];

        if (empty($items)) {
            Flight::json(['status' => 'error', 'message' => 'Your cart is empty.']);
            return;
        }

        $db = Database::getConnection();

        $buyerName = trim(Flight::request()->data['buyer_name'] ?? '');
        $buyerEmail = trim(Flight::request()->data['buyer_email'] ?? '');
        $buyerPhone = trim(Flight::request()->data['buyer_phone'] ?? '');

        // Mandatory checkout validation
        if (empty($buyerName) || mb_strlen($buyerName) < 2) {
            Flight::json(['status' => 'error', 'message' => 'Please enter your full name.'], 400);
            return;
        }

        if (empty($buyerEmail) || !filter_var($buyerEmail, FILTER_VALIDATE_EMAIL)) {
            Flight::json(['status' => 'error', 'message' => 'Please enter a valid email address.'], 400);
            return;
        }

        $phoneDigits = preg_replace('/[^0-9]/', '', $buyerPhone);
        if (empty($buyerPhone) || strlen($phoneDigits) < 10) {
            Flight::json(['status' => 'error', 'message' => 'Please enter a valid 10-digit WhatsApp/mobile number.'], 400);
            return;
        }

        $total = 0;
        foreach ($items as $item) {
            $total += (float)$item['price'];
        }

        $orderNumber = 'IN-ORD-' . strtoupper(substr(uniqid(), 7));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));

        // SCENARIO A: 100% Freebies (Zero payment required)
        if ($total <= 0) {
            try {
                $orderStmt = $db->prepare("
                    INSERT INTO orders (order_number, buyer_name, buyer_email, buyer_phone, payment_method, payment_status, total_amount)
                    VALUES (?, ?, ?, ?, 'Free Download', 'paid', 0)
                ");
                $orderStmt->execute([$orderNumber, $buyerName, $buyerEmail, $buyerPhone]);
                $orderId = $db->lastInsertId();

                $itemStmt = $db->prepare("
                    INSERT INTO order_items (order_id, product_id, price, download_token, expires_at)
                    VALUES (?, ?, ?, ?, ?)
                ");

                $orderItems = [];
                foreach ($items as $item) {
                    $token = bin2hex(random_bytes(16));
                    $itemStmt->execute([$orderId, $item['id'], 0, $token, $expiresAt]);
                    $db->prepare("UPDATE products SET sales_count = sales_count + 1 WHERE id = ?")->execute([$item['id']]);
                    $orderItems[] = [
                        'title' => $item['title'],
                        'file_format' => $item['file_format'] ?? 'PDF',
                        'download_token' => $token,
                        'price' => 0,
                        'expires_at' => $expiresAt
                    ];
                }

                // Automated Email Receipt via Hostinger SMTP
                $orderRecord = [
                    'order_number' => $orderNumber,
                    'buyer_name' => $buyerName,
                    'buyer_email' => $buyerEmail,
                    'buyer_phone' => $buyerPhone,
                    'payment_method' => 'Free Download',
                    'total_amount' => 0
                ];
                try {
                    MailService::sendOrderReceipt($orderRecord, $orderItems);
                } catch (\Throwable $mailErr) {
                    error_log("MailService freebie receipt error: " . $mailErr->getMessage());
                }

                $_SESSION['cart'] = [];

                Flight::json([
                    'status' => 'free',
                    'redirect_url' => '/order/success/' . $orderNumber
                ]);
                return;
            } catch (\Throwable $e) {
                error_log("Freebie checkout error: " . $e->getMessage());
                Flight::json([
                    'status' => 'error',
                    'message' => 'Free checkout failed: ' . $e->getMessage()
                ], 500);
                return;
            }
        }

        // SCENARIO B: Paid order requiring Razorpay standard checkout
        try {
            $rzpOrder = RazorpayService::createOrder($total, $orderNumber, [
                'internal_order' => $orderNumber,
                'buyer_email' => $buyerEmail,
                'buyer_phone' => $buyerPhone
            ]);

            // Save pending order in database
            $orderStmt = $db->prepare("
                INSERT INTO orders 
                (order_number, buyer_name, buyer_email, buyer_phone, payment_method, razorpay_order_id, payment_status, total_amount)
                VALUES (?, ?, ?, ?, 'Razorpay', ?, 'pending', ?)
            ");
            $orderStmt->execute([$orderNumber, $buyerName, $buyerEmail, $buyerPhone, $rzpOrder['id'], $total]);
            $orderId = $db->lastInsertId();

            $itemStmt = $db->prepare("
                INSERT INTO order_items (order_id, product_id, price, download_token, expires_at)
                VALUES (?, ?, ?, ?, ?)
            ");

            foreach ($items as $item) {
                $token = bin2hex(random_bytes(16));
                $itemStmt->execute([$orderId, $item['id'], $item['price'], $token, $expiresAt]);
            }

            Flight::json([
                'status' => 'payment_required',
                'key' => RazorpayService::getKeyId(),
                'amount' => $rzpOrder['amount'],
                'currency' => $rzpOrder['currency'],
                'order_id' => $rzpOrder['id'],
                'internal_order_number' => $orderNumber,
                'name' => 'Lockinrank Shop',
                'description' => 'Academic Resource & Test Series License',
                'prefill' => [
                    'name' => $buyerName,
                    'email' => $buyerEmail,
                    'contact' => $buyerPhone
                ],
                'theme' => [
                    'color' => '#4f46e5'
                ]
            ]);

        } catch (\Exception $e) {
            Flight::json([
                'status' => 'error',
                'message' => 'Could not initialize Razorpay payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Endpoint: POST /cart/verify-payment
     * Verifies HMAC signature after Razorpay Checkout modal completes
     */
    public static function verifyPayment()
    {
        self::initCart();
        $db = Database::getConnection();

        $razorpayOrderId = trim(Flight::request()->data['razorpay_order_id'] ?? '');
        $razorpayPaymentId = trim(Flight::request()->data['razorpay_payment_id'] ?? '');
        $razorpaySignature = trim(Flight::request()->data['razorpay_signature'] ?? '');
        $internalOrderNumber = trim(Flight::request()->data['internal_order_number'] ?? '');

        if (empty($razorpayOrderId) || empty($razorpayPaymentId) || empty($razorpaySignature)) {
            Flight::json(['status' => 'error', 'message' => 'Missing Razorpay verification parameters.'], 400);
            return;
        }

        // Verify HMAC-SHA256 signature
        $isValid = RazorpayService::verifyPayment($razorpayOrderId, $razorpayPaymentId, $razorpaySignature);

        if (!$isValid) {
            $stmt = $db->prepare("UPDATE orders SET payment_status = 'failed' WHERE order_number = ?");
            $stmt->execute([$internalOrderNumber]);

            Flight::json([
                'status' => 'error',
                'message' => 'Payment signature verification failed. Please contact support.'
            ], 400);
            return;
        }

        // Payment is verified! Update database
        $stmt = $db->prepare("
            UPDATE orders 
            SET payment_status = 'paid',
                payment_method = 'Razorpay (Verified)',
                razorpay_payment_id = ?,
                razorpay_signature = ?
            WHERE order_number = ?
        ");
        $stmt->execute([$razorpayPaymentId, $razorpaySignature, $internalOrderNumber]);

        // Increment sales count for each product in this order & fetch full order details for receipt
        $orderStmt = $db->prepare("SELECT * FROM orders WHERE order_number = ?");
        $orderStmt->execute([$internalOrderNumber]);
        $order = $orderStmt->fetch();

        if ($order) {
            $itemsStmt = $db->prepare("
                SELECT oi.*, p.title, p.file_format 
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?
            ");
            $itemsStmt->execute([$order['id']]);
            $orderItems = $itemsStmt->fetchAll();

            foreach ($orderItems as $oi) {
                $db->prepare("UPDATE products SET sales_count = sales_count + 1 WHERE id = ?")->execute([$oi['product_id']]);
            }

            // Automated Email Receipt via Hostinger SMTP
            try {
                MailService::sendOrderReceipt($order, $orderItems);
            } catch (\Throwable $mailErr) {
                error_log("MailService paid receipt error: " . $mailErr->getMessage());
            }
        }

        // Clear session cart
        $_SESSION['cart'] = [];

        Flight::json([
            'status' => 'success',
            'redirect_url' => '/order/success/' . $internalOrderNumber
        ]);
    }

    /**
     * Endpoint: GET /order/success/@orderNumber
     */
    public static function orderSuccess($orderNumber)
    {
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ?");
        $stmt->execute([$orderNumber]);
        $order = $stmt->fetch();

        if (!$order) {
            Flight::notFound();
            return;
        }

        $itemsStmt = $db->prepare("
            SELECT oi.*, p.title, p.file_format, p.board, p.grade 
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $itemsStmt->execute([$order['id']]);
        $downloads = $itemsStmt->fetchAll();

        $domain = getenv('STORE_DOMAIN') ?: (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'shop.lockinrank.com');
        $baseUrl = str_contains($domain, 'localhost') ? "http://{$domain}" : "https://{$domain}";
        $whatsappUrl = WhatsAppService::generateClickToChatUrl($order, $downloads, $baseUrl);

        Flight::render('order-success', [
            'orderNumber' => $order['order_number'],
            'buyerName' => $order['buyer_name'],
            'buyerEmail' => $order['buyer_email'],
            'buyerPhone' => $order['buyer_phone'],
            'paymentMethod' => $order['payment_method'],
            'razorpayPaymentId' => $order['razorpay_payment_id'] ?? null,
            'total' => $order['total_amount'],
            'downloads' => $downloads,
            'whatsappUrl' => $whatsappUrl
        ]);
    }

    public static function download($token)
    {
        $db = Database::getConnection();

        $stmt = $db->prepare("
            SELECT oi.*, p.title, p.file_format, p.board, p.grade, p.material_file_path, 
                   o.order_number, o.buyer_name, o.buyer_email, o.buyer_phone, o.razorpay_payment_id
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            JOIN orders o ON oi.order_id = o.id
            WHERE oi.download_token = ?
        ");
        $stmt->execute([$token]);
        $item = $stmt->fetch();

        if (!$item) {
            Flight::halt(403, 'Invalid download link.');
            return;
        }

        // Check 7-day expiration
        if (!empty($item['expires_at']) && strtotime($item['expires_at']) < time()) {
            Flight::render('pages/download-expired', [
                'item' => $item,
                'orderNumber' => $item['order_number'] ?? '',
                'title' => $item['title'] ?? 'Requested Material',
                'expiredAt' => $item['expires_at'],
                'buyerName' => $item['buyer_name'] ?? 'Student',
                'buyerEmail' => $item['buyer_email'] ?? '',
                'buyerPhone' => $item['buyer_phone'] ?? '',
                'pageTitle' => 'Download Link Expired — Lockinrank Shop'
            ]);
            return;
        }

        $db->prepare("UPDATE order_items SET download_count = download_count + 1 WHERE id = ?")->execute([$item['id']]);

        // If a real material file was uploaded (PDF, ZIP, DOC, DOCX)
        if (!empty($item['material_file_path'])) {
            $filePath = __DIR__ . '/../../storage/materials/' . basename($item['material_file_path']);
            if (file_exists($filePath)) {
                $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                $mimeTypes = [
                    'pdf'  => 'application/pdf',
                    'zip'  => 'application/zip',
                    'doc'  => 'application/msword',
                    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                ];
                $contentType = $mimeTypes[$ext] ?? 'application/octet-stream';
                $safeTitle = preg_replace('/[^a-zA-Z0-9_-]/', '_', $item['title']);
                $downloadName = "Lockinrank_" . $safeTitle . "." . $ext;

                header('Content-Type: ' . $contentType);
                header('Content-Disposition: attachment; filename="' . $downloadName . '"');
                header('Content-Length: ' . filesize($filePath));
                header('Cache-Control: private, max-age=0, must-revalidate');
                header('Pragma: public');
                readfile($filePath);
                exit;
            }
        }

        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $item['title']) . '_PrintableResource.txt"');
        echo "===================================================================\n";
        echo " LOCKINRANK SHOP (shop.lockinrank.com) - DIGITAL RESOURCE DOWNLOAD\n";
        echo "===================================================================\n\n";
        echo "Resource:       " . $item['title'] . "\n";
        echo "Target Exam:    " . ($item['board'] ?? 'JEE / NTA Pattern') . "\n";
        echo "Target Course:  " . $item['grade'] . "\n";
        echo "Format:         " . $item['file_format'] . "\n";
        echo "Order Ref:      " . $item['order_number'] . "\n";
        if (!empty($item['razorpay_payment_id'])) {
            echo "Razorpay ID:    " . $item['razorpay_payment_id'] . "\n";
        }
        echo "Security Check: Verified Signed URL - Access Confirmed\n\n";
        echo "License:\n";
        echo "Thank you for trusting Lockinrank (shop.lockinrank.com)!\n";
        echo "This purchase confers a single-student / single-classroom license.\n";
        echo "For support or questions, reach us at support@lockinrank.com\n\n";
        echo "All the best with your preparation!\n";
        exit;
    }
}
