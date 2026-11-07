<?php

namespace App\Controllers;

use Flight;
use App\Database;

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
            'pageTitle' => 'Admin Dashboard — Lockinrank Shop'
        ]);
    }

    public static function uploadForm()
    {
        self::checkAuth();
        $db = Database::getConnection();
        $store = $db->query("SELECT * FROM stores ORDER BY id ASC LIMIT 1")->fetch();

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
            'targetExams' => $targetExams,
            'boards' => $boards,
            'subjects' => $subjects,
            'pageTitle' => 'Publish New Study Material — Lockinrank Shop'
        ]);
    }

    /**
     * Helper to process multiple uploaded images from $_FILES['images']
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
            for ($i = 0; $i < $fileCount; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK && $files['size'][$i] > 0) {
                    $originalName = $files['name'][$i];
                    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                    $tmpName = $files['tmp_name'][$i];

                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $tmpName);
                    finfo_close($finfo);

                    if (in_array($ext, $allowedExtensions) && (in_array($mime, $allowedMimes) || $ext === 'svg')) {
                        $safeName = 'prod_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                        $targetPath = $uploadDir . $safeName;
                        if (move_uploaded_file($tmpName, $targetPath)) {
                            $uploaded[] = '/uploads/products/' . $safeName;
                        }
                    }
                }
            }
        }

        return $uploaded;
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

        // Combine all images (uploads first, then URLs)
        $allImages = array_values(array_unique(array_merge($uploadedImages, $urlList)));

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

        $stmt = $db->prepare("
            INSERT INTO products 
            (store_id, title, slug, description, price, is_free, grade, board, subject, resource_type, file_format, page_count, is_ink_friendly, standards, thumbnail_url, preview_images, material_file_path, sample_file_path, rating, review_count, sales_count, sample_downloads)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 5.0, 0, 0, 0)
        ");

        $stmt->execute([
            $storeId, $title, $slug, $description, $price, $isFree, $exam, $board, $subject, $type, $format, $pageCount, $inkFriendly, $standards, $thumbnail, $previewImages, $materialFilePath, $sampleFilePath
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

        Flight::render('admin/edit', [
            'product' => $product,
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

        $allImages = array_values(array_unique(array_merge($keptImages, $newUploaded, $newUrls)));

        if (empty($allImages)) {
            $allImages = [$existing['thumbnail_url'] ?: 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=600&auto=format&fit=crop&q=80'];
        }

        // Allow explicitly picking primary thumbnail index
        $primaryIndex = (int)(Flight::request()->data['primary_image_index'] ?? 0);
        if (isset($allImages[$primaryIndex])) {
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
                sample_file_path = ?
            WHERE id = ?
        ");

        $updateStmt->execute([
            $title, $description, $price, $isFree, $exam, $board, $subject, $type, $format, $pageCount, $inkFriendly, $standards, $thumbnail, $previewImages, $materialFilePath, $sampleFilePath, (int)$id
        ]);

        Flight::redirect('/admin');
    }

    public static function deleteProduct($id)
    {
        self::checkAuth();
        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([(int)$id]);
        Flight::redirect('/admin');
    }
}
