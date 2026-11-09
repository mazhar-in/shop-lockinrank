<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

$siteSeoConfig = file_exists(__DIR__ . '/../config/seo.php') ? require __DIR__ . '/../config/seo.php' : [];
$siteBaseUrl = rtrim($siteSeoConfig['base_url'] ?? 'https://shop.lockinrank.com', '/');

// Clean Page Title (strip any existing tags/entities to prevent double encoding like &amp;amp;)
$rawTitle = !empty($pageTitle) ? html_entity_decode(strip_tags($pageTitle), ENT_QUOTES, 'UTF-8') : ($siteSeoConfig['default_title'] ?? 'Lockinrank Shop — Top-Rank JEE, NEET, CBSE & AP Physics Prep Materials');
$seoTitle = htmlspecialchars($rawTitle, ENT_QUOTES, 'UTF-8');

// Clean Page Description
$rawDesc = !empty($pageDescription) ? html_entity_decode(strip_tags($pageDescription), ENT_QUOTES, 'UTF-8') : ($siteSeoConfig['default_description'] ?? 'Official Lockinrank digital store. Download high-yield physics DPPs, formula master sheets, ray diagrams, and test series for JEE Main, JEE Advanced, NEET, CBSE Boards & AP Physics.');
$seoDescription = htmlspecialchars($rawDesc, ENT_QUOTES, 'UTF-8');

// Canonical URL (automatically strips query parameters like ?board=CBSE or ?sort=popular)
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$cleanCanonical = !empty($canonicalUrl) ? $canonicalUrl : ($siteBaseUrl . ($currentPath === '/' ? '' : $currentPath));

// Robots Indexing Directive
$isNoIndex = !empty($noIndex) 
    || str_starts_with($currentPath, '/admin') 
    || str_starts_with($currentPath, '/cart') 
    || str_starts_with($currentPath, '/order') 
    || str_starts_with($currentPath, '/download') 
    || str_starts_with($currentPath, '/seller');

// Social Sharing Assets
$seoImage = !empty($ogImage) ? $ogImage : ($siteBaseUrl . '/images/lockinrank-logo.png');
$seoType = !empty($ogType) ? $ogType : 'website';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <meta name="theme-color" content="#ffffff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">

    <!-- Primary SEO Meta Tags -->
    <title><?= $seoTitle ?></title>
    <meta name="title" content="<?= $seoTitle ?>">
    <meta name="description" content="<?= $seoDescription ?>">
    <link rel="canonical" href="<?= htmlspecialchars($cleanCanonical, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="robots" content="<?= $isNoIndex ? 'noindex, nofollow' : 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' ?>">

    <!-- Open Graph / Facebook / WhatsApp -->
    <meta property="og:type" content="<?= htmlspecialchars($seoType, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:site_name" content="Lockinrank Shop">
    <meta property="og:url" content="<?= htmlspecialchars($cleanCanonical, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:title" content="<?= $seoTitle ?>">
    <meta property="og:description" content="<?= $seoDescription ?>">
    <meta property="og:image" content="<?= htmlspecialchars($seoImage, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:locale" content="en_IN">
    <?php if ($seoType === 'product' && !empty($ogPrice)): ?>
    <meta property="product:price:amount" content="<?= htmlspecialchars($ogPrice, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="product:price:currency" content="INR">
    <?php endif; ?>

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?= htmlspecialchars($cleanCanonical, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:title" content="<?= $seoTitle ?>">
    <meta name="twitter:description" content="<?= $seoDescription ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($seoImage, ENT_QUOTES, 'UTF-8') ?>">

    <!-- Search Console & Google Verification -->
    <?php if (!empty($siteSeoConfig['google_site_verification'])): ?>
    <meta name="google-site-verification" content="<?= htmlspecialchars($siteSeoConfig['google_site_verification'], ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>

    <!-- Resource Preconnect & DNS Prefetch for Fast CDN Delivery -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://unpkg.com" crossorigin>
    <link rel="dns-prefetch" href="https://unpkg.com">

    <!-- Favicon & Touch Icons -->
    <link rel="icon" type="image/svg+xml" href="/images/lockinrank-icon.svg">
    <link rel="apple-touch-icon" href="/images/lockinrank-icon.svg">

    <!-- Pico CSS v2 (Ultra-lightweight ~10KB) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <!-- KaTeX CSS (Academic Physics & Math Formula Engine) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.css" crossorigin="anonymous">
    <!-- Custom High-Contrast Theme (Minified & Optimized) -->
    <link rel="stylesheet" href="/css/app.min.css?v=2.4">
    <!-- HTMX (Instant reactivity without node build tools) -->
    <script src="https://unpkg.com/htmx.org@1.9.12" defer></script>
    <!-- Simple Icon Font -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Global Organization & WebSite Schema (Homepage) -->
    <?php if ($currentPath === '/' || $currentPath === ''): ?>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@graph": [
            {
                "@type": "Organization",
                "@id": "<?= $siteBaseUrl ?>/#organization",
                "name": "Lockinrank",
                "url": "<?= $siteBaseUrl ?>",
                "logo": {
                    "@type": "ImageObject",
                    "url": "<?= $siteBaseUrl ?>/images/lockinrank-logo.png"
                },
                "contactPoint": [
                    {
                        "@type": "ContactPoint",
                        "telephone": "+91 9990253219",
                        "email": "<?= $siteSeoConfig['support_email'] ?? 'support@lockinrank.com' ?>",
                        "contactType": "Customer Support",
                        "availableLanguage": ["English", "Hindi", "Tamil"]
                    }
                ],
                "address": {
                    "@type": "PostalAddress",
                    "postalCode": "641004",
                    "addressLocality": "Coimbatore",
                    "addressRegion": "Tamil Nadu",
                    "addressCountry": "IN"
                }
            },
            {
                "@type": "WebSite",
                "@id": "<?= $siteBaseUrl ?>/#website",
                "url": "<?= $siteBaseUrl ?>",
                "name": "Lockinrank Shop",
                "description": "<?= addslashes($rawDesc) ?>",
                "publisher": {
                    "@id": "<?= $siteBaseUrl ?>/#organization"
                },
                "potentialAction": {
                    "@type": "SearchAction",
                    "target": "<?= $siteBaseUrl ?>/search?q={search_term_string}",
                    "query-input": "required name=search_term_string"
                }
            }
        ]
    }
    </script>
    <?php endif; ?>
</head>
<body>

    <!-- Main Navigation Bar -->
    <nav class="site-nav container-fluid">
        <ul class="nav-brand-section">
            <li>
                <a href="/" class="brand-logo" title="Lockinrank Shop">
                    <img src="/images/lockinrank-logo.svg" alt="Lockinrank Shop" class="logo-image" style="height: 44px; width: auto; display: block;">
                </a>
            </li>
        </ul>

        <!-- Live Global Search Bar with HTMX -->
        <div class="nav-search">
            <input type="search" 
                   name="q" 
                   placeholder="Search JEE Main, Advanced, NEET, AP Physics, CBSE workbooks, formula cheatsheets..." 
                   hx-get="/search" 
                   hx-trigger="keyup changed delay:250ms, search" 
                   hx-target="#product-grid" 
                   hx-include="#filter-form"
                   hx-indicator="#search-loading">
        </div>

        <ul class="nav-actions-section" style="gap: 0.75rem; align-items: center;">
            <li>
                <span id="search-loading" class="htmx-indicator" style="font-size: 0.85rem; color: #4f46e5;">
                    <i class="fa-solid fa-spinner fa-spin"></i>
                </span>
            </li>
            <li class="nav-link-desktop"><a href="/" class="nav-link"><i class="fa-solid fa-compass"></i> Store Catalog</a></li>
            <li class="nav-link-desktop"><a href="/?price=free" class="nav-link" style="color: #10b981 !important;"><i class="fa-solid fa-gift"></i> 100% Freebies</a></li>
            <li class="nav-link-desktop"><a href="/contact" class="nav-link"><i class="fa-regular fa-envelope"></i> Support</a></li>
            <li>
                <a href="/cart" id="nav-cart-btn" class="nav-cart-btn" title="View Shopping Cart (<?= $cartCount ?> items)">
                    <i class="fa-solid fa-cart-shopping"></i> Cart (<?= $cartCount ?>)
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Container Start -->
    <main class="container">
