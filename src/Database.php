<?php

namespace App;

use PDO;

class Database
{
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            $configPath = __DIR__ . '/../config/database.php';
            $config = file_exists($configPath) ? require $configPath : ['default' => 'sqlite'];

            if (($config['default'] ?? 'sqlite') === 'mysql' && !empty($config['mysql']['database'])) {
                $m = $config['mysql'];
                $dsn = "mysql:host={$m['host']};port={$m['port']};dbname={$m['database']};charset={$m['charset']}";
                try {
                    self::$pdo = new PDO($dsn, $m['username'], $m['password'], [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]);

                    // Auto-seed Hostinger MySQL if tables do not exist yet
                    $tableCheck = self::$pdo->query("SHOW TABLES LIKE 'stores'")->fetch();
                    if (!$tableCheck) {
                        $dumpPath = __DIR__ . '/../data/hostinger_mysql_dump.sql';
                        if (file_exists($dumpPath)) {
                            $sql = file_get_contents($dumpPath);
                            self::$pdo->exec($sql);
                        }
                    }

                    // Always ensure all required columns exist on MySQL
                    self::ensureSchema(self::$pdo);
                } catch (\Throwable $e) {
                    error_log("MySQL connection failed: " . $e->getMessage() . " — Falling back to SQLite.");
                    $dbPath = $config['sqlite']['database'] ?? (__DIR__ . '/../data/tpt.db');
                    self::$pdo = new PDO("sqlite:" . $dbPath);
                    self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                    self::ensureSchema(self::$pdo);
                }
            } else {
                $dbPath = $config['sqlite']['database'] ?? (__DIR__ . '/../data/tpt.db');
                $isNew = !file_exists($dbPath);

                self::$pdo = new PDO("sqlite:" . $dbPath);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                if ($isNew) {
                    self::migrateAndSeed(self::$pdo);
                } else {
                    self::ensureSchema(self::$pdo);

                    // Auto-migrate branding if existing store has old brand
                    try {
                        $store = self::$pdo->query("SELECT name FROM stores WHERE id = 1")->fetch();
                        if ($store && str_contains($store['name'], 'TeachShare')) {
                            self::rebrandStoreAndProducts(self::$pdo);
                        }
                    } catch (\Throwable $e) {}
                }
            }
        }
        return self::$pdo;
    }

    private static function ensureSchema(PDO $pdo): void
    {
        // Ensure columns in order_items exist
        try { $pdo->exec("ALTER TABLE order_items ADD COLUMN download_token VARCHAR(100) DEFAULT NULL"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE order_items ADD COLUMN download_count INT DEFAULT 0"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE order_items ADD COLUMN expires_at DATETIME DEFAULT NULL"); } catch (\Throwable $e) {}

        // Populate fallback expires_at for older orders (7 days after order date)
        try {
            $pdo->exec("
                UPDATE order_items 
                SET expires_at = (
                    SELECT datetime(orders.created_at, '+7 days') 
                    FROM orders 
                    WHERE orders.id = order_items.order_id
                )
                WHERE expires_at IS NULL
            ");
        } catch (\Throwable $e) {
            // MySQL syntax fallback: DATE_ADD
            try {
                $pdo->exec("
                    UPDATE order_items oi
                    JOIN orders o ON oi.order_id = o.id
                    SET oi.expires_at = DATE_ADD(o.created_at, INTERVAL 7 DAY)
                    WHERE oi.expires_at IS NULL
                ");
            } catch (\Throwable $e2) {}
        }

        // Ensure columns in orders exist
        try { $pdo->exec("ALTER TABLE orders ADD COLUMN razorpay_order_id VARCHAR(100) DEFAULT NULL"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE orders ADD COLUMN razorpay_payment_id VARCHAR(100) DEFAULT NULL"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE orders ADD COLUMN razorpay_signature VARCHAR(255) DEFAULT NULL"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE orders ADD COLUMN payment_status VARCHAR(50) DEFAULT 'pending'"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE orders ADD COLUMN download_token VARCHAR(100) DEFAULT NULL"); } catch (\Throwable $e) {}

        // Ensure columns in products exist
        try { $pdo->exec("ALTER TABLE products ADD COLUMN sample_downloads INT DEFAULT 0"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE products ADD COLUMN material_file_path TEXT DEFAULT NULL"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE products ADD COLUMN sample_file_path TEXT DEFAULT NULL"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE products ADD COLUMN category_id INT DEFAULT NULL"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE products ADD COLUMN tags TEXT DEFAULT NULL"); } catch (\Throwable $e) {}
        try { $pdo->exec("ALTER TABLE products ADD COLUMN mrp DECIMAL(10, 2) DEFAULT NULL"); } catch (\Throwable $e) {}

        // Set realistic baseline MRP for existing products where MRP is not set yet
        try {
            $pdo->exec("
                UPDATE products 
                SET mrp = CASE 
                    WHEN price >= 250 THEN 699.00
                    WHEN price >= 150 THEN 499.00
                    WHEN price > 0 THEN 299.00
                    ELSE NULL 
                END 
                WHERE mrp IS NULL AND price > 0
            ");
        } catch (\Throwable $e) {}

        // Ensure columns in reviews exist
        try { $pdo->exec("ALTER TABLE reviews ADD COLUMN author_role VARCHAR(50) DEFAULT 'Verified Student'"); } catch (\Throwable $e) {}

        // Ensure categories table exists
        try {
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS categories (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    slug TEXT UNIQUE NOT NULL,
                    description TEXT DEFAULT NULL,
                    icon TEXT DEFAULT 'fa-solid fa-folder',
                    display_order INTEGER DEFAULT 0,
                    is_active INTEGER DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ");
        } catch (\Throwable $e) {
            try {
                $pdo->exec("
                    CREATE TABLE IF NOT EXISTS `categories` (
                        `id` INT AUTO_INCREMENT PRIMARY KEY,
                        `name` VARCHAR(255) NOT NULL,
                        `slug` VARCHAR(255) NOT NULL UNIQUE,
                        `description` TEXT DEFAULT NULL,
                        `icon` VARCHAR(100) DEFAULT 'fa-solid fa-folder',
                        `display_order` INT DEFAULT 0,
                        `is_active` TINYINT(1) DEFAULT 1,
                        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                ");
            } catch (\Throwable $e2) {}
        }

        // Seed initial categories if none exist
        try {
            $catCount = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
            if ($catCount === 0) {
                $initialCategories = [
                    ['JEE Main & Advanced', 'jee-main-advanced', 'Comprehensive problem sets, rotation mechanics, calculus drills, and formula guides calibrated for IIT JEE.', 'fa-solid fa-atom', 1],
                    ['NEET (Medical)', 'neet-medical', 'High-yield NCERT diagram cheatsheets, biology DPPs, and chemistry reaction mechanisms.', 'fa-solid fa-dna', 2],
                    ['CBSE Class 11-12', 'cbse-class-11-12', 'Board exam question banks, derivations, NCERT textbook solutions, and printable cheatsheets.', 'fa-solid fa-graduation-cap', 3],
                    ['CBSE Class 9-10', 'cbse-class-9-10', 'Foundation physics, ray diagrams, light and electricity practice sheets for secondary school.', 'fa-solid fa-book-open', 4],
                    ['AP Physics (College Board)', 'ap-physics', 'Calculus and algebra-based AP Physics 1 & AP Physics C unit packs with FRQs.', 'fa-solid fa-globe', 5],
                    ['Olympiad & Competitive', 'olympiad-competitive', 'Advanced non-routine problem solving toolkit for NSEP, INPhO, and competitive science contests.', 'fa-solid fa-trophy', 6],
                    ['Formulas & Cheatsheets', 'formulas-cheatsheets', 'Compact pocket revision cards, mind maps, and quick summary tables for rapid revision.', 'fa-solid fa-bolt', 7]
                ];
                $insCat = $pdo->prepare("INSERT INTO categories (name, slug, description, icon, display_order) VALUES (?, ?, ?, ?, ?)");
                foreach ($initialCategories as $c) {
                    $insCat->execute($c);
                }
            }
        } catch (\Throwable $e) {}

        // Auto-assign category_id and initial tags to existing products if unassigned
        try {
            $pdo->exec("UPDATE products SET category_id = (SELECT id FROM categories WHERE categories.name = products.grade LIMIT 1) WHERE category_id IS NULL OR category_id = 0");
            $pdo->exec("UPDATE products SET category_id = (SELECT id FROM categories WHERE slug = 'ap-physics') WHERE (category_id IS NULL OR category_id = 0) AND (grade LIKE '%AP%' OR title LIKE '%AP%')");
            $pdo->exec("UPDATE products SET category_id = (SELECT id FROM categories WHERE slug = 'jee-main-advanced') WHERE (category_id IS NULL OR category_id = 0) AND (grade LIKE '%JEE%' OR title LIKE '%JEE%')");
            $pdo->exec("UPDATE products SET category_id = (SELECT id FROM categories WHERE slug = 'neet-medical') WHERE (category_id IS NULL OR category_id = 0) AND (grade LIKE '%NEET%' OR title LIKE '%NEET%')");
            $pdo->exec("UPDATE products SET category_id = (SELECT id FROM categories WHERE slug = 'cbse-class-11-12') WHERE (category_id IS NULL OR category_id = 0) AND grade LIKE '%11-12%'");
            $pdo->exec("UPDATE products SET category_id = (SELECT id FROM categories WHERE slug = 'cbse-class-9-10') WHERE (category_id IS NULL OR category_id = 0) AND grade LIKE '%9-10%'");
            $pdo->exec("UPDATE products SET category_id = (SELECT id FROM categories WHERE slug = 'olympiad-competitive') WHERE (category_id IS NULL OR category_id = 0) AND grade LIKE '%Olympiad%'");

            // Seed sample tags if tags is null or empty
            $pdo->exec("UPDATE products SET tags = 'Mechanics, Rotation, Angular Momentum, JEE Advanced, High Yield' WHERE (tags IS NULL OR tags = '') AND title LIKE '%Mechanics%'");
            $pdo->exec("UPDATE products SET tags = 'NCERT, Human Physiology, Genetics, Diagrams, NEET 2026' WHERE (tags IS NULL OR tags = '') AND title LIKE '%NEET Biology%'");
            $pdo->exec("UPDATE products SET tags = 'Organic Chemistry, Reaction Roadmaps, NCERT Line-by-Line, Chemistry' WHERE (tags IS NULL OR tags = '') AND title LIKE '%NEET Chemistry%'");
            $pdo->exec("UPDATE products SET tags = 'Calculus, Electrodynamics, Gauss Law, College Board, AP Physics' WHERE (tags IS NULL OR tags = '') AND title LIKE '%AP Physics C%'");
            $pdo->exec("UPDATE products SET tags = 'Kinematics, Dynamics, Energy, FRQ, AP Physics 1' WHERE (tags IS NULL OR tags = '') AND title LIKE '%AP Physics 1%'");
            $pdo->exec("UPDATE products SET tags = 'Formulas, Quick Revision, Freebie, Class 12, Class 11, Cheat Sheet' WHERE (tags IS NULL OR tags = '') AND title LIKE '%Formula Master%'");
            $pdo->exec("UPDATE products SET tags = 'Calculus, Definite Integrals, Speed Drills, Mathematics, JEE Main' WHERE (tags IS NULL OR tags = '') AND title LIKE '%Calculus & Algebra Drills%'");
            $pdo->exec("UPDATE products SET tags = 'Ray Optics, Electricity, Class 10, Freebie, NCERT Diagrams' WHERE (tags IS NULL OR tags = '') AND title LIKE '%Class 10 Light%'");
            $pdo->exec("UPDATE products SET tags = 'Olympiad, NSEP, INPhO, Non-Routine Problems, Advanced Physics' WHERE (tags IS NULL OR tags = '') AND title LIKE '%Olympiad%'");
            $pdo->exec("UPDATE products SET tags = 'Physics, Study Notes, Question Bank, Lockinrank' WHERE tags IS NULL OR tags = ''");
        } catch (\Throwable $e) {}
    }

    private static function migrateAndSeed(PDO $db): void
    {
        $db->exec("
            CREATE TABLE IF NOT EXISTS stores (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                slug TEXT UNIQUE NOT NULL,
                author_name TEXT NOT NULL,
                avatar TEXT,
                banner_color TEXT DEFAULT '#4f46e5',
                bio TEXT,
                upi_id TEXT DEFAULT 'lockinrank@upi',
                rating REAL DEFAULT 5.0,
                total_reviews INTEGER DEFAULT 0,
                total_sales INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                store_id INTEGER NOT NULL,
                title TEXT NOT NULL,
                slug TEXT UNIQUE NOT NULL,
                description TEXT NOT NULL,
                price REAL NOT NULL,
                mrp REAL DEFAULT NULL,
                is_free INTEGER DEFAULT 0,
                grade TEXT NOT NULL,
                board TEXT NOT NULL DEFAULT 'JEE / NTA Pattern',
                subject TEXT NOT NULL,
                resource_type TEXT NOT NULL,
                file_format TEXT NOT NULL,
                page_count INTEGER NOT NULL,
                is_ink_friendly INTEGER DEFAULT 1,
                standards TEXT,
                thumbnail_url TEXT,
                preview_images TEXT,
                rating REAL DEFAULT 5.0,
                review_count INTEGER DEFAULT 0,
                sales_count INTEGER DEFAULT 0,
                sample_downloads INTEGER DEFAULT 0,
                material_file_path TEXT,
                sample_file_path TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (store_id) REFERENCES stores (id)
            );

            CREATE TABLE IF NOT EXISTS reviews (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                product_id INTEGER NOT NULL,
                author_name TEXT NOT NULL,
                rating INTEGER NOT NULL,
                comment TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (product_id) REFERENCES products (id)
            );

            CREATE TABLE IF NOT EXISTS orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_number TEXT UNIQUE NOT NULL,
                buyer_name TEXT NOT NULL,
                buyer_email TEXT NOT NULL,
                buyer_phone TEXT,
                payment_method TEXT DEFAULT 'Razorpay',
                razorpay_order_id TEXT,
                razorpay_payment_id TEXT,
                razorpay_signature TEXT,
                payment_status TEXT DEFAULT 'pending',
                total_amount REAL NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS order_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                price REAL NOT NULL,
                download_token TEXT NOT NULL,
                download_count INTEGER DEFAULT 0,
                FOREIGN KEY (order_id) REFERENCES orders (id),
                FOREIGN KEY (product_id) REFERENCES products (id)
            );
        ");

        self::seedStoreAndProducts($db);
    }

    public static function rebrandStoreAndProducts(PDO $db): void
    {
        $db->exec("DELETE FROM reviews");
        $db->exec("DELETE FROM products");
        $db->exec("DELETE FROM stores");
        self::seedStoreAndProducts($db);
    }

    private static function seedStoreAndProducts(PDO $db): void
    {
        // Seed Single Official Lockinrank Store
        $storeInsert = $db->prepare("
            INSERT INTO stores (name, slug, author_name, avatar, banner_color, bio, upi_id, rating, total_reviews, total_sales)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $storeInsert->execute([
            'Lockinrank Official Store',
            'official-store',
            'Lockinrank Academic Team',
            'https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=150&auto=format&fit=crop&q=80',
            '#4f46e5',
            'Official digital store of Lockinrank (shop.lockinrank.com). High-yield problem drills, formula sheets, ray diagrams, and test series for JEE Main, JEE Advanced, NEET, CBSE Boards, and International AP Physics.',
            'lockinrank@upi',
            4.95, 342, 2850
        ]);
        $store1 = $db->lastInsertId();

        $prodInsert = $db->prepare("
            INSERT INTO products 
            (store_id, title, slug, description, price, is_free, grade, board, subject, resource_type, file_format, page_count, is_ink_friendly, standards, thumbnail_url, preview_images, rating, review_count, sales_count)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $products = [
            [
                $store1,
                'JEE Advanced Physics: Mechanics & Rotation Master Problem Set',
                'jee-advanced-physics-mechanics-rotation-master',
                '64-page advanced problem set covering Newton Laws, Work-Energy, Center of Mass, Rotational Dynamics, and Angular Momentum conservation. Features multi-correct, integer-type, and matrix-match questions with step-by-step rigorous solutions.',
                199.00, 0, 'JEE Main & Advanced', 'JEE / NTA Pattern', 'Physics', 'Unit Plan', 'PDF (Printable)', 64, 1,
                'JEE Advanced Physics Mechanics Level 1-3 with Detailed Sol',
                'https://images.unsplash.com/photo-1635070041078-e363dbe005cb?w=600&auto=format&fit=crop&q=80',
                json_encode([
                    'https://images.unsplash.com/photo-1635070041078-e363dbe005cb?w=600&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1509062522246-3755977927d7?w=600&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=600&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600&auto=format&fit=crop&q=80'
                ]),
                4.9, 142, 890
            ],
            [
                $store1,
                'NEET Biology: 1000 High-Yield NCERT DPPs & Diagram Revision Cheatsheets',
                'neet-biology-1000-high-yield-dpps-diagrams',
                'Comprehensive NEET medical entrance workbook. Covers Genetics, Human Physiology, Ecology, Cell Biology, and Plant Anatomy. Includes 100% labeled NCERT diagrams and assertion-reason daily practice papers.',
                149.00, 0, 'NEET (Medical)', 'NEET / NTA Pattern', 'Biology', 'Worksheet', 'PDF (Printable)', 55, 1,
                '100% NCERT Class 11 & 12 Line-by-Line Alignment',
                'https://images.unsplash.com/photo-1530210124550-912dc1381cb8?w=600&auto=format&fit=crop&q=80',
                json_encode([
                    'https://images.unsplash.com/photo-1530210124550-912dc1381cb8?w=600&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=600&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1576086213369-97a306d36557?w=600&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1509062522246-3755977927d7?w=600&auto=format&fit=crop&q=80'
                ]),
                4.9, 118, 760
            ],
            [
                $store1,
                'AP Physics C (Mechanics & E&M): International Exam Prep Drills & Formula Cards',
                'ap-physics-c-mechanics-em-prep-drills',
                'Engineered for international AP students aiming for a 5. Includes calculus-based mechanics, rotational inertia integrations, Gauss Law, Ampere Law, and electromagnetic induction. Packed with College Board style Free Response Questions (FRQs).',
                249.00, 0, 'AP Physics (College Board)', 'College Board (AP)', 'Physics', 'Unit Plan', 'PDF (Printable)', 50, 1,
                'College Board AP Physics C: Mechanics & E&M',
                'https://images.unsplash.com/photo-1509228468518-180dd4864904?w=600&auto=format&fit=crop&q=80',
                json_encode([
                    'https://images.unsplash.com/photo-1509228468518-180dd4864904?w=600&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1635070041078-e363dbe005cb?w=600&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600&auto=format&fit=crop&q=80'
                ]),
                5.0, 89, 430
            ],
            [
                $store1,
                'AP Physics 1: Complete Kinematics, Dynamics & Energy Unit Workbook',
                'ap-physics-1-kinematics-dynamics-workbook',
                'Conceptual and algebra-based AP Physics 1 toolkit. Covers 1D/2D kinematics, projectile motion, friction on inclines, circular motion, and conservation of mechanical energy with Paragraph Argument Short Answer prompts.',
                199.00, 0, 'AP Physics (College Board)', 'College Board (AP)', 'Physics', 'Worksheet', 'PDF (Printable)', 42, 1,
                'College Board AP Physics 1 Unit 1-4 Standards',
                'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=600&auto=format&fit=crop&q=80',
                json_encode([
                    'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=600&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1509228468518-180dd4864904?w=600&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=600&auto=format&fit=crop&q=80'
                ]),
                4.8, 64, 380
            ],
            [
                $store1,
                'Class 11 & 12 Physics & Maths Formula Master Cheatsheet [100% FREEBIE]',
                'class-11-12-physics-maths-formula-cheatsheet-freebie',
                'Download this free 20-page pocket formula guide! Complete formulas for Vectors, Calculus, Thermodynamics, Ray Optics, Modern Physics, and Matrices. High-contrast, printer-friendly B&W layout.',
                0.00, 1, 'CBSE Class 11-12', 'CBSE', 'Physics', 'Printable', 'PDF', 20, 1,
                'CBSE Board Exam & JEE Main Quick Revision',
                'https://images.unsplash.com/photo-1596495578065-6e0763fa1178?w=600&auto=format&fit=crop&q=80',
                json_encode([
                    'https://images.unsplash.com/photo-1596495578065-6e0763fa1178?w=600&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1509062522246-3755977927d7?w=600&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=600&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600&auto=format&fit=crop&q=80'
                ]),
                5.0, 210, 1620
            ],
            [
                $store1,
                'JEE Main 2026 Mathematics: 300 High-Probability Calculus & Algebra Drills',
                'jee-main-2026-mathematics-calculus-algebra-drills',
                'Targeted 300 question speed-drill pack covering Limits, Continuity, Definite Integrals, Differential Equations, Vectors & 3D Geometry, and Complex Numbers. Built for speed and accuracy in CBT mode.',
                149.00, 0, 'JEE Main & Advanced', 'JEE / NTA Pattern', 'Mathematics', 'Unit Plan', 'PDF (Printable)', 48, 1,
                'NTA JEE Main Mathematics Standard',
                'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=600&auto=format&fit=crop&q=80',
                json_encode([
                    'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=600&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1596495578065-6e0763fa1178?w=600&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=600&auto=format&fit=crop&q=80'
                ]),
                4.9, 95, 590
            ],
            [
                $store1,
                'CBSE Class 10 Light, Electricity & Magnetism Diagram Handbook [FREEBIE]',
                'cbse-class-10-light-electricity-diagram-handbook-freebie',
                'Master Ray Diagrams (Concave/Convex mirrors, lenses), Solenoid magnetic field lines, domestic electric circuits, and sign conventions. 100% free download for high-scoring Class 10 board preparation.',
                0.00, 1, 'CBSE Class 9-10', 'CBSE', 'Physics', 'Worksheet', 'PDF', 16, 1,
                'CBSE Class 10 Board Exam Physics (NCERT)',
                'https://images.unsplash.com/photo-1530595467537-0b5996c41f2d?w=600&auto=format&fit=crop&q=80',
                json_encode([
                    'https://images.unsplash.com/photo-1530595467537-0b5996c41f2d?w=600&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1635070041078-e363dbe005cb?w=600&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1509062522246-3755977927d7?w=600&auto=format&fit=crop&q=80'
                ]),
                4.9, 165, 1240
            ],
            [
                $store1,
                'Physics Olympiad (NSEP / INPhO) Advanced Problem Solving Toolkit',
                'physics-olympiad-nsep-inpho-toolkit',
                'High-level non-routine problems in Classical Mechanics, Electrodynamics, and Thermodynamics for National Standard Examination in Physics (NSEP) and International Physics Olympiad aspirants.',
                249.00, 0, 'Olympiad & Competitive', 'International Baccalaureate (IB)', 'Physics', 'Unit Plan', 'PDF (Printable)', 60, 1,
                'IAPT NSEP & International Olympiad Level',
                'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=600&auto=format&fit=crop&q=80',
                json_encode([
                    'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=600&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1509228468518-180dd4864904?w=600&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600&auto=format&fit=crop&q=80'
                ]),
                5.0, 72, 340
            ]
        ];

        foreach ($products as $p) {
            $prodInsert->execute($p);
            $pid = $db->lastInsertId();

            $rev = $db->prepare("INSERT INTO reviews (product_id, author_name, rating, comment) VALUES (?, ?, ?, ?)");
            $rev->execute([$pid, 'Aakash R. (AIR 412, JEE Advanced Aspirant)', 5, 'The rotational mechanics problem set is top-tier. Solutions don\'t skip steps like standard answer keys do. Must-have for serious aspirants!']);
            $rev->execute([$pid, 'Sarah L. (AP Physics C Student, IB World School)', 5, 'Crystal clear calculus derivations and FRQ mock questions. Exactly what I needed for getting a 5 on the College Board exam!']);
        }
    }
}
