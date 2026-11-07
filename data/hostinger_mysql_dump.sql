-- Lockinrank Shop MySQL Database Dump for Hostinger
-- Target Database: u258212572_shop
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;


CREATE TABLE IF NOT EXISTS `stores` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(191) NOT NULL UNIQUE,
    `author_name` VARCHAR(255) NOT NULL,
    `avatar` TEXT,
    `banner_color` VARCHAR(50) DEFAULT '#4f46e5',
    `bio` TEXT,
    `upi_id` VARCHAR(100) DEFAULT 'lockinrank@upi',
    `rating` DECIMAL(3,2) DEFAULT 5.0,
    `total_reviews` INT DEFAULT 0,
    `total_sales` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `store_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(191) NOT NULL UNIQUE,
    `description` LONGTEXT NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `is_free` TINYINT(1) DEFAULT 0,
    `grade` VARCHAR(100) NOT NULL,
    `board` VARCHAR(100) NOT NULL DEFAULT 'JEE / NTA Pattern',
    `subject` VARCHAR(100) NOT NULL,
    `resource_type` VARCHAR(100) NOT NULL,
    `file_format` VARCHAR(50) NOT NULL,
    `page_count` INT NOT NULL,
    `is_ink_friendly` TINYINT(1) DEFAULT 1,
    `standards` VARCHAR(255),
    `thumbnail_url` TEXT,
    `preview_images` LONGTEXT,
    `rating` DECIMAL(3,2) DEFAULT 5.0,
    `review_count` INT DEFAULT 0,
    `sales_count` INT DEFAULT 0,
    `sample_downloads` INT DEFAULT 0,
    `material_file_path` TEXT,
    `sample_file_path` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `reviews` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_id` INT NOT NULL,
    `author_name` VARCHAR(255) NOT NULL,
    `rating` INT NOT NULL,
    `comment` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_number` VARCHAR(100) NOT NULL UNIQUE,
    `buyer_name` VARCHAR(255) NOT NULL,
    `buyer_email` VARCHAR(255) NOT NULL,
    `buyer_phone` VARCHAR(50),
    `payment_method` VARCHAR(50) DEFAULT 'Razorpay',
    `razorpay_order_id` VARCHAR(100) DEFAULT NULL,
    `razorpay_payment_id` VARCHAR(100) DEFAULT NULL,
    `razorpay_signature` VARCHAR(255) DEFAULT NULL,
    `payment_status` VARCHAR(50) DEFAULT 'pending',
    `total_amount` DECIMAL(10,2) NOT NULL,
    `download_token` VARCHAR(100) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `download_token` VARCHAR(100) DEFAULT NULL,
    `download_count` INT DEFAULT 0,
    `expires_at` DATETIME DEFAULT NULL,
    KEY `idx_order_id` (`order_id`),
    KEY `idx_download_token` (`download_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `stores`
INSERT INTO `stores` (`id`, `name`, `slug`, `author_name`, `avatar`, `banner_color`, `bio`, `upi_id`, `rating`, `total_reviews`, `total_sales`, `created_at`) VALUES (3, 'Lockinrank Official Store', 'official-store', 'Lockinrank Academic Team', 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=150&auto=format&fit=crop&q=80', '#4f46e5', 'Official digital store of Lockinrank (shop.lockinrank.com). High-yield problem drills, formula sheets, ray diagrams, and test series for JEE Main, JEE Advanced, NEET, CBSE Boards, and International AP Physics.', 'lockinrank@upi', 4.95, 342, 2850, '2026-10-25 15:23:41') ON DUPLICATE KEY UPDATE id=id;

-- Data for table `products`
INSERT INTO `products` (`id`, `store_id`, `title`, `slug`, `description`, `price`, `is_free`, `grade`, `board`, `subject`, `resource_type`, `file_format`, `page_count`, `is_ink_friendly`, `standards`, `thumbnail_url`, `preview_images`, `rating`, `review_count`, `sales_count`, `created_at`, `sample_downloads`, `material_file_path`, `sample_file_path`) VALUES (17, 3, 'JEE Advanced Physics: Mechanics & Rotation Master Problem Set', 'jee-advanced-physics-mechanics-rotation-master', '64-page advanced problem set covering Newton Laws, Work-Energy, Center of Mass, Rotational Dynamics, and Angular Momentum conservation. Features multi-correct, integer-type, and matrix-match questions with step-by-step rigorous solutions.', 199.0, 0, 'JEE Main & Advanced', 'JEE / NTA Pattern', 'Physics', 'Unit Plan', 'PDF (Printable)', 64, 1, 'JEE Advanced Physics Mechanics Level 1-3 with Detailed Sol', 'https://images.unsplash.com/photo-1635070041078-e363dbe005cb?w=600&auto=format&fit=crop&q=80', '["https:\\/\\/images.unsplash.com\\/photo-1635070041078-e363dbe005cb?w=600&auto=format&fit=crop&q=80"]', 4.9, 142, 890, '2026-10-25 15:23:41', 2674, '20261026_014853_a2b30b34_scratch_material.zip', '20261026_014853_30d8bcef_scratch_sample.pdf') ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `products` (`id`, `store_id`, `title`, `slug`, `description`, `price`, `is_free`, `grade`, `board`, `subject`, `resource_type`, `file_format`, `page_count`, `is_ink_friendly`, `standards`, `thumbnail_url`, `preview_images`, `rating`, `review_count`, `sales_count`, `created_at`, `sample_downloads`, `material_file_path`, `sample_file_path`) VALUES (18, 3, 'NEET Biology: 1000 High-Yield NCERT DPPs & Diagram Revision Cheatsheets', 'neet-biology-1000-high-yield-dpps-diagrams', 'Comprehensive NEET medical entrance workbook. Covers Genetics, Human Physiology, Ecology, Cell Biology, and Plant Anatomy. Includes 100% labeled NCERT diagrams and assertion-reason daily practice papers.', 149.0, 0, 'NEET (Medical)', 'NEET / NTA Pattern', 'Biology', 'Worksheet', 'PDF (Printable)', 55, 1, '100% NCERT Class 11 & 12 Line-by-Line Alignment', 'https://images.unsplash.com/photo-1530210124550-912dc1381cb8?w=600&auto=format&fit=crop&q=80', '["https:\\/\\/images.unsplash.com\\/photo-1530210124550-912dc1381cb8?w=600&auto=format&fit=crop&q=80","https:\\/\\/images.unsplash.com\\/photo-1532094349884-543bc11b234d?w=600&auto=format&fit=crop&q=80","https:\\/\\/images.unsplash.com\\/photo-1576086213369-97a306d36557?w=600&auto=format&fit=crop&q=80","https:\\/\\/images.unsplash.com\\/photo-1509062522246-3755977927d7?w=600&auto=format&fit=crop&q=80"]', 4.9, 118, 760, '2026-10-25 15:23:41', 2280, NULL, NULL) ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `products` (`id`, `store_id`, `title`, `slug`, `description`, `price`, `is_free`, `grade`, `board`, `subject`, `resource_type`, `file_format`, `page_count`, `is_ink_friendly`, `standards`, `thumbnail_url`, `preview_images`, `rating`, `review_count`, `sales_count`, `created_at`, `sample_downloads`, `material_file_path`, `sample_file_path`) VALUES (19, 3, 'AP Physics C (Mechanics & E&M): International Exam Prep Drills & Formula Cards', 'ap-physics-c-mechanics-em-prep-drills', 'Engineered for international AP students aiming for a 5. Includes calculus-based mechanics, rotational inertia integrations, Gauss Law, Ampere Law, and electromagnetic induction. Packed with College Board style Free Response Questions (FRQs).', 249.0, 0, 'AP Physics (College Board)', 'College Board (AP)', 'Physics', 'Unit Plan', 'PDF (Printable)', 50, 1, 'College Board AP Physics C: Mechanics & E&M', 'https://images.unsplash.com/photo-1509228468518-180dd4864904?w=600&auto=format&fit=crop&q=80', '["https:\\/\\/images.unsplash.com\\/photo-1509228468518-180dd4864904?w=600&auto=format&fit=crop&q=80","https:\\/\\/images.unsplash.com\\/photo-1635070041078-e363dbe005cb?w=600&auto=format&fit=crop&q=80","https:\\/\\/images.unsplash.com\\/photo-1516321318423-f06f85e504b3?w=600&auto=format&fit=crop&q=80"]', 5.0, 89, 430, '2026-10-25 15:23:41', 1290, NULL, NULL) ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `products` (`id`, `store_id`, `title`, `slug`, `description`, `price`, `is_free`, `grade`, `board`, `subject`, `resource_type`, `file_format`, `page_count`, `is_ink_friendly`, `standards`, `thumbnail_url`, `preview_images`, `rating`, `review_count`, `sales_count`, `created_at`, `sample_downloads`, `material_file_path`, `sample_file_path`) VALUES (20, 3, 'AP Physics 1: Complete Kinematics, Dynamics & Energy Unit Workbook', 'ap-physics-1-kinematics-dynamics-workbook', 'Conceptual and algebra-based AP Physics 1 toolkit. Covers 1D/2D kinematics, projectile motion, friction on inclines, circular motion, and conservation of mechanical energy with Paragraph Argument Short Answer prompts.', 199.0, 0, 'AP Physics (College Board)', 'College Board (AP)', 'Physics', 'Worksheet', 'PDF (Printable)', 42, 1, 'College Board AP Physics 1 Unit 1-4 Standards', 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=600&auto=format&fit=crop&q=80', '["https:\\/\\/images.unsplash.com\\/photo-1451187580459-43490279c0fa?w=600&auto=format&fit=crop&q=80","https:\\/\\/images.unsplash.com\\/photo-1509228468518-180dd4864904?w=600&auto=format&fit=crop&q=80","https:\\/\\/images.unsplash.com\\/photo-1434030216411-0b793f4b4173?w=600&auto=format&fit=crop&q=80"]', 4.8, 64, 380, '2026-10-25 15:23:41', 1140, NULL, NULL) ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `products` (`id`, `store_id`, `title`, `slug`, `description`, `price`, `is_free`, `grade`, `board`, `subject`, `resource_type`, `file_format`, `page_count`, `is_ink_friendly`, `standards`, `thumbnail_url`, `preview_images`, `rating`, `review_count`, `sales_count`, `created_at`, `sample_downloads`, `material_file_path`, `sample_file_path`) VALUES (21, 3, 'Class 11 & 12 Physics & Maths Formula Master Cheatsheet [100% FREEBIE]', 'class-11-12-physics-maths-formula-cheatsheet-freebie', 'Download this free 20-page pocket formula guide! Complete formulas for Vectors, Calculus, Thermodynamics, Ray Optics, Modern Physics, and Matrices. High-contrast, printer-friendly B&W layout.', 0.0, 1, 'CBSE Class 11-12', 'CBSE', 'Physics', 'Printable', 'PDF', 20, 1, 'CBSE Board Exam & JEE Main Quick Revision', 'https://images.unsplash.com/photo-1596495578065-6e0763fa1178?w=600&auto=format&fit=crop&q=80', '["https:\\/\\/images.unsplash.com\\/photo-1596495578065-6e0763fa1178?w=600&auto=format&fit=crop&q=80","https:\\/\\/images.unsplash.com\\/photo-1509062522246-3755977927d7?w=600&auto=format&fit=crop&q=80","https:\\/\\/images.unsplash.com\\/photo-1497633762265-9d179a990aa6?w=600&auto=format&fit=crop&q=80","https:\\/\\/images.unsplash.com\\/photo-1516321318423-f06f85e504b3?w=600&auto=format&fit=crop&q=80"]', 5.0, 210, 1621, '2026-10-25 15:23:41', 0, NULL, NULL) ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `products` (`id`, `store_id`, `title`, `slug`, `description`, `price`, `is_free`, `grade`, `board`, `subject`, `resource_type`, `file_format`, `page_count`, `is_ink_friendly`, `standards`, `thumbnail_url`, `preview_images`, `rating`, `review_count`, `sales_count`, `created_at`, `sample_downloads`, `material_file_path`, `sample_file_path`) VALUES (22, 3, 'JEE Main 2026 Mathematics: 300 High-Probability Calculus & Algebra Drills', 'jee-main-2026-mathematics-calculus-algebra-drills', 'Targeted 300 question speed-drill pack covering Limits, Continuity, Definite Integrals, Differential Equations, Vectors & 3D Geometry, and Complex Numbers. Built for speed and accuracy in CBT mode.', 149.0, 0, 'JEE Main & Advanced', 'JEE / NTA Pattern', 'Mathematics', 'Unit Plan', 'PDF (Printable)', 48, 1, 'NTA JEE Main Mathematics Standard', 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?w=600&auto=format&fit=crop&q=80', '["https:\\/\\/images.unsplash.com\\/photo-1497633762265-9d179a990aa6?w=600&auto=format&fit=crop&q=80","https:\\/\\/images.unsplash.com\\/photo-1596495578065-6e0763fa1178?w=600&auto=format&fit=crop&q=80","https:\\/\\/images.unsplash.com\\/photo-1434030216411-0b793f4b4173?w=600&auto=format&fit=crop&q=80"]', 4.9, 95, 590, '2026-10-25 15:23:41', 1770, NULL, NULL) ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `products` (`id`, `store_id`, `title`, `slug`, `description`, `price`, `is_free`, `grade`, `board`, `subject`, `resource_type`, `file_format`, `page_count`, `is_ink_friendly`, `standards`, `thumbnail_url`, `preview_images`, `rating`, `review_count`, `sales_count`, `created_at`, `sample_downloads`, `material_file_path`, `sample_file_path`) VALUES (23, 3, 'CBSE Class 10 Light, Electricity & Magnetism Diagram Handbook [FREEBIE]', 'cbse-class-10-light-electricity-diagram-handbook-freebie', 'Master Ray Diagrams (Concave/Convex mirrors, lenses), Solenoid magnetic field lines, domestic electric circuits, and sign conventions. 100% free download for high-scoring Class 10 board preparation.', 0.0, 1, 'CBSE Class 9-10', 'CBSE', 'Physics', 'Worksheet', 'PDF', 16, 1, 'CBSE Class 10 Board Exam Physics (NCERT)', 'https://images.unsplash.com/photo-1530595467537-0b5996c41f2d?w=600&auto=format&fit=crop&q=80', '["https:\\/\\/images.unsplash.com\\/photo-1530595467537-0b5996c41f2d?w=600&auto=format&fit=crop&q=80","https:\\/\\/images.unsplash.com\\/photo-1635070041078-e363dbe005cb?w=600&auto=format&fit=crop&q=80","https:\\/\\/images.unsplash.com\\/photo-1509062522246-3755977927d7?w=600&auto=format&fit=crop&q=80"]', 4.9, 165, 1240, '2026-10-25 15:23:41', 0, NULL, NULL) ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `products` (`id`, `store_id`, `title`, `slug`, `description`, `price`, `is_free`, `grade`, `board`, `subject`, `resource_type`, `file_format`, `page_count`, `is_ink_friendly`, `standards`, `thumbnail_url`, `preview_images`, `rating`, `review_count`, `sales_count`, `created_at`, `sample_downloads`, `material_file_path`, `sample_file_path`) VALUES (24, 3, 'Physics Olympiad (NSEP / INPhO) Advanced Problem Solving Toolkit', 'physics-olympiad-nsep-inpho-toolkit', 'High-level non-routine problems in Classical Mechanics, Electrodynamics, and Thermodynamics for National Standard Examination in Physics (NSEP) and International Physics Olympiad aspirants.', 249.0, 0, 'Olympiad & Competitive', 'International Baccalaureate (IB)', 'Physics', 'Unit Plan', 'PDF (Printable)', 60, 1, 'IAPT NSEP & International Olympiad Level', 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=600&auto=format&fit=crop&q=80', '["https:\\/\\/images.unsplash.com\\/photo-1456513080510-7bf3a84b82f8?w=600&auto=format&fit=crop&q=80","https:\\/\\/images.unsplash.com\\/photo-1509228468518-180dd4864904?w=600&auto=format&fit=crop&q=80","https:\\/\\/images.unsplash.com\\/photo-1516321318423-f06f85e504b3?w=600&auto=format&fit=crop&q=80"]', 5.0, 72, 340, '2026-10-25 15:23:41', 1020, NULL, NULL) ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `products` (`id`, `store_id`, `title`, `slug`, `description`, `price`, `is_free`, `grade`, `board`, `subject`, `resource_type`, `file_format`, `page_count`, `is_ink_friendly`, `standards`, `thumbnail_url`, `preview_images`, `rating`, `review_count`, `sales_count`, `created_at`, `sample_downloads`, `material_file_path`, `sample_file_path`) VALUES (25, 3, 'Test Multi Image Upload Product', 'test-multi-image-upload-product-516', 'This is a test product created to verify multiple image uploading.', 99.0, 0, 'JEE Main & Advanced', 'JEE / NTA Pattern', 'Physics', 'Worksheet', 'PDF (Printable)', 15, 1, 'Test Alignment', '/uploads/products/prod_20261026_010601_60b5a5a6.svg', '["\\/uploads\\/products\\/prod_20261026_010601_60b5a5a6.svg","\\/uploads\\/products\\/prod_20261026_010601_e53e80b3.svg"]', 5.0, 0, 0, '2026-10-26 00:06:01', 0, NULL, NULL) ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `products` (`id`, `store_id`, `title`, `slug`, `description`, `price`, `is_free`, `grade`, `board`, `subject`, `resource_type`, `file_format`, `page_count`, `is_ink_friendly`, `standards`, `thumbnail_url`, `preview_images`, `rating`, `review_count`, `sales_count`, `created_at`, `sample_downloads`, `material_file_path`, `sample_file_path`) VALUES (26, 3, 'JEE Advance 2026 High Scoring Physics Sheets', 'jee-advance-2026-high-scoring-physics-sheets-677', 'This is the description', 0.0, 1, 'JEE Main & Advanced', 'JEE / NTA Pattern', 'Physics', 'Worksheet', 'PDF (Printable)', 45, 1, '', '/uploads/products/prod_20261026_013915_c0d41ff9.png', '["\\/uploads\\/products\\/prod_20261026_013915_c0d41ff9.png","\\/uploads\\/products\\/prod_20261026_013915_a2ad24d1.png","\\/uploads\\/products\\/prod_20261026_013915_97979d20.png"]', 5.0, 0, 1, '2026-10-26 00:39:15', 0, '20261026_015118_1408b753_dpp_01__2_.pdf', NULL) ON DUPLICATE KEY UPDATE id=id;

-- Data for table `reviews`
INSERT INTO `reviews` (`id`, `product_id`, `author_name`, `rating`, `comment`, `created_at`) VALUES (33, 17, 'Aakash R. (AIR 412, JEE Advanced Aspirant)', 5, 'The rotational mechanics problem set is top-tier. Solutions don\'t skip steps like standard answer keys do. Must-have for serious aspirants!', '2026-10-25 15:23:41') ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `reviews` (`id`, `product_id`, `author_name`, `rating`, `comment`, `created_at`) VALUES (34, 17, 'Sarah L. (AP Physics C Student, IB World School)', 5, 'Crystal clear calculus derivations and FRQ mock questions. Exactly what I needed for getting a 5 on the College Board exam!', '2026-10-25 15:23:41') ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `reviews` (`id`, `product_id`, `author_name`, `rating`, `comment`, `created_at`) VALUES (35, 18, 'Aakash R. (AIR 412, JEE Advanced Aspirant)', 5, 'The rotational mechanics problem set is top-tier. Solutions don\'t skip steps like standard answer keys do. Must-have for serious aspirants!', '2026-10-25 15:23:41') ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `reviews` (`id`, `product_id`, `author_name`, `rating`, `comment`, `created_at`) VALUES (36, 18, 'Sarah L. (AP Physics C Student, IB World School)', 5, 'Crystal clear calculus derivations and FRQ mock questions. Exactly what I needed for getting a 5 on the College Board exam!', '2026-10-25 15:23:41') ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `reviews` (`id`, `product_id`, `author_name`, `rating`, `comment`, `created_at`) VALUES (37, 19, 'Aakash R. (AIR 412, JEE Advanced Aspirant)', 5, 'The rotational mechanics problem set is top-tier. Solutions don\'t skip steps like standard answer keys do. Must-have for serious aspirants!', '2026-10-25 15:23:41') ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `reviews` (`id`, `product_id`, `author_name`, `rating`, `comment`, `created_at`) VALUES (38, 19, 'Sarah L. (AP Physics C Student, IB World School)', 5, 'Crystal clear calculus derivations and FRQ mock questions. Exactly what I needed for getting a 5 on the College Board exam!', '2026-10-25 15:23:41') ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `reviews` (`id`, `product_id`, `author_name`, `rating`, `comment`, `created_at`) VALUES (39, 20, 'Aakash R. (AIR 412, JEE Advanced Aspirant)', 5, 'The rotational mechanics problem set is top-tier. Solutions don\'t skip steps like standard answer keys do. Must-have for serious aspirants!', '2026-10-25 15:23:41') ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `reviews` (`id`, `product_id`, `author_name`, `rating`, `comment`, `created_at`) VALUES (40, 20, 'Sarah L. (AP Physics C Student, IB World School)', 5, 'Crystal clear calculus derivations and FRQ mock questions. Exactly what I needed for getting a 5 on the College Board exam!', '2026-10-25 15:23:41') ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `reviews` (`id`, `product_id`, `author_name`, `rating`, `comment`, `created_at`) VALUES (41, 21, 'Aakash R. (AIR 412, JEE Advanced Aspirant)', 5, 'The rotational mechanics problem set is top-tier. Solutions don\'t skip steps like standard answer keys do. Must-have for serious aspirants!', '2026-10-25 15:23:41') ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `reviews` (`id`, `product_id`, `author_name`, `rating`, `comment`, `created_at`) VALUES (42, 21, 'Sarah L. (AP Physics C Student, IB World School)', 5, 'Crystal clear calculus derivations and FRQ mock questions. Exactly what I needed for getting a 5 on the College Board exam!', '2026-10-25 15:23:41') ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `reviews` (`id`, `product_id`, `author_name`, `rating`, `comment`, `created_at`) VALUES (43, 22, 'Aakash R. (AIR 412, JEE Advanced Aspirant)', 5, 'The rotational mechanics problem set is top-tier. Solutions don\'t skip steps like standard answer keys do. Must-have for serious aspirants!', '2026-10-25 15:23:41') ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `reviews` (`id`, `product_id`, `author_name`, `rating`, `comment`, `created_at`) VALUES (44, 22, 'Sarah L. (AP Physics C Student, IB World School)', 5, 'Crystal clear calculus derivations and FRQ mock questions. Exactly what I needed for getting a 5 on the College Board exam!', '2026-10-25 15:23:41') ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `reviews` (`id`, `product_id`, `author_name`, `rating`, `comment`, `created_at`) VALUES (45, 23, 'Aakash R. (AIR 412, JEE Advanced Aspirant)', 5, 'The rotational mechanics problem set is top-tier. Solutions don\'t skip steps like standard answer keys do. Must-have for serious aspirants!', '2026-10-25 15:23:41') ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `reviews` (`id`, `product_id`, `author_name`, `rating`, `comment`, `created_at`) VALUES (46, 23, 'Sarah L. (AP Physics C Student, IB World School)', 5, 'Crystal clear calculus derivations and FRQ mock questions. Exactly what I needed for getting a 5 on the College Board exam!', '2026-10-25 15:23:41') ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `reviews` (`id`, `product_id`, `author_name`, `rating`, `comment`, `created_at`) VALUES (47, 24, 'Aakash R. (AIR 412, JEE Advanced Aspirant)', 5, 'The rotational mechanics problem set is top-tier. Solutions don\'t skip steps like standard answer keys do. Must-have for serious aspirants!', '2026-10-25 15:23:41') ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `reviews` (`id`, `product_id`, `author_name`, `rating`, `comment`, `created_at`) VALUES (48, 24, 'Sarah L. (AP Physics C Student, IB World School)', 5, 'Crystal clear calculus derivations and FRQ mock questions. Exactly what I needed for getting a 5 on the College Board exam!', '2026-10-25 15:23:41') ON DUPLICATE KEY UPDATE id=id;

-- Data for table `orders`
INSERT INTO `orders` (`id`, `order_number`, `buyer_name`, `buyer_email`, `buyer_phone`, `payment_method`, `total_amount`, `created_at`, `razorpay_order_id`, `razorpay_payment_id`, `razorpay_signature`, `payment_status`) VALUES (1, 'IN-ORD-214A17', 'Test Buyer', 'test@example.com', '+91 9876543210', 'Razorpay', 149.0, '2026-10-17 17:32:34', 'order_Tp3icvDXiYjKtu', NULL, NULL, 'pending') ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `orders` (`id`, `order_number`, `buyer_name`, `buyer_email`, `buyer_phone`, `payment_method`, `total_amount`, `created_at`, `razorpay_order_id`, `razorpay_payment_id`, `razorpay_signature`, `payment_status`) VALUES (2, 'IN-ORD-C2F8F9', 'Priya Sharma', 'priya@school.in', '+91 98765 43210', 'Razorpay (Verified)', 149.0, '2026-10-17 17:34:52', 'order_Tp3l3O81BhC3Ry', 'pay_test_payment_123', '7905edc1cb35512a1f147e7ffa79503a7ff20616c9e9f8c81c4528435da71ea9', 'paid') ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `orders` (`id`, `order_number`, `buyer_name`, `buyer_email`, `buyer_phone`, `payment_method`, `total_amount`, `created_at`, `razorpay_order_id`, `razorpay_payment_id`, `razorpay_signature`, `payment_status`) VALUES (3, 'IN-ORD-C4042C', 'Pooja Verma', 'pooja@school.in', '+91 91234 56789', 'Free Download', 0.0, '2026-10-17 17:42:36', NULL, NULL, NULL, 'paid') ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `orders` (`id`, `order_number`, `buyer_name`, `buyer_email`, `buyer_phone`, `payment_method`, `total_amount`, `created_at`, `razorpay_order_id`, `razorpay_payment_id`, `razorpay_signature`, `payment_status`) VALUES (4, 'IN-ORD-C98E6E', 'Vikram Singh', 'vikram.singh@kv.edu.in', '+91 98888 77777', 'Razorpay', 99.0, '2026-10-17 17:42:37', 'order_Tp3tE71WIbEhxy', NULL, NULL, 'pending') ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `orders` (`id`, `order_number`, `buyer_name`, `buyer_email`, `buyer_phone`, `payment_method`, `total_amount`, `created_at`, `razorpay_order_id`, `razorpay_payment_id`, `razorpay_signature`, `payment_status`) VALUES (5, 'IN-ORD-049286', 'Priya Sharma', 'priya.sharma@school.in', '+91 98765 43210', 'Razorpay', 99.0, '2026-10-17 17:45:05', 'order_Tp3vpcRp884u7w', NULL, NULL, 'pending') ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `orders` (`id`, `order_number`, `buyer_name`, `buyer_email`, `buyer_phone`, `payment_method`, `total_amount`, `created_at`, `razorpay_order_id`, `razorpay_payment_id`, `razorpay_signature`, `payment_status`) VALUES (6, 'IN-ORD-85C8CC', 'Priya Sharma', 'priya.sharma@school.in', '+91 98765 43210', 'Razorpay', 149.0, '2026-10-25 15:06:17', 'order_TsBV2ifF2G0CC9', NULL, NULL, 'pending') ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `orders` (`id`, `order_number`, `buyer_name`, `buyer_email`, `buyer_phone`, `payment_method`, `total_amount`, `created_at`, `razorpay_order_id`, `razorpay_payment_id`, `razorpay_signature`, `payment_status`) VALUES (7, 'IN-ORD-4E6759', 'Aryan Verma', 'aryan.verma@example.com', '+91 98765 43210', 'Razorpay', 348.0, '2026-10-25 15:40:54', 'order_TsC5bjqSSdjrUT', NULL, NULL, 'pending') ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `orders` (`id`, `order_number`, `buyer_name`, `buyer_email`, `buyer_phone`, `payment_method`, `total_amount`, `created_at`, `razorpay_order_id`, `razorpay_payment_id`, `razorpay_signature`, `payment_status`) VALUES (8, 'IN-ORD-4E2EEB', 'Aryan Verma', 'aryan.verma@example.com', '+91 98765 43210', 'Free Download', 0.0, '2026-10-25 15:41:40', NULL, NULL, NULL, 'paid') ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `orders` (`id`, `order_number`, `buyer_name`, `buyer_email`, `buyer_phone`, `payment_method`, `total_amount`, `created_at`, `razorpay_order_id`, `razorpay_payment_id`, `razorpay_signature`, `payment_status`) VALUES (9, 'IN-ORD-A53BEC', 'Aryan Verma', 'aryan.verma@example.com', '+91 98765 43210', 'Free Download', 0.0, '2026-10-26 00:51:54', NULL, NULL, NULL, 'paid') ON DUPLICATE KEY UPDATE id=id;

-- Data for table `order_items`
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `price`, `download_token`, `download_count`) VALUES (1, 1, 1, 149.0, '22cc990001051b0c11a04075d6deeb95', 0) ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `price`, `download_token`, `download_count`) VALUES (2, 2, 1, 149.0, '033b2dc0d17aae2bf3e032052f4550e2', 0) ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `price`, `download_token`, `download_count`) VALUES (3, 3, 3, 0.0, '3eeadc2c8ef6377d5e65168cc2d7003c', 1) ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `price`, `download_token`, `download_count`) VALUES (4, 4, 2, 99.0, 'b3f7f2d1bd38d201ad1aeaa772c16ad6', 0) ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `price`, `download_token`, `download_count`) VALUES (5, 5, 3, 0.0, '12d90c9573ec2801c1e18f5d4fca5958', 0) ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `price`, `download_token`, `download_count`) VALUES (6, 5, 2, 99.0, '61796ef51e9136d6031c2516a562c4b2', 0) ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `price`, `download_token`, `download_count`) VALUES (7, 6, 1, 149.0, 'a39f17e3e947b8b8b7629857a170e12e', 0) ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `price`, `download_token`, `download_count`) VALUES (8, 7, 1, 149.0, '8230e282300badcea83393544925bdd5', 0) ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `price`, `download_token`, `download_count`) VALUES (9, 7, 20, 199.0, 'f9f3e31c97f4606764ecbd75763c75bd', 0) ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `price`, `download_token`, `download_count`) VALUES (10, 8, 21, 0.0, '8dfe7089ad99fbfae17a5e1f07b908a6', 1) ON DUPLICATE KEY UPDATE id=id;
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `price`, `download_token`, `download_count`) VALUES (11, 9, 26, 0.0, 'eb87e6cec468c3c24544caef897ff351', 1) ON DUPLICATE KEY UPDATE id=id;

SET FOREIGN_KEY_CHECKS = 1;