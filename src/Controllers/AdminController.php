<?php

namespace App\Controllers;

use Flight;
use App\Database;
use App\Services\ImageOptimizer;

class AdminController
{
    private static function startSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function getConfig(): array
    {
        return require __DIR__ . '/../../config/admin.php';
    }

    public static function isAuthenticated(): bool
    {
        self::startSession();
        $config = self::getConfig();
        $key = $config['session_key'] ?? 'lockinrank_admin_auth';

        if (!isset($_SESSION[$key]) || $_SESSION[$key] !== true) {
            return false;
        }

        // Check session timeout (default: 24h)
        $timeout = $config['session_timeout'] ?? 86400;
        if (isset($_SESSION['admin_login_time']) && (time() - $_SESSION['admin_login_time'] > $timeout)) {
            self::logout();
            return false;
        }

        return true;
    }

    public static function checkAuth(): void
    {
        if (!self::isAuthenticated()) {
            Flight::redirect('/admin/login');
            exit;
        }
    }

    public static function loginForm()
    {
        self::startSession();
        if (self::isAuthenticated()) {
            Flight::redirect('/admin');
            return;
        }

        $error = $_SESSION['admin_login_error'] ?? null;
        unset($_SESSION['admin_login_error']);

        Flight::render('admin/login', [
            'error' => $error,
            'pageTitle' => 'Admin Portal Login — Lockinrank Shop'
        ]);
    }

    public static function loginSubmit()
    {
        self::startSession();
        $username = trim(Flight::request()->data['username'] ?? '');
        $password = trim(Flight::request()->data['password'] ?? '');

        $config = self::getConfig();
        $validUser = $config['username'] ?? 'admin';
        $hash = $config['password_hash'] ?? '';

        $isValid = false;
        if ($username === $validUser) {
            if (!empty($hash) && password_verify($password, $hash)) {
                $isValid = true;
            } elseif (isset($config['password']) && $config['password'] === $password) {
                // Fallback if configured as plaintext
                $isValid = true;
            }
        }

        if ($isValid) {
            session_regenerate_id(true);
            $key = $config['session_key'] ?? 'lockinrank_admin_auth';
            $_SESSION[$key] = true;
            $_SESSION['admin_user'] = $username;
            $_SESSION['admin_name'] = $config['admin_name'] ?? 'Lockinrank Admin';
            $_SESSION['admin_login_time'] = time();

            Flight::redirect('/admin');
            return;
        }

        $_SESSION['admin_login_error'] = 'Invalid username or password.';
        Flight::redirect('/admin/login');
    }

    public static function logout()
    {
        self::startSession();
        $config = self::getConfig();
        $key = $config['session_key'] ?? 'lockinrank_admin_auth';
        unset($_SESSION[$key], $_SESSION['admin_user'], $_SESSION['admin_name'], $_SESSION['admin_login_time']);
        Flight::redirect('/admin/login');
    }

    public static function dashboard()
    {
        self::checkAuth();
        $db = Database::getConnection();

        // Lockinrank single store
        $store = $db->query("SELECT * FROM stores ORDER BY id ASC LIMIT 1")->fetch();

        $prodStmt = $db->prepare("SELECT * FROM products WHERE store_id = ? ORDER BY id DESC");
        $prodStmt->execute([$store['id']]);
        $products = $prodStmt->fetchAll();

        // Orders summary
        $ordersStmt = $db->query("SELECT * FROM orders ORDER BY id DESC LIMIT 15");
        $recentOrders = $ordersStmt->fetchAll();

        // Customer count and total order count
        $custCountStmt = $db->query("SELECT COUNT(DISTINCT buyer_email) as c FROM orders WHERE buyer_email IS NOT NULL AND buyer_email != ''");
        $totalCustomers = (int)($custCountStmt->fetch()['c'] ?? 0);

        $orderCountStmt = $db->query("SELECT COUNT(*) as c FROM orders");
        $orderCount = (int)($orderCountStmt->fetch()['c'] ?? 0);

        $totalEarnings = 0;
        $totalSales = 0;
        $totalSampleDownloads = 0;
        foreach ($products as $p) {
            $totalSales += $p['sales_count'];
            $totalEarnings += ($p['sales_count'] * $p['price']);
            $totalSampleDownloads += ($p['sample_downloads'] ?? 0);
        }

        Flight::render('admin/dashboard', [
            'store' => $store,
            'products' => $products,
            'recentOrders' => $recentOrders,
            'totalSales' => $totalSales,
            'totalEarnings' => $totalEarnings,
            'totalSampleDownloads' => $totalSampleDownloads,
            'totalCustomers' => $totalCustomers,
            'orderCount' => $orderCount,
            'pageTitle' => 'Admin Dashboard — Lockinrank Shop'
        ]);
    }

    public static function uploadForm()
    {
        self::checkAuth();
        $db = Database::getConnection();
        $store = $db->query("SELECT * FROM stores ORDER BY id ASC LIMIT 1")->fetch();

        $categories = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY display_order ASC, name ASC")->fetchAll();

        $targetExams = [
            'JEE Main & Advanced',
            'NEET (Medical)',
            'AP Physics (AP Physics 1, 2, C Mechanics & E&M)',
            'CBSE Class 11-12 (Board & Foundation)',
            'CBSE Class 9-10 (Foundation)',
            'Olympiad & Competitive (Physics / Math)',
            'International (IB / A-Levels / SAT)',
            'All Exams'
        ];

        $boards = [
            'JEE / NTA Pattern',
            'NEET / NTA Pattern',
            'College Board (AP)',
            'CBSE',
            'ICSE',
            'International Baccalaureate (IB)',
            'All Boards'
        ];

        $subjects = [
            'Physics',
            'Chemistry',
            'Mathematics',
            'Biology',
            'Science & Math Foundation'
        ];

        Flight::render('admin/upload', [
            'store' => $store,
            'categories' => $categories,
            'targetExams' => $targetExams,
            'boards' => $boards,
            'subjects' => $subjects,
            'pageTitle' => 'Publish New Study Material — Lockinrank Shop'
        ]);
    }

    /**
     * Helper to process multiple uploaded images from $_FILES['images']
     * Automatically compresses, downscales heavy images, converts to WebP/JPEG, and saves to /public/uploads/products/
     * If any image filename contains 'cover' (case-insensitive), it is prioritized as the first image.
     */
    private static function handleUploadedImages(): array
    {
        $uploaded = [];
        $uploadDir = __DIR__ . '/../../public/uploads/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (!empty($_FILES['images']) && is_array($_FILES['images']['name'])) {
            $files = $_FILES['images'];
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'];

            $fileCount = count($files['name']);
            $coverImages = [];
            $otherImages = [];

            for ($i = 0; $i < $fileCount; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK && $files['size'][$i] > 0) {
                    $originalName = $files['name'][$i];
                    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                    $tmpName = $files['tmp_name'][$i];

                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $tmpName);
                    finfo_close($finfo);

                    if (in_array($ext, $allowedExtensions) && (in_array($mime, $allowedMimes) || $ext === 'svg')) {
                        // Compress & convert to optimized WebP/JPEG using ImageOptimizer
                        $storedUrl = ImageOptimizer::compressAndStore($tmpName, $originalName, $uploadDir);
                        if ($storedUrl) {
                            if (stripos($originalName, 'cover') !== false) {
                                $coverImages[] = $storedUrl;
                            } else {
                                $otherImages[] = $storedUrl;
                            }
                        }
                    }
                }
            }

            // Put images with 'cover' in filename first
            $uploaded = array_merge($coverImages, $otherImages);
        }

        return $uploaded;
    }

    /**
     * Reorders an array of image paths/URLs so that any image with 'cover'
     * in its file name or URL is placed at index 0 (as primary cover).
     */
    public static function prioritizeCoverImage(array $images): array
    {
        if (empty($images)) {
            return [];
        }

        $covers = [];
        $others = [];

        foreach ($images as $img) {
            $imgStr = is_string($img) ? trim($img) : '';
            if (empty($imgStr)) continue;
            $path = parse_url($imgStr, PHP_URL_PATH) ?? $imgStr;
            $name = basename($path);
            if (stripos($name, 'cover') !== false || stripos($imgStr, 'cover') !== false) {
                $covers[] = $imgStr;
            } else {
                $others[] = $imgStr;
            }
        }

        return array_values(array_unique(array_merge($covers, $others)));
    }

    /**
     * Helper to process uploaded material / sample documents (pdf, zip, doc, docx)
     */
    private static function handleDocumentUpload(string $inputKey, string $subfolder): ?string
    {
        if (empty($_FILES[$inputKey]) || $_FILES[$inputKey]['error'] !== UPLOAD_ERR_OK || $_FILES[$inputKey]['size'] === 0) {
            return null;
        }

        $file = $_FILES[$inputKey];
        $uploadDir = __DIR__ . '/../../storage/' . trim($subfolder, '/') . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $allowedExtensions = ['pdf', 'zip', 'doc', 'docx'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExtensions)) {
            return null;
        }

        $safeOriginal = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
        $storedName = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '_' . $safeOriginal . '.' . $ext;
        $targetPath = $uploadDir . $storedName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $storedName;
        }

        return null;
    }

    public static function save()
    {
        self::checkAuth();
        $db = Database::getConnection();

        $title = trim(Flight::request()->data['title'] ?? '');
        $description = trim(Flight::request()->data['description'] ?? '');
        $price = (float)(Flight::request()->data['price'] ?? 0);
        $rawMrp = Flight::request()->data['mrp'] ?? null;
        $mrp = ($rawMrp !== null && $rawMrp !== '') ? (float)$rawMrp : null;
        $isFree = isset(Flight::request()->data['is_free']) || $price == 0 ? 1 : 0;
        if ($isFree) $price = 0.00;

        $exam = Flight::request()->data['grade'] ?? 'JEE Main & Advanced';
        $board = Flight::request()->data['board'] ?? 'JEE / NTA Pattern';
        $subject = Flight::request()->data['subject'] ?? 'Physics';
        $type = Flight::request()->data['resource_type'] ?? 'Worksheet';
        $format = Flight::request()->data['file_format'] ?? 'PDF (Printable)';
        $pageCount = (int)(Flight::request()->data['page_count'] ?? 25);
        $inkFriendly = isset(Flight::request()->data['is_ink_friendly']) ? 1 : 0;
        $standards = Flight::request()->data['standards'] ?? '';
        $categoryId = !empty(Flight::request()->data['category_id']) ? (int)Flight::request()->data['category_id'] : null;
        $tags = trim(strip_tags(Flight::request()->data['tags'] ?? ''));

        // Process uploaded image files
        $uploadedImages = self::handleUploadedImages();

        // Process remote URLs (one per line)
        $urlsText = trim(Flight::request()->data['preview_urls'] ?? '');
        $urlList = [];
        if (!empty($urlsText)) {
            $lines = preg_split("/\r\n|\n|\r/", $urlsText);
            foreach ($lines as $line) {
                $trimmed = trim($line);
                if (!empty($trimmed) && filter_var($trimmed, FILTER_VALIDATE_URL)) {
                    $urlList[] = $trimmed;
                }
            }
        }

        // Process primary cover URL if provided
        $coverUrl = trim(Flight::request()->data['thumbnail_url'] ?? '');
        if (!empty($coverUrl) && filter_var($coverUrl, FILTER_VALIDATE_URL)) {
            if (!in_array($coverUrl, $urlList)) {
                array_unshift($urlList, $coverUrl);
            }
        }

        // Combine all images (uploads first, then URLs) and prioritize image with 'cover'
        $allImages = self::prioritizeCoverImage(array_merge($uploadedImages, $urlList));

        if (empty($allImages)) {
            $allImages = ['https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=600&auto=format&fit=crop&q=80'];
        }

        $thumbnail = $allImages[0];
        $previewImages = json_encode($allImages);

        // Process uploaded documents: material_file (full paid resource) & sample_file (free sample)
        $materialFilePath = self::handleDocumentUpload('material_file', 'materials');
        $sampleFilePath = self::handleDocumentUpload('sample_file', 'samples');

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title))) . '-' . rand(100, 999);

        $store = $db->query("SELECT id FROM stores ORDER BY id ASC LIMIT 1")->fetch();
        $storeId = $store['id'] ?? 1;

        $categoryId = !empty(Flight::request()->data['category_id']) ? (int)Flight::request()->data['category_id'] : null;
        $tags = trim(strip_tags(Flight::request()->data['tags'] ?? ''));

        $stmt = $db->prepare("
            INSERT INTO products 
            (store_id, title, slug, description, price, mrp, is_free, grade, board, subject, resource_type, file_format, page_count, is_ink_friendly, standards, thumbnail_url, preview_images, material_file_path, sample_file_path, category_id, tags, rating, review_count, sales_count, sample_downloads)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 5.0, 0, 0, 0)
        ");

        $stmt->execute([
            $storeId, $title, $slug, $description, $price, $mrp, $isFree, $exam, $board, $subject, $type, $format, $pageCount, $inkFriendly, $standards, $thumbnail, $previewImages, $materialFilePath, $sampleFilePath, $categoryId, $tags
        ]);

        Flight::redirect('/admin');
    }

    public static function editForm($id)
    {
        self::checkAuth();
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([(int)$id]);
        $product = $stmt->fetch();

        if (!$product) {
            Flight::halt(404, 'Material not found');
            return;
        }

        $previewImages = json_decode($product['preview_images'] ?? '[]', true) ?: [];
        if (!empty($product['thumbnail_url']) && !in_array($product['thumbnail_url'], $previewImages)) {
            array_unshift($previewImages, $product['thumbnail_url']);
        }

        $targetExams = [
            'JEE Main & Advanced',
            'NEET (Medical)',
            'AP Physics (College Board)',
            'CBSE Class 11-12',
            'CBSE Class 9-10',
            'Olympiad & Competitive'
        ];

        $boards = [
            'JEE / NTA Pattern',
            'NEET / NTA Pattern',
            'College Board (AP)',
            'CBSE',
            'ICSE',
            'International Baccalaureate (IB)',
            'All Boards'
        ];

        $subjects = [
            'Physics',
            'Chemistry',
            'Mathematics',
            'Biology',
            'Science & Math Foundation'
        ];

        $categories = $db->query("SELECT * FROM categories ORDER BY display_order ASC, name ASC")->fetchAll();

        Flight::render('admin/edit', [
            'product' => $product,
            'categories' => $categories,
            'previewImages' => $previewImages,
            'targetExams' => $targetExams,
            'boards' => $boards,
            'subjects' => $subjects,
            'pageTitle' => 'Edit ' . htmlspecialchars($product['title']) . ' — Lockinrank Admin'
        ]);
    }

    public static function updateProduct($id)
    {
        self::checkAuth();
        $db = Database::getConnection();

        $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([(int)$id]);
        $existing = $stmt->fetch();

        if (!$existing) {
            Flight::halt(404, 'Material not found');
            return;
        }

        $title = trim(Flight::request()->data['title'] ?? '');
        $description = trim(Flight::request()->data['description'] ?? '');
        $price = (float)(Flight::request()->data['price'] ?? 0);
        $rawMrp = Flight::request()->data['mrp'] ?? null;
        $mrp = ($rawMrp !== null && $rawMrp !== '') ? (float)$rawMrp : null;
        $isFree = isset(Flight::request()->data['is_free']) || $price == 0 ? 1 : 0;
        if ($isFree) $price = 0.00;

        $exam = Flight::request()->data['grade'] ?? $existing['grade'];
        $board = Flight::request()->data['board'] ?? $existing['board'];
        $subject = Flight::request()->data['subject'] ?? $existing['subject'];
        $type = Flight::request()->data['resource_type'] ?? $existing['resource_type'];
        $format = Flight::request()->data['file_format'] ?? $existing['file_format'];
        $pageCount = (int)(Flight::request()->data['page_count'] ?? $existing['page_count']);
        $inkFriendly = isset(Flight::request()->data['is_ink_friendly']) ? 1 : 0;
        $standards = Flight::request()->data['standards'] ?? '';
        $categoryId = !empty(Flight::request()->data['category_id']) ? (int)Flight::request()->data['category_id'] : null;
        $tags = trim(strip_tags(Flight::request()->data['tags'] ?? ''));

        // Existing kept images from checkboxes/form
        $keptImages = Flight::request()->data['kept_images'] ?? [];
        if (!is_array($keptImages)) {
            $keptImages = [];
        }

        // Process newly uploaded files
        $newUploaded = self::handleUploadedImages();

        // Process new URLs (one per line)
        $urlsText = trim(Flight::request()->data['preview_urls'] ?? '');
        $newUrls = [];
        if (!empty($urlsText)) {
            $lines = preg_split("/\r\n|\n|\r/", $urlsText);
            foreach ($lines as $line) {
                $trimmed = trim($line);
                if (!empty($trimmed) && filter_var($trimmed, FILTER_VALIDATE_URL)) {
                    $newUrls[] = $trimmed;
                }
            }
        }

        $allImages = self::prioritizeCoverImage(array_merge($keptImages, $newUploaded, $newUrls));

        if (empty($allImages)) {
            $allImages = [$existing['thumbnail_url'] ?: 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=600&auto=format&fit=crop&q=80'];
        }

        // If an image with 'cover' in its name is found, make it the cover thumbnail
        $hasCoverImage = !empty($allImages) && (stripos(basename(parse_url($allImages[0], PHP_URL_PATH) ?? $allImages[0]), 'cover') !== false || stripos($allImages[0], 'cover') !== false);
        $primaryIndex = (int)(Flight::request()->data['primary_image_index'] ?? 0);
        if ($hasCoverImage) {
            $thumbnail = $allImages[0];
        } elseif (isset($allImages[$primaryIndex])) {
            $thumbnail = $allImages[$primaryIndex];
        } else {
            $thumbnail = $allImages[0];
        }

        $previewImages = json_encode($allImages);

        // Process full material document upload / retain
        $newMaterial = self::handleDocumentUpload('material_file', 'materials');
        if ($newMaterial) {
            $materialFilePath = $newMaterial;
        } elseif (isset(Flight::request()->data['remove_material_file'])) {
            $materialFilePath = null;
        } else {
            $materialFilePath = $existing['material_file_path'] ?? null;
        }

        // Process sample document upload / retain
        $newSample = self::handleDocumentUpload('sample_file', 'samples');
        if ($newSample) {
            $sampleFilePath = $newSample;
        } elseif (isset(Flight::request()->data['remove_sample_file'])) {
            $sampleFilePath = null;
        } else {
            $sampleFilePath = $existing['sample_file_path'] ?? null;
        }

        $updateStmt = $db->prepare("
            UPDATE products SET
                title = ?,
                description = ?,
                price = ?,
                mrp = ?,
                is_free = ?,
                grade = ?,
                board = ?,
                subject = ?,
                resource_type = ?,
                file_format = ?,
                page_count = ?,
                is_ink_friendly = ?,
                standards = ?,
                thumbnail_url = ?,
                preview_images = ?,
                material_file_path = ?,
                sample_file_path = ?,
                category_id = ?,
                tags = ?
            WHERE id = ?
        ");

        $updateStmt->execute([
            $title, $description, $price, $mrp, $isFree, $exam, $board, $subject, $type, $format, $pageCount, $inkFriendly, $standards, $thumbnail, $previewImages, $materialFilePath, $sampleFilePath, $categoryId, $tags, (int)$id
        ]);

        $_SESSION['admin_flash_success'] = "Resource updated successfully.";
        Flight::redirect('/admin/products');
    }

    public static function deleteProduct($id)
    {
        self::checkAuth();
        $id = (int)$id;
        if ($id <= 0) {
            $_SESSION['admin_flash_error'] = "Invalid product ID.";
            Flight::redirect('/admin/products');
            return;
        }

        $db = Database::getConnection();
        try {
            $db->beginTransaction();

            // 1. Delete associated reviews
            $stmtRev = $db->prepare("DELETE FROM reviews WHERE product_id = ?");
            $stmtRev->execute([$id]);

            // 2. Delete associated order items
            $stmtOi = $db->prepare("DELETE FROM order_items WHERE product_id = ?");
            $stmtOi->execute([$id]);

            // 3. Delete product
            $stmtProd = $db->prepare("DELETE FROM products WHERE id = ?");
            $stmtProd->execute([$id]);

            $db->commit();
            $_SESSION['admin_flash_success'] = "Resource #{$id} has been permanently deleted.";
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Failed to delete product #{$id}: " . $e->getMessage());
            $_SESSION['admin_flash_error'] = "Failed to delete resource: " . $e->getMessage();
        }

        Flight::redirect('/admin/products');
    }

    public static function bulkDeleteProducts()
    {
        self::checkAuth();
        $productIds = Flight::request()->data['product_ids'] ?? [];
        if (!is_array($productIds) || empty($productIds)) {
            $_SESSION['admin_flash_error'] = "No products were selected for bulk deletion.";
            Flight::redirect('/admin/products');
            return;
        }

        $cleanIds = [];
        foreach ($productIds as $pid) {
            $val = (int)$pid;
            if ($val > 0) {
                $cleanIds[] = $val;
            }
        }

        if (empty($cleanIds)) {
            $_SESSION['admin_flash_error'] = "No valid products selected.";
            Flight::redirect('/admin/products');
            return;
        }

        $db = Database::getConnection();
        try {
            $db->beginTransaction();

            $placeholders = implode(',', array_fill(0, count($cleanIds), '?'));

            // 1. Delete associated reviews
            $stmtRev = $db->prepare("DELETE FROM reviews WHERE product_id IN ($placeholders)");
            $stmtRev->execute($cleanIds);

            // 2. Delete associated order items
            $stmtOi = $db->prepare("DELETE FROM order_items WHERE product_id IN ($placeholders)");
            $stmtOi->execute($cleanIds);

            // 3. Delete products
            $stmtProd = $db->prepare("DELETE FROM products WHERE id IN ($placeholders)");
            $stmtProd->execute($cleanIds);

            $count = count($cleanIds);
            $db->commit();
            $_SESSION['admin_flash_success'] = "Successfully deleted {$count} resource" . ($count > 1 ? 's' : '') . " in bulk.";
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Failed bulk deleting products: " . $e->getMessage());
            $_SESSION['admin_flash_error'] = "Failed to delete selected resources: " . $e->getMessage();
        }

        Flight::redirect('/admin/products');
    }

    /**
     * Download a sample products.json template
     */
    public static function exportSampleJson()
    {
        self::checkAuth();
        $sampleData = [
            [
                "title" => "51 Modular Practice DPPs for NEET Physics Class 11",
                "description" => "# Comprehensive DPP Pack for NEET 2026/2027\n\nModular Daily Practice Problem sheets specifically structured for **Class 11 Physics** syllabus.\n\n### Key Highlights\n- **51 Chapterwise modules** covering Kinematics, Laws of Motion, Work Energy, and Gravitation\n- Ink-friendly monochrome design ready for high-speed printing\n- Detailed answer keys and step-by-step mathematical solutions included\n\n### Specifications\n| Feature | Details |\n| :--- | :--- |\n| Pages | 148 Pages |\n| Difficulty | Easy to NTA NEET Exam Level |\n| Format | High-resolution PDF |",
                "price" => 299,
                "mrp" => 699,
                "is_free" => 0,
                "grade" => "NEET (Medical)",
                "board" => "NEET / NTA Pattern",
                "subject" => "Physics",
                "resource_type" => "Worksheet",
                "file_format" => "PDF (Printable)",
                "page_count" => 148,
                "is_ink_friendly" => 1,
                "standards" => "NCERT Class 11 & NTA NEET Pattern",
                "tags" => "NEET, Physics, DPP, Class 11, Worksheets",
                "thumbnail_url" => "https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=600&auto=format&fit=crop&q=80",
                "preview_images" => [
                    "https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=600&auto=format&fit=crop&q=80"
                ]
            ],
            [
                "title" => "Free Sample: Vectors & Kinematics Mastery Formula Sheet",
                "description" => "## Free Quick Revision Formula Sheet\n\nEssential formulas, unit conversions, and vector cross/dot product shortcuts for **JEE Main & Advanced** aspirants.\n\n- Concise 2-page cheat sheet\n- Includes memory triggers & sign conventions\n- 100% Free digital download",
                "price" => 0,
                "mrp" => 99,
                "is_free" => 1,
                "grade" => "JEE Main & Advanced",
                "board" => "JEE / NTA Pattern",
                "subject" => "Physics",
                "resource_type" => "Formula Sheet",
                "file_format" => "PDF (Printable)",
                "page_count" => 2,
                "is_ink_friendly" => 1,
                "standards" => "NCERT Class 11",
                "tags" => "Free, Vectors, Kinematics, JEE Main, Formula Sheet",
                "thumbnail_url" => "https://images.unsplash.com/photo-1635070041078-e363dbe005cb?w=600&auto=format&fit=crop&q=80",
                "preview_images" => [
                    "https://images.unsplash.com/photo-1635070041078-e363dbe005cb?w=600&auto=format&fit=crop&q=80"
                ]
            ]
        ];

        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="sample-products.json"');
        echo json_encode($sampleData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Import products from uploaded JSON file or raw JSON text
     */
    public static function importJson()
    {
        self::checkAuth();
        $db = Database::getConnection();

        $jsonString = '';

        if (!empty($_FILES['json_file']) && $_FILES['json_file']['error'] === UPLOAD_ERR_OK && $_FILES['json_file']['size'] > 0) {
            $jsonString = file_get_contents($_FILES['json_file']['tmp_name']);
        } elseif (!empty(Flight::request()->data['json_content'])) {
            $jsonString = trim(Flight::request()->data['json_content']);
        }

        if (empty($jsonString)) {
            $_SESSION['admin_flash_error'] = "Please choose a JSON file or paste JSON content to import.";
            Flight::redirect('/admin/products');
            return;
        }

        $decoded = json_decode($jsonString, true);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            $_SESSION['admin_flash_error'] = "Invalid JSON syntax: " . json_last_error_msg();
            Flight::redirect('/admin/products');
            return;
        }

        // Support single object or array of objects
        $items = [];
        if (isset($decoded['title'])) {
            $items = [$decoded];
        } elseif (is_array($decoded)) {
            $items = $decoded;
        }

        if (empty($items)) {
            $_SESSION['admin_flash_error'] = "No valid product entries found in the provided JSON.";
            Flight::redirect('/admin/products');
            return;
        }

        $store = $db->query("SELECT id FROM stores ORDER BY id ASC LIMIT 1")->fetch();
        $storeId = $store['id'] ?? 1;

        $categoriesMap = [];
        $cats = $db->query("SELECT id, name FROM categories")->fetchAll();
        foreach ($cats as $cat) {
            $categoriesMap[strtolower(trim($cat['name']))] = (int)$cat['id'];
        }

        $importedCount = 0;
        $errors = [];

        $stmt = $db->prepare("
            INSERT INTO products 
            (store_id, title, slug, description, price, mrp, is_free, grade, board, subject, resource_type, file_format, page_count, is_ink_friendly, standards, thumbnail_url, preview_images, material_file_path, sample_file_path, category_id, tags, rating, review_count, sales_count, sample_downloads)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 5.0, 0, 0, 0)
        ");

        foreach ($items as $index => $item) {
            if (!is_array($item)) {
                continue;
            }

            $title = trim($item['title'] ?? '');
            if (empty($title)) {
                $errors[] = "Item #" . ($index + 1) . " skipped: missing 'title'.";
                continue;
            }

            $description = trim($item['description'] ?? '');
            $price = (float)($item['price'] ?? 0);
            $rawMrp = $item['mrp'] ?? null;
            $mrp = ($rawMrp !== null && $rawMrp !== '') ? (float)$rawMrp : null;
            $isFree = (!empty($item['is_free']) || $price == 0) ? 1 : 0;
            if ($isFree) {
                $price = 0.00;
            }

            $grade = trim($item['grade'] ?? 'JEE Main & Advanced');
            $board = trim($item['board'] ?? 'JEE / NTA Pattern');
            $subject = trim($item['subject'] ?? 'Physics');
            $resourceType = trim($item['resource_type'] ?? 'Worksheet');
            $fileFormat = trim($item['file_format'] ?? 'PDF (Printable)');
            $pageCount = (int)($item['page_count'] ?? 10);
            $isInkFriendly = isset($item['is_ink_friendly']) ? (int)$item['is_ink_friendly'] : 1;
            $standards = trim($item['standards'] ?? '');
            $tags = trim(strip_tags($item['tags'] ?? ''));

            // Images
            $thumbnail = trim($item['thumbnail_url'] ?? '');
            $previewImagesList = [];
            if (!empty($item['preview_images']) && is_array($item['preview_images'])) {
                foreach ($item['preview_images'] as $img) {
                    if (is_string($img) && filter_var(trim($img), FILTER_VALIDATE_URL)) {
                        $previewImagesList[] = trim($img);
                    }
                }
            }

            if (!empty($thumbnail) && !in_array($thumbnail, $previewImagesList)) {
                $previewImagesList[] = $thumbnail;
            }

            // Prioritize image containing 'cover' as the first image and cover thumbnail
            $previewImagesList = self::prioritizeCoverImage($previewImagesList);

            if (empty($previewImagesList)) {
                $previewImagesList = ['https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=600&auto=format&fit=crop&q=80'];
            }

            $thumbnail = $previewImagesList[0];
            $previewImages = json_encode($previewImagesList);

            $materialFilePath = !empty($item['material_file_path']) ? trim($item['material_file_path']) : null;
            $sampleFilePath = !empty($item['sample_file_path']) ? trim($item['sample_file_path']) : null;

            // Category resolution
            $categoryId = null;
            if (!empty($item['category_id'])) {
                $categoryId = (int)$item['category_id'];
            } elseif (!empty($item['category']) && isset($categoriesMap[strtolower(trim($item['category']))])) {
                $categoryId = $categoriesMap[strtolower(trim($item['category']))];
            }

            $cleanTitle = preg_replace('/[^A-Za-z0-9-]+/', '-', $title);
            $slug = strtolower(trim($cleanTitle, '-')) . '-' . rand(100, 999);

            try {
                $stmt->execute([
                    $storeId,
                    $title,
                    $slug,
                    $description,
                    $price,
                    $mrp,
                    $isFree,
                    $grade,
                    $board,
                    $subject,
                    $resourceType,
                    $fileFormat,
                    $pageCount,
                    $isInkFriendly,
                    $standards,
                    $thumbnail,
                    $previewImages,
                    $materialFilePath,
                    $sampleFilePath,
                    $categoryId,
                    $tags
                ]);
                $importedCount++;
            } catch (\Exception $e) {
                $errors[] = "Error importing '{$title}': " . $e->getMessage();
            }
        }

        if ($importedCount > 0) {
            $msg = "Successfully imported {$importedCount} product" . ($importedCount > 1 ? 's' : '') . " from JSON.";
            if (!empty($errors)) {
                $msg .= " (" . count($errors) . " issue(s) reported)";
            }
            $_SESSION['admin_flash_success'] = $msg;
        } else {
            $_SESSION['admin_flash_error'] = "No products were imported. " . implode(" ", $errors);
        }

        Flight::redirect('/admin/products');
    }

    public static function categories()
    {
        self::checkAuth();
        $db = Database::getConnection();

        $stmt = $db->query("
            SELECT c.*, COUNT(p.id) as product_count 
            FROM categories c 
            LEFT JOIN products p ON p.category_id = c.id 
            GROUP BY c.id 
            ORDER BY c.display_order ASC, c.name ASC
        ");
        $categories = $stmt->fetchAll();

        Flight::render('admin/categories', [
            'categories' => $categories,
            'pageTitle' => 'Curriculum Categories — Lockinrank Control Panel'
        ]);
    }

    public static function createCategory()
    {
        self::checkAuth();
        $name = trim(Flight::request()->data['name'] ?? '');
        $slug = trim(Flight::request()->data['slug'] ?? '');
        $description = trim(Flight::request()->data['description'] ?? '');
        $icon = trim(Flight::request()->data['icon'] ?? 'fa-solid fa-folder');
        $displayOrder = (int)(Flight::request()->data['display_order'] ?? 0);
        $isActive = isset(Flight::request()->data['is_active']) ? 1 : 0;

        if (empty($name)) {
            $_SESSION['admin_flash_error'] = "Category name is required.";
            Flight::redirect('/admin/categories');
            return;
        }

        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        } else {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $slug)));
        }

        if (empty($icon)) {
            $icon = 'fa-solid fa-folder';
        }

        $db = Database::getConnection();
        try {
            $stmt = $db->prepare("
                INSERT INTO categories (name, slug, description, icon, display_order, is_active, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $slug, $description, $icon, $displayOrder, $isActive, date('Y-m-d H:i:s')]);
            $_SESSION['admin_flash_success'] = "Category '{$name}' created successfully.";
        } catch (\Throwable $e) {
            error_log("Failed to create category: " . $e->getMessage());
            $_SESSION['admin_flash_error'] = "Failed to create category: " . (str_contains($e->getMessage(), 'UNIQUE') ? 'A category with this slug already exists.' : $e->getMessage());
        }

        Flight::redirect('/admin/categories');
    }

    public static function updateCategory($id)
    {
        self::checkAuth();
        $id = (int)$id;
        $name = trim(Flight::request()->data['name'] ?? '');
        $slug = trim(Flight::request()->data['slug'] ?? '');
        $description = trim(Flight::request()->data['description'] ?? '');
        $icon = trim(Flight::request()->data['icon'] ?? 'fa-solid fa-folder');
        $displayOrder = (int)(Flight::request()->data['display_order'] ?? 0);
        $isActive = isset(Flight::request()->data['is_active']) ? 1 : 0;

        if (empty($name)) {
            $_SESSION['admin_flash_error'] = "Category name cannot be empty.";
            Flight::redirect('/admin/categories');
            return;
        }

        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        } else {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $slug)));
        }

        if (empty($icon)) {
            $icon = 'fa-solid fa-folder';
        }

        $db = Database::getConnection();
        try {
            $stmt = $db->prepare("
                UPDATE categories SET
                    name = ?,
                    slug = ?,
                    description = ?,
                    icon = ?,
                    display_order = ?,
                    is_active = ?
                WHERE id = ?
            ");
            $stmt->execute([$name, $slug, $description, $icon, $displayOrder, $isActive, $id]);
            $_SESSION['admin_flash_success'] = "Category '{$name}' updated successfully.";
        } catch (\Throwable $e) {
            error_log("Failed to update category: " . $e->getMessage());
            $_SESSION['admin_flash_error'] = "Failed to update category: " . (str_contains($e->getMessage(), 'UNIQUE') ? 'A category with this slug already exists.' : $e->getMessage());
        }

        Flight::redirect('/admin/categories');
    }

    public static function deleteCategory($id)
    {
        self::checkAuth();
        $id = (int)$id;
        if ($id <= 0) {
            $_SESSION['admin_flash_error'] = "Invalid category ID.";
            Flight::redirect('/admin/categories');
            return;
        }

        $db = Database::getConnection();
        try {
            $db->beginTransaction();

            // Unassign products referencing this category
            $unassignStmt = $db->prepare("UPDATE products SET category_id = NULL WHERE category_id = ?");
            $unassignStmt->execute([$id]);

            // Delete category
            $delStmt = $db->prepare("DELETE FROM categories WHERE id = ?");
            $delStmt->execute([$id]);

            $db->commit();
            $_SESSION['admin_flash_success'] = "Category deleted successfully. Associated products have been unassigned.";
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log("Failed to delete category: " . $e->getMessage());
            $_SESSION['admin_flash_error'] = "Failed to delete category: " . $e->getMessage();
        }

        Flight::redirect('/admin/categories');
    }

    public static function products()
    {
        self::checkAuth();
        $db = Database::getConnection();

        $page = max(1, (int)(Flight::request()->query['page'] ?? 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $search = trim(Flight::request()->query['q'] ?? '');
        $gradeFilter = trim(Flight::request()->query['grade'] ?? '');
        $boardFilter = trim(Flight::request()->query['board'] ?? '');

        $where = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(title LIKE ? OR subject LIKE ? OR description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if (!empty($gradeFilter)) {
            $where[] = "grade = ?";
            $params[] = $gradeFilter;
        }

        if (!empty($boardFilter)) {
            $where[] = "board = ?";
            $params[] = $boardFilter;
        }

        $whereSql = implode(" AND ", $where);

        $countStmt = $db->prepare("SELECT COUNT(*) as cnt FROM products WHERE $whereSql");
        $countStmt->execute($params);
        $totalProducts = (int)($countStmt->fetch()['cnt'] ?? 0);
        $totalPages = max(1, (int)ceil($totalProducts / $limit));

        $sql = "SELECT * FROM products WHERE $whereSql ORDER BY id DESC LIMIT $limit OFFSET $offset";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();

        $grades = $db->query("SELECT DISTINCT grade FROM products WHERE grade IS NOT NULL AND grade != '' ORDER BY grade ASC")->fetchAll(\PDO::FETCH_COLUMN);
        $boards = $db->query("SELECT DISTINCT board FROM products WHERE board IS NOT NULL AND board != '' ORDER BY board ASC")->fetchAll(\PDO::FETCH_COLUMN);

        Flight::render('admin/products', [
            'products' => $products,
            'totalProducts' => $totalProducts,
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'gradeFilter' => $gradeFilter,
            'boardFilter' => $boardFilter,
            'grades' => $grades,
            'boards' => $boards,
            'pageTitle' => 'Manage Products — Lockinrank Control Panel'
        ]);
    }

    public static function orders()
    {
        self::checkAuth();
        $db = Database::getConnection();

        $page = max(1, (int)(Flight::request()->query['page'] ?? 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $search = trim(Flight::request()->query['q'] ?? '');
        $statusFilter = trim(Flight::request()->query['status'] ?? '');

        $where = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(order_number LIKE ? OR buyer_name LIKE ? OR buyer_email LIKE ? OR buyer_phone LIKE ? OR razorpay_payment_id LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if (!empty($statusFilter)) {
            $where[] = "payment_status = ?";
            $params[] = $statusFilter;
        }

        $whereSql = implode(" AND ", $where);

        $countStmt = $db->prepare("SELECT COUNT(*) as cnt FROM orders WHERE $whereSql");
        $countStmt->execute($params);
        $totalOrders = (int)($countStmt->fetch()['cnt'] ?? 0);
        $totalPages = max(1, (int)ceil($totalOrders / $limit));

        $sql = "SELECT * FROM orders WHERE $whereSql ORDER BY id DESC LIMIT $limit OFFSET $offset";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll();

        Flight::render('admin/orders', [
            'orders' => $orders,
            'totalOrders' => $totalOrders,
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'statusFilter' => $statusFilter,
            'pageTitle' => 'Manage Orders — Lockinrank Control Panel'
        ]);
    }

    public static function customers()
    {
        self::checkAuth();
        $db = Database::getConnection();

        $page = max(1, (int)(Flight::request()->query['page'] ?? 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $search = trim(Flight::request()->query['q'] ?? '');

        $where = ["buyer_email IS NOT NULL AND buyer_email != ''"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(buyer_email LIKE ? OR buyer_name LIKE ? OR buyer_phone LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $whereSql = implode(" AND ", $where);

        // Count distinct emails matching search
        $countSql = "SELECT COUNT(DISTINCT buyer_email) as cnt FROM orders WHERE $whereSql";
        $countStmt = $db->prepare($countSql);
        $countStmt->execute($params);
        $totalCustomers = (int)($countStmt->fetch()['cnt'] ?? 0);
        $totalPages = max(1, (int)ceil($totalCustomers / $limit));

        // Aggregate customer records
        $dataSql = "
            SELECT buyer_email,
                   MAX(buyer_name) as buyer_name,
                   MAX(buyer_phone) as buyer_phone,
                   COUNT(id) as total_orders,
                   SUM(total_amount) as total_spent,
                   MIN(created_at) as first_order_date,
                   MAX(created_at) as last_order_date
            FROM orders
            WHERE $whereSql
            GROUP BY buyer_email
            ORDER BY last_order_date DESC
            LIMIT $limit OFFSET $offset
        ";
        $stmt = $db->prepare($dataSql);
        $stmt->execute($params);
        $customers = $stmt->fetchAll();

        Flight::render('admin/customers', [
            'customers' => $customers,
            'totalCustomers' => $totalCustomers,
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'pageTitle' => 'Customer CRM & Profiles — Lockinrank Control Panel'
        ]);
    }

    public static function customerDetail()
    {
        self::checkAuth();
        $db = Database::getConnection();

        $email = trim(Flight::request()->query['email'] ?? '');
        if (empty($email)) {
            Flight::redirect('/admin/customers');
            return;
        }

        // Customer aggregation stats
        $stmt = $db->prepare("
            SELECT buyer_email,
                   MAX(buyer_name) as buyer_name,
                   MAX(buyer_phone) as buyer_phone,
                   COUNT(id) as total_orders,
                   SUM(total_amount) as total_spent,
                   MIN(created_at) as first_order_date,
                   MAX(created_at) as last_order_date
            FROM orders
            WHERE buyer_email = ?
            GROUP BY buyer_email
        ");
        $stmt->execute([$email]);
        $customer = $stmt->fetch();

        if (!$customer) {
            Flight::redirect('/admin/customers');
            return;
        }

        // Past order records
        $ordStmt = $db->prepare("SELECT * FROM orders WHERE buyer_email = ? ORDER BY id DESC");
        $ordStmt->execute([$email]);
        $orders = $ordStmt->fetchAll();

        // Purchased materials and token expiry status
        $itemsStmt = $db->prepare("
            SELECT oi.*, p.title, p.file_format, p.price as original_price, o.order_number, o.created_at as order_date
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            JOIN orders o ON oi.order_id = o.id
            WHERE o.buyer_email = ?
            ORDER BY oi.id DESC
        ");
        $itemsStmt->execute([$email]);
        $materials = $itemsStmt->fetchAll();

        Flight::render('admin/customer-detail', [
            'customer' => $customer,
            'orders' => $orders,
            'materials' => $materials,
            'pageTitle' => 'Student Profile: ' . htmlspecialchars($customer['buyer_name'] ?: 'Customer') . ' — Lockinrank Control Panel'
        ]);
    }

    public static function refreshToken()
    {
        self::checkAuth();
        $db = Database::getConnection();

        $itemId = (int)(Flight::request()->data['item_id'] ?? 0);
        $returnUrl = Flight::request()->data['return_url'] ?? '/admin/customers';

        if ($itemId <= 0) {
            $_SESSION['admin_flash_error'] = 'Invalid material item reference.';
            Flight::redirect($returnUrl);
            return;
        }

        $newExpiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));

        $stmt = $db->prepare("UPDATE order_items SET expires_at = ? WHERE id = ?");
        $stmt->execute([$newExpiresAt, $itemId]);

        $_SESSION['admin_flash_success'] = "Download token renewed! Validity extended for 7 days until " . date('d M Y, h:i A', strtotime($newExpiresAt));
        Flight::redirect($returnUrl);
    }
}
