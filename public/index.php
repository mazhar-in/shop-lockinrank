<?php

// Serve static assets directly if running via PHP built-in server
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ($path !== '/' && file_exists(__DIR__ . $path) && !is_dir(__DIR__ . $path)) {
        return false;
    }
}

require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\HomeController;
use App\Controllers\ProductController;
use App\Controllers\StoreController;
use App\Controllers\CartController;
use App\Controllers\AdminController;
use App\Controllers\PageController;

// Configure Flight views directory
Flight::set('flight.views.path', __DIR__ . '/../views');

// --- Marketplace Catalog & Search ---
Flight::route('GET /', [HomeController::class, 'index']);
Flight::route('GET /search', [HomeController::class, 'search']);

// --- Product Details & Watermarked Preview ---
Flight::route('GET /product/@slug:[a-zA-Z0-9_-]+', [ProductController::class, 'show']);
Flight::route('POST /product/review', [ProductController::class, 'submitReview']);
Flight::route('GET /product/preview/@id:[0-9]+', [ProductController::class, 'quickPreview']);
Flight::route('GET /product/sample/@id:[0-9]+', [ProductController::class, 'downloadSample']);

// --- Store Profile ---
Flight::route('GET /store/@slug:[a-zA-Z0-9_-]+', [StoreController::class, 'show']);

// --- Cart & Razorpay Digital Fulfillment ---
Flight::route('POST /cart/add', [CartController::class, 'add']);
Flight::route('GET /cart/remove/@id:[0-9]+', [CartController::class, 'remove']);
Flight::route('GET /cart', [CartController::class, 'view']);
Flight::route('POST /cart/initiate-payment', [CartController::class, 'initiatePayment']);
Flight::route('POST /cart/verify-payment', [CartController::class, 'verifyPayment']);
Flight::route('GET /order/success/@orderNumber:[a-zA-Z0-9_-]+', [CartController::class, 'orderSuccess']);
Flight::route('GET /download/@token:[a-zA-Z0-9_-]+', [CartController::class, 'download']);

// --- Password-Protected Admin Portal (/admin) ---
Flight::route('GET /admin', [AdminController::class, 'dashboard']);
Flight::route('GET /admin/dashboard', [AdminController::class, 'dashboard']);
Flight::route('GET /admin/products', [AdminController::class, 'products']);
Flight::route('GET /admin/categories', [AdminController::class, 'categories']);
Flight::route('POST /admin/category/create', [AdminController::class, 'createCategory']);
Flight::route('POST /admin/category/edit/@id:[0-9]+', [AdminController::class, 'updateCategory']);
Flight::route('GET|POST /admin/category/delete/@id:[0-9]+', [AdminController::class, 'deleteCategory']);
Flight::route('GET /admin/orders', [AdminController::class, 'orders']);
Flight::route('GET /admin/customers', [AdminController::class, 'customers']);
Flight::route('GET /admin/customer', [AdminController::class, 'customerDetail']);
Flight::route('POST /admin/token/refresh', [AdminController::class, 'refreshToken']);
Flight::route('GET /admin/login', [AdminController::class, 'loginForm']);
Flight::route('POST /admin/login', [AdminController::class, 'loginSubmit']);
Flight::route('GET /admin/logout', [AdminController::class, 'logout']);
Flight::route('GET /admin/upload', [AdminController::class, 'uploadForm']);
Flight::route('POST /admin/upload', [AdminController::class, 'save']);
Flight::route('GET /admin/product/edit/@id:[0-9]+', [AdminController::class, 'editForm']);
Flight::route('POST /admin/product/edit/@id:[0-9]+', [AdminController::class, 'updateProduct']);
Flight::route('GET|POST /admin/product/delete/@id:[0-9]+', [AdminController::class, 'deleteProduct']);
Flight::route('POST /admin/products/bulk-delete', [AdminController::class, 'bulkDeleteProducts']);

// --- Legacy Seller Routes Protection (Redirect to Admin Login) ---
Flight::route('GET /seller', function() { Flight::redirect('/admin/login'); });
Flight::route('GET /seller/@any:[a-zA-Z0-9_-]+', function() { Flight::redirect('/admin/login'); });

// --- Legal & Information Pages (Razorpay & Compliance) ---
Flight::route('GET /terms', [PageController::class, 'terms']);
Flight::route('GET /privacy', [PageController::class, 'privacy']);
Flight::route('GET /shipping', [PageController::class, 'shipping']);
Flight::route('GET /cancellation-refunds', [PageController::class, 'refunds']);
Flight::route('GET /contact', [PageController::class, 'contact']);
Flight::route('POST /contact', [PageController::class, 'submitContact']);

// 404 Handler
Flight::map('notFound', function () {
    Flight::response()->status(404);
    echo '<!DOCTYPE html><html><head><title>404 Not Found — Lockinrank Shop</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css"><link rel="stylesheet" href="/css/app.css"></head><body class="container" style="text-align:center; padding-top: 5rem;"><h1>404</h1><p>Resource or Page not found on Lockinrank Shop.</p><a href="/" role="button">Back to Store</a></body></html>';
});

Flight::start();
