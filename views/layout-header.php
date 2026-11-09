<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <meta name="theme-color" content="#ffffff">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title><?= htmlspecialchars($pageTitle ?? 'Lockinrank Shop — Competitive Exam Prep, JEE, NEET & AP Physics') ?></title>
    <link rel="icon" type="image/svg+xml" href="/images/lockinrank-icon.svg">
    <!-- Pico CSS v2 (Ultra-lightweight ~10KB) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <!-- Custom High-Contrast Theme -->
    <link rel="stylesheet" href="/css/app.css">
    <!-- HTMX (Instant reactivity without node build tools) -->
    <script src="https://unpkg.com/htmx.org@1.9.12"></script>
    <!-- Simple Icon Font -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php
    $siteSeoConfig = file_exists(__DIR__ . '/../config/seo.php') ? require __DIR__ . '/../config/seo.php' : [];
    if (!empty($siteSeoConfig['google_site_verification'])): ?>
    <!-- Google Merchant Center / Search Console Verification -->
    <meta name="google-site-verification" content="<?= htmlspecialchars($siteSeoConfig['google_site_verification']) ?>">
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
