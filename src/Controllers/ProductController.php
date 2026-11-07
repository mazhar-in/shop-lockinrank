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

        // Get related products prioritizing the same course / exam
        $relStmt = $db->prepare("
            SELECT p.*, s.name as store_name,
                   CASE 
                       WHEN p.grade = :grade AND p.board = :board THEN 4
                       WHEN p.grade = :grade THEN 3
                       WHEN p.board = :board THEN 2
                       WHEN p.subject = :subject THEN 1
                       ELSE 0
                   END as relevance
            FROM products p 
            JOIN stores s ON p.store_id = s.id
            WHERE p.id != :id
            ORDER BY relevance DESC, p.sales_count DESC, p.rating DESC
            LIMIT 4
        ");
        $relStmt->execute([
            ':grade' => $product['grade'],
            ':board' => $product['board'] ?? '',
            ':subject' => $product['subject'] ?? '',
            ':id' => $product['id']
        ]);
        $related = $relStmt->fetchAll();

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
            'previewImages' => array_values(array_filter($previewImages))
        ]);
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
