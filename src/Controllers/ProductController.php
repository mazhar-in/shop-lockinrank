<?php

namespace App\Controllers;

use Flight;
use App\Database;

class ProductController
{
    public static function show($slug)
    {
        $db = Database::getConnection();

        $stmt = $db->prepare("
            SELECT p.*, s.name as store_name, s.avatar as store_avatar, s.slug as store_slug, s.author_name, s.bio as store_bio, s.rating as store_rating
            FROM products p
            JOIN stores s ON p.store_id = s.id
            WHERE p.slug = ?
        ");
        $stmt->execute([$slug]);
        $product = $stmt->fetch();

        if (!$product) {
            Flight::notFound();
            return;
        }

        // Get reviews
        $revStmt = $db->prepare("SELECT * FROM reviews WHERE product_id = ? ORDER BY created_at DESC");
        $revStmt->execute([$product['id']]);
        $reviews = $revStmt->fetchAll();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Generate Math Challenge for Anti-Spam verification
        $captchaN1 = rand(2, 9);
        $captchaN2 = rand(1, 9);
        $_SESSION['review_captcha_num1'] = $captchaN1;
        $_SESSION['review_captcha_num2'] = $captchaN2;
        $_SESSION['review_captcha_answer'] = $captchaN1 + $captchaN2;
        $_SESSION['review_form_time'] = time();
        if (empty($_SESSION['review_token'])) {
            $_SESSION['review_token'] = bin2hex(random_bytes(16));
        }

        // Calculate rating breakdown (counts of 5, 4, 3, 2, 1 stars)
        $starBreakdown = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        foreach ($reviews as $r) {
            $val = (int)($r['rating'] ?? 5);
            if (isset($starBreakdown[$val])) {
                $starBreakdown[$val]++;
            }
        }

        // Get related products prioritizing the same course / exam
        $related = [];
        try {
            $relStmt = $db->prepare("
                SELECT p.*, s.name as store_name,
                       CASE 
                           WHEN p.grade = :grade1 AND p.board = :board1 THEN 4
                           WHEN p.grade = :grade2 THEN 3
                           WHEN p.board = :board2 THEN 2
                           WHEN p.subject = :subject1 THEN 1
                           ELSE 0
                       END as relevance
                FROM products p 
                JOIN stores s ON p.store_id = s.id
                WHERE p.id != :prod_id
                ORDER BY relevance DESC, p.sales_count DESC, p.rating DESC
                LIMIT 4
            ");
            $relStmt->execute([
                ':grade1'   => $product['grade'] ?? '',
                ':board1'   => $product['board'] ?? '',
                ':grade2'   => $product['grade'] ?? '',
                ':board2'   => $product['board'] ?? '',
                ':subject1' => $product['subject'] ?? '',
                ':prod_id'  => (int)$product['id']
            ]);
            $related = $relStmt->fetchAll();
        } catch (\Throwable $relErr) {
            error_log("Related products query error: " . $relErr->getMessage());
            try {
                $fallback = $db->prepare("SELECT p.*, s.name as store_name FROM products p JOIN stores s ON p.store_id = s.id WHERE p.id != ? LIMIT 4");
                $fallback->execute([(int)$product['id']]);
                $related = $fallback->fetchAll();
            } catch (\Throwable $fbErr) {
                $related = [];
            }
        }

        $previewImages = json_decode($product['preview_images'] ?? '[]', true) ?: [];
        if (!empty($product['thumbnail_url']) && !in_array($product['thumbnail_url'], $previewImages)) {
            array_unshift($previewImages, $product['thumbnail_url']);
        }
        if (empty($previewImages)) {
            $previewImages = ['https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=600&auto=format&fit=crop&q=80'];
        }

        Flight::render('product-detail', [
            'product' => $product,
            'reviews' => $reviews,
            'related' => $related,
            'previewImages' => array_values(array_filter($previewImages)),
            'captchaNum1' => $captchaN1,
            'captchaNum2' => $captchaN2,
            'starBreakdown' => $starBreakdown
        ]);
    }

    public static function submitReview()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $req = Flight::request()->data;
        $productId = (int)($req['product_id'] ?? 0);
        $slug = trim($req['product_slug'] ?? '');
        $redirectUrl = !empty($slug) ? "/product/{$slug}#reviews" : "/";

        $db = Database::getConnection();

        // Check product exists
        $stmt = $db->prepare("SELECT id, slug FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        if (!$product) {
            $_SESSION['review_flash_error'] = "Product not found.";
            Flight::redirect('/');
            return;
        }

        // --- SPAM PROTECTION 1: Honeypot Trap ---
        if (!empty($req['hp_website'])) {
            error_log("Spam blocked: Honeypot field filled for product #{$productId}");
            $_SESSION['review_flash_error'] = "Spam submission detected.";
            Flight::redirect($redirectUrl);
            return;
        }

        // --- SPAM PROTECTION 2: Time-Trap (Minimum 3 seconds between page view and submission) ---
        $formTime = (int)($_SESSION['review_form_time'] ?? 0);
        if ($formTime <= 0 || (time() - $formTime) < 3) {
            $_SESSION['review_flash_error'] = "Review submitted too quickly. Please take a moment to write your review.";
            Flight::redirect($redirectUrl);
            return;
        }

        // --- SPAM PROTECTION 3: Anti-Spam Math Challenge Verification ---
        $expectedAnswer = (int)($_SESSION['review_captcha_answer'] ?? -999);
        $userAnswer = (int)trim($req['captcha_answer'] ?? '');
        if ($userAnswer !== $expectedAnswer) {
            $_SESSION['review_flash_error'] = "Verification failed: The math answer was incorrect. Please try again.";
            Flight::redirect($redirectUrl);
            return;
        }

        // --- SPAM PROTECTION 4: Flood Control / Rate Limiting (60s between reviews) ---
        $lastSubmit = (int)($_SESSION['last_review_submit_time'] ?? 0);
        if ($lastSubmit > 0 && (time() - $lastSubmit) < 60) {
            $_SESSION['review_flash_error'] = "You recently submitted a review. Please wait 60 seconds before posting another.";
            Flight::redirect($redirectUrl);
            return;
        }

        // --- SPAM PROTECTION 5: Link, Keyword & Blacklist Filtering ---
        $authorName = trim(strip_tags($req['author_name'] ?? ''));
        $comment = trim(strip_tags($req['comment'] ?? ''));
        $role = trim(strip_tags($req['author_role'] ?? 'Verified Student'));
        $rating = (int)($req['rating'] ?? 5);

        $spamPattern = '/https?:\/\/|www\.|t\.me\/|wa\.me\/|\.xyz|\.top|\.ru|free\s*crypto|casino|viagra|porn|bit\.ly/i';
        if (preg_match($spamPattern, $comment) || preg_match($spamPattern, $authorName)) {
            $_SESSION['review_flash_error'] = "Reviews containing external links, promotional URLs, or spam phrases are prohibited.";
            Flight::redirect($redirectUrl);
            return;
        }

        // Name validation
        if (mb_strlen($authorName) < 2 || mb_strlen($authorName) > 60) {
            $_SESSION['review_flash_error'] = "Please provide a valid name between 2 and 60 characters.";
            Flight::redirect($redirectUrl);
            return;
        }

        // Comment validation
        if (mb_strlen($comment) < 10) {
            $_SESSION['review_flash_error'] = "Review feedback is too short. Please write at least 10 characters.";
            Flight::redirect($redirectUrl);
            return;
        }
        if (mb_strlen($comment) > 1000) {
            $_SESSION['review_flash_error'] = "Review feedback exceeds the 1,000 character maximum limit.";
            Flight::redirect($redirectUrl);
            return;
        }

        // Clamp rating 1 to 5
        if ($rating < 1) $rating = 1;
        if ($rating > 5) $rating = 5;

        // Role clamp
        $allowedRoles = ['Verified Student', 'Verified Educator', 'Verified Parent'];
        if (!in_array($role, $allowedRoles, true)) {
            $role = 'Verified Student';
        }

        try {
            $now = date('Y-m-d H:i:s');
            // Save review
            $ins = $db->prepare("
                INSERT INTO reviews (product_id, author_name, author_role, rating, comment, created_at)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $ins->execute([$productId, $authorName, $role, $rating, $comment, $now]);

            // Recompute product rating & review_count
            $agg = $db->prepare("SELECT COUNT(*) as cnt, AVG(rating) as avg_r FROM reviews WHERE product_id = ?");
            $agg->execute([$productId]);
            $stats = $agg->fetch();
            $newCount = (int)($stats['cnt'] ?? 0);
            $newAvg = round((float)($stats['avg_r'] ?? 5.0), 1);

            $upd = $db->prepare("UPDATE products SET rating = ?, review_count = ? WHERE id = ?");
            $upd->execute([$newAvg, $newCount, $productId]);

            // Update store total_reviews if store exists
            try {
                $storeUpd = $db->prepare("
                    UPDATE stores SET 
                        total_reviews = (SELECT COUNT(*) FROM reviews r JOIN products p ON r.product_id = p.id WHERE p.store_id = stores.id),
                        rating = ROUND((SELECT AVG(r.rating) FROM reviews r JOIN products p ON r.product_id = p.id WHERE p.store_id = stores.id), 2)
                    WHERE id = (SELECT store_id FROM products WHERE id = ?)
                ");
                $storeUpd->execute([$productId]);
            } catch (\Throwable $storeErr) {}

            // Set rate limit timestamp & reset captcha
            $_SESSION['last_review_submit_time'] = time();
            unset($_SESSION['review_captcha_answer']);

            $_SESSION['review_flash_success'] = "Thank you! Your verified review has been posted successfully.";
        } catch (\Throwable $e) {
            error_log("Failed to insert review: " . $e->getMessage());
            $_SESSION['review_flash_error'] = "An error occurred while saving your review. Please try again.";
        }

        Flight::redirect($redirectUrl);
    }

    public static function quickPreview($id)
    {
        $db = Database::getConnection();

        $stmt = $db->prepare("
            SELECT p.*, s.name as store_name, s.author_name 
            FROM products p
            JOIN stores s ON p.store_id = s.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $product = $stmt->fetch();

        if (!$product) {
            Flight::halt(404, 'Product not found');
            return;
        }

        $previewImages = json_decode($product['preview_images'] ?? '[]', true) ?: [];
        if (!empty($product['thumbnail_url']) && !in_array($product['thumbnail_url'], $previewImages)) {
            array_unshift($previewImages, $product['thumbnail_url']);
        }
        if (empty($previewImages)) {
            $previewImages = ['https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=600&auto=format&fit=crop&q=80'];
        }

        Flight::render('partials/preview-modal', [
            'product' => $product,
            'previewImages' => array_values(array_filter($previewImages))
        ]);
    }

    public static function downloadSample($id)
    {
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([(int)$id]);
        $product = $stmt->fetch();

        if (!$product) {
            Flight::halt(404, 'Product not found');
            return;
        }

        // Increment sample downloads counter in database
        $updateStmt = $db->prepare("UPDATE products SET sample_downloads = COALESCE(sample_downloads, 0) + 1 WHERE id = ?");
        $updateStmt->execute([(int)$id]);

        // If a real sample file was uploaded (PDF, ZIP, DOC, DOCX)
        if (!empty($product['sample_file_path'])) {
            $filePath = __DIR__ . '/../../storage/samples/' . basename($product['sample_file_path']);
            if (file_exists($filePath)) {
                $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                $mimeTypes = [
                    'pdf'  => 'application/pdf',
                    'zip'  => 'application/zip',
                    'doc'  => 'application/msword',
                    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                ];
                $contentType = $mimeTypes[$ext] ?? 'application/octet-stream';
                $safeTitle = preg_replace('/[^a-zA-Z0-9_-]/', '_', $product['title']);
                $downloadName = "Lockinrank_Sample_" . $safeTitle . "." . $ext;

                header('Content-Type: ' . $contentType);
                header('Content-Disposition: attachment; filename="' . $downloadName . '"');
                header('Content-Length: ' . filesize($filePath));
                header('Cache-Control: private, max-age=0, must-revalidate');
                header('Pragma: public');
                readfile($filePath);
                exit;
            }
        }

        $safeTitle = preg_replace('/[^a-zA-Z0-9_-]/', '_', $product['title']);
        $filename = "Lockinrank_Sample_" . $safeTitle . ".txt";

        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo "===================================================================\n";
        echo " LOCKINRANK SHOP (shop.lockinrank.com) - FREE SAMPLE RESOURCE\n";
        echo "===================================================================\n\n";
        echo "Resource:        " . $product['title'] . "\n";
        echo "Target Exam:     " . $product['grade'] . "\n";
        echo "Board / Pattern: " . $product['board'] . "\n";
        echo "Subject:         " . $product['subject'] . "\n";
        echo "Curriculum Ref:  " . ($product['standards'] ?? 'N/A') . "\n";
        echo "Total Pages:     " . $product['page_count'] . " Pages in Full Edition\n";
        echo "Price in INR:    ₹" . number_format($product['price'], 0) . " (Full Instant Access)\n";
        echo "Status:          SAMPLE EXTRACT - 100% Free Verification Copy\n\n";
        echo "-------------------------------------------------------------------\n";
        echo " SAMPLE QUESTIONS & OVERVIEW\n";
        echo "-------------------------------------------------------------------\n\n";
        echo "Description:\n" . $product['description'] . "\n\n";
        echo "[SAMPLE QUESTION SET - EXCERPT]\n";
        echo "1. Conceptual Drill: Analyze the scenario described in Section A and identify\n";
        echo "   the primary conservation laws applicable under standard boundary conditions.\n";
        echo "2. Multi-Correct / FRQ Drill: Determine the critical parameters governing\n";
        echo "   stability and derive the analytical expression for maximum amplitude.\n";
        echo "3. Step-by-Step Solution Methodology: See full answer keys with detailed derivations\n";
        echo "   in the complete " . $product['page_count'] . "-page workbook package.\n\n";
        echo "-------------------------------------------------------------------\n";
        echo " HOW TO UNLOCK THE COMPLETE " . $product['page_count'] . "-PAGE WORKBOOK:\n";
        echo "-------------------------------------------------------------------\n";
        echo "Visit: https://shop.lockinrank.com/product/" . $product['slug'] . "\n";
        echo "Instant UPI, QR code, RuPay & card payment via Razorpay.\n";
        echo "Your complete printable PDF will unlock immediately after checkout.\n\n";
        echo "Academic Support: support@lockinrank.com\n";
        echo "All the best with your preparation!\n";
        exit;
    }
}
