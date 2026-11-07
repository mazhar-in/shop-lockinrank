<?php

namespace App;

use PDO;

class Database
{
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            $dbPath = __DIR__ . '/../data/tpt.db';
            $isNew = !file_exists($dbPath);

            self::$pdo = new PDO("sqlite:" . $dbPath);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            if ($isNew) {
                self::migrateAndSeed(self::$pdo);
            } else {
                try { self::$pdo->exec("ALTER TABLE orders ADD COLUMN razorpay_order_id TEXT"); } catch (\Throwable $e) {}
                try { self::$pdo->exec("ALTER TABLE orders ADD COLUMN razorpay_payment_id TEXT"); } catch (\Throwable $e) {}
                try { self::$pdo->exec("ALTER TABLE orders ADD COLUMN razorpay_signature TEXT"); } catch (\Throwable $e) {}
                try { self::$pdo->exec("ALTER TABLE orders ADD COLUMN payment_status TEXT DEFAULT 'pending'"); } catch (\Throwable $e) {}
                try { self::$pdo->exec("ALTER TABLE products ADD COLUMN sample_downloads INTEGER DEFAULT 0"); } catch (\Throwable $e) {}
                try { self::$pdo->exec("ALTER TABLE products ADD COLUMN material_file_path TEXT"); } catch (\Throwable $e) {}
                try { self::$pdo->exec("ALTER TABLE products ADD COLUMN sample_file_path TEXT"); } catch (\Throwable $e) {}

                // Auto-migrate branding if existing store has old brand
                try {
                    $store = self::$pdo->query("SELECT name FROM stores WHERE id = 1")->fetch();
                    if ($store && str_contains($store['name'], 'TeachShare')) {
                        self::rebrandStoreAndProducts(self::$pdo);
                    }
                } catch (\Throwable $e) {}
            }
        }
        return self::$pdo;
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
