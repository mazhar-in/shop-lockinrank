<?php

namespace App\Controllers;

use Flight;
use App\Database;

class FeedController
{
    /**
     * Load SEO and domain configuration
     */
    private static function getSeoConfig(): array
    {
        $configPath = __DIR__ . '/../../config/seo.php';
        return file_exists($configPath) ? require $configPath : [
            'base_url' => 'https://shop.lockinrank.com',
            'brand_name' => 'Lockinrank',
            'currency' => 'INR',
            'google_product_category' => 'Media > Books > Non-Fiction'
        ];
    }

    /**
     * Generate dynamic Google Merchant Center (GMC) XML Product Feed.
     * Complies with Google Base RSS 2.0 specifications (xmlns:g="http://base.google.com/ns/1.0").
     */
    public static function googleMerchantXml()
    {
        $config = self::getSeoConfig();
        $baseUrl = rtrim($config['base_url'] ?? 'https://shop.lockinrank.com', '/');
        $currency = $config['currency'] ?? 'INR';
        $brand = $config['brand_name'] ?? 'Lockinrank';
        $defaultCategory = $config['google_product_category'] ?? 'Media > Books > Non-Fiction';

        $db = Database::getConnection();

        // Fetch all active products
        $stmt = $db->query("
            SELECT p.*, s.name as store_name, c.name as category_name
            FROM products p
            LEFT JOIN stores s ON p.store_id = s.id
            LEFT JOIN categories c ON p.category_id = c.id
            ORDER BY p.sales_count DESC, p.id DESC
        ");
        $products = $stmt->fetchAll();

        // Set response headers for clean XML delivery
        header('Content-Type: application/xml; charset=UTF-8');
        header('X-Robots-Tag: noindex'); // Feed is for GMC crawler, not directly indexed as a web page

        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->setIndentString('  ');

        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('rss');
        $xml->writeAttribute('version', '2.0');
        $xml->writeAttribute('xmlns:g', 'http://base.google.com/ns/1.0');

        $xml->startElement('channel');
        $xml->writeElement('title', 'Lockinrank Shop — Official Study Materials & Question Banks');
        $xml->writeElement('link', $baseUrl);
        $xml->writeElement('description', 'High-yield problem drills, formula sheets, ray diagrams, and test series for JEE Main, JEE Advanced, NEET, CBSE Boards, and AP Physics.');
        $xml->writeElement('lastBuildDate', date(DATE_RSS));

        $defaultFallbackImage = $baseUrl . '/images/default-study-material.png';

        foreach ($products as $p) {
            $productUrl = $baseUrl . '/product/' . $p['slug'];

            // Extract preview images
            $rawPreviews = json_decode($p['preview_images'] ?? '[]', true) ?: [];

            // Resolve primary image with verification and fallback
            $imageUrl = self::resolveValidImageUrl($p['thumbnail_url'] ?? '', $baseUrl);
            if (empty($imageUrl)) {
                // Try to find first valid preview image
                foreach ($rawPreviews as $candidate) {
                    $resolved = self::resolveValidImageUrl($candidate, $baseUrl);
                    if (!empty($resolved)) {
                        $imageUrl = $resolved;
                        break;
                    }
                }
            }
            if (empty($imageUrl)) {
                $imageUrl = $defaultFallbackImage;
            }

            // Extract additional preview images (only verified valid images)
            $additionalImages = [];
            foreach ($rawPreviews as $pi) {
                if (!empty($pi)) {
                    $resolvedPi = self::resolveValidImageUrl($pi, $baseUrl);
                    if (!empty($resolvedPi) && $resolvedPi !== $imageUrl && !in_array($resolvedPi, $additionalImages)) {
                        $additionalImages[] = $resolvedPi;
                    }
                }
            }

            // Clean description (remove HTML, limit to 5000 chars)
            $cleanDesc = trim(strip_tags($p['description'] ?? ''));
            if (empty($cleanDesc)) {
                $cleanDesc = $p['title'] . ' - Official study resource by ' . $brand;
            }
            if (mb_strlen($cleanDesc) > 4900) {
                $cleanDesc = mb_substr($cleanDesc, 0, 4900) . '...';
            }

            // Clean price
            $priceValue = (float)($p['is_free'] ? 0.00 : ($p['price'] ?? 0));
            $formattedPrice = number_format($priceValue, 2, '.', '') . ' ' . $currency;

            // Product Type Taxonomy
            $catName = !empty($p['category_name']) ? $p['category_name'] : $p['grade'];
            $productType = 'Curriculum > ' . $catName . ' > ' . $p['subject'];

            $xml->startElement('item');
            $xml->writeElement('g:id', 'LOCKIN-PROD-' . $p['id']);
            $xml->writeElement('g:title', mb_substr($p['title'], 0, 150));
            $xml->writeElement('g:description', $cleanDesc);
            $xml->writeElement('g:link', $productUrl);
            $xml->writeElement('g:image_link', $imageUrl);

            // Additional images (up to 5)
            foreach (array_slice($additionalImages, 0, 5) as $ai) {
                $xml->writeElement('g:additional_image_link', $ai);
            }

            $xml->writeElement('g:availability', 'in_stock');
            $xml->writeElement('g:price', $formattedPrice);
            $xml->writeElement('g:brand', $brand);
            $xml->writeElement('g:condition', 'new');
            $xml->writeElement('g:google_product_category', $defaultCategory);
            $xml->writeElement('g:product_type', $productType);

            // Digital goods without universal barcode
            $xml->writeElement('g:identifier_exists', 'no');

            // Shipping details for instant digital download (Free)
            $xml->startElement('g:shipping');
            $xml->writeElement('g:country', 'IN');
            $xml->writeElement('g:service', 'Instant Digital Download');
            $xml->writeElement('g:price', '0.00 ' . $currency);
            $xml->endElement(); // g:shipping

            // Custom labels for Shopping Campaign segmentation
            $xml->writeElement('g:custom_label_0', $p['grade']);
            $xml->writeElement('g:custom_label_1', $p['subject']);
            $xml->writeElement('g:custom_label_2', $p['is_free'] ? 'Freebie' : 'Paid');
            if (!empty($p['tags'])) {
                $firstTag = trim(explode(',', $p['tags'])[0] ?? '');
                if (!empty($firstTag)) {
                    $xml->writeElement('g:custom_label_3', $firstTag);
                }
            }

            $xml->endElement(); // item
        }

        $xml->endElement(); // channel
        $xml->endElement(); // rss
        $xml->endDocument();

        echo $xml->outputMemory();
    }

    /**
     * Serve Google Site Verification file (e.g. google[hash].html)
     */
    public static function googleVerification($hash)
    {
        $cleanHash = preg_replace('/\.html$/i', '', $hash);
        header('Content-Type: text/html; charset=UTF-8');
        echo "google-site-verification: google{$cleanHash}.html";
    }

    /**
     * Validate product image URL.
     * Verifies that local uploads actually exist on disk with valid file size.
     * Returns the verified absolute URL or null if invalid.
     */
    private static function resolveValidImageUrl(?string $rawUrl, string $baseUrl): ?string
    {
        if (empty($rawUrl)) {
            return null;
        }

        // Relative path e.g. /uploads/products/xyz.png
        if (!str_starts_with($rawUrl, 'http://') && !str_starts_with($rawUrl, 'https://')) {
            $localRel = ltrim($rawUrl, '/');
            $localFile = __DIR__ . '/../../public/' . $localRel;
            if (file_exists($localFile) && filesize($localFile) > 0) {
                return $baseUrl . '/' . $localRel;
            }
            return null;
        }

        // Absolute URL pointing to current domain
        if (str_starts_with($rawUrl, $baseUrl)) {
            $parsedPath = parse_url($rawUrl, PHP_URL_PATH) ?? '';
            $localRel = ltrim($parsedPath, '/');
            $localFile = __DIR__ . '/../../public/' . $localRel;
            if (file_exists($localFile) && filesize($localFile) > 0) {
                return $rawUrl;
            }
            return null;
        }

        // External CDN / Unsplash URL
        return $rawUrl;
    }
}
