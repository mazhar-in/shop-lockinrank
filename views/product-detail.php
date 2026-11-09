<?php 
// Prepare SEO and Structured Data variables BEFORE layout-header inclusion
$seoConfig = file_exists(__DIR__ . '/../config/seo.php') ? require __DIR__ . '/../config/seo.php' : [];
$baseUrl = rtrim($seoConfig['base_url'] ?? 'https://shop.lockinrank.com', '/');
$canonicalUrl = $baseUrl . '/product/' . ($product['slug'] ?? $product['id']);

$cleanTitle = trim(strip_tags($product['title']));
$pageTitle = $cleanTitle . " — Lockinrank Shop";

$rawDesc = trim(strip_tags($product['description'] ?? ''));
$pageDescription = !empty($rawDesc) 
    ? (mb_strlen($rawDesc) > 155 ? mb_substr($rawDesc, 0, 152) . '...' : $rawDesc)
    : ($cleanTitle . ' - Download official study materials, DPPs, and practice sets by Lockinrank.');

$schemaImages = [];
$defaultFallbackImage = $baseUrl . '/images/default-study-material.png';

$checkImageExists = function($imgUrl) use ($baseUrl) {
    if (empty($imgUrl)) return false;
    if (str_starts_with($imgUrl, $baseUrl)) {
        $path = parse_url($imgUrl, PHP_URL_PATH);
        $local = __DIR__ . '/../public/' . ltrim($path, '/');
        return file_exists($local) && filesize($local) > 0;
    }
    if (!str_starts_with($imgUrl, 'http://') && !str_starts_with($imgUrl, 'https://')) {
        $local = __DIR__ . '/../public/' . ltrim($imgUrl, '/');
        return file_exists($local) && filesize($local) > 0;
    }
    return true; // External CDN/Unsplash URL
};

if (!empty($product['thumbnail_url'])) {
    $thumb = $product['thumbnail_url'];
    $fullThumb = str_starts_with($thumb, 'http') ? $thumb : ($baseUrl . '/' . ltrim($thumb, '/'));
    if ($checkImageExists($fullThumb)) {
        $schemaImages[] = $fullThumb;
    }
}
if (!empty($previewImages)) {
    foreach ($previewImages as $prevImg) {
        $fullImg = str_starts_with($prevImg, 'http') ? $prevImg : ($baseUrl . '/' . ltrim($prevImg, '/'));
        if (!in_array($fullImg, $schemaImages) && $checkImageExists($fullImg)) {
            $schemaImages[] = $fullImg;
        }
    }
}
if (empty($schemaImages)) {
    $schemaImages[] = $defaultFallbackImage;
}

$ogType = 'product';
$ogImage = $schemaImages[0];
$ogPrice = number_format((float)($product['is_free'] ? 0 : $product['price']), 2, '.', '');

$hideMobileBottomNav = true; // Replace bottom nav with Sticky Purchase Bar on product page
include __DIR__ . '/layout-header.php'; 

// Structured Data: Schema.org Product & BreadcrumbList
$schemaData = [
    '@context' => 'https://schema.org/',
    '@type' => 'Product',
    'name' => $cleanTitle,
    'image' => $schemaImages,
    'description' => $rawDesc ?: $pageDescription,
    'sku' => 'LOCKIN-PROD-' . $product['id'],
    'mpn' => 'LOCKIN-' . $product['id'],
    'brand' => [
        '@type' => 'Brand',
        'name' => $seoConfig['brand_name'] ?? 'Lockinrank'
    ],
    'offers' => [
        '@type' => 'Offer',
        'url' => $canonicalUrl,
        'priceCurrency' => $seoConfig['currency'] ?? 'INR',
        'price' => $ogPrice,
        'priceValidUntil' => date('Y-12-31', strtotime('+1 year')),
        'availability' => 'https://schema.org/InStock',
        'itemCondition' => 'https://schema.org/NewCondition'
    ]
];

if (!empty($product['review_count']) && (int)$product['review_count'] > 0) {
    $schemaData['aggregateRating'] = [
        '@type' => 'AggregateRating',
        'ratingValue' => number_format((float)$product['rating'], 1, '.', ''),
        'reviewCount' => (int)$product['review_count']
    ];
}

$breadcrumbSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Home',
            'item' => $baseUrl
        ],
        [
            '@type' => 'ListItem',
            'position' => 2,
            'name' => $product['grade'] ?? 'Curriculum',
            'item' => $baseUrl . '/?class=' . urlencode($product['grade'] ?? '')
        ],
        [
            '@type' => 'ListItem',
            'position' => 3,
            'name' => $cleanTitle,
            'item' => $canonicalUrl
        ]
    ]
];
?>
<script type="application/ld+json">
<?= json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?>
</script>
<script type="application/ld+json">
<?= json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?>
</script>


<!-- Breadcrumbs -->
<nav aria-label="breadcrumb" style="margin-bottom: 1.5rem; font-size: 0.85rem;">
    <ul>
        <li><a href="/">Home</a></li>
        <li><a href="/?board=<?= urlencode($product['board'] ?? 'CBSE') ?>"><?= htmlspecialchars($product['board'] ?? 'CBSE') ?></a></li>
        <li><a href="/?class=<?= urlencode($product['grade']) ?>"><?= htmlspecialchars($product['grade']) ?></a></li>
        <li><?= htmlspecialchars($product['title']) ?></li>
    </ul>
</nav>

<div class="product-detail-layout" style="margin-bottom: 3rem;">

    <!-- Left: Multi-Image Preview Gallery -->
    <div>
        <div class="product-gallery-card">
            <!-- Main Display Stage -->
            <div class="product-gallery-stage" id="galleryStage" onclick="triggerMainPreview()">
                <img id="mainGalleryImage" 
                     src="<?= htmlspecialchars($previewImages[0] ?? (!empty($product['thumbnail_url']) ? $product['thumbnail_url'] : '/images/default-study-material.png')) ?>" 
                     alt="<?= htmlspecialchars($product['title']) ?>"
                     onerror="this.onerror=null; this.src='/images/default-study-material.png';">
                
                <div class="watermark-overlay" style="font-size: 1.8rem; padding: 0.35rem 1.25rem;">PREVIEW ONLY</div>
                
                <!-- Image Counter Badge -->
                <div class="gallery-counter-badge" id="galleryCounter">
                    <i class="fa-solid fa-images"></i> 
                    <span id="galleryCurrentIndex">1</span> / <?= count($previewImages) ?>
                </div>

                <!-- Zoom / Fullscreen Hint -->
                <div class="gallery-zoom-hint">
                    <i class="fa-solid fa-magnifying-glass-plus"></i> Click to enlarge
                </div>

                <?php if (count($previewImages) > 1): ?>
                    <!-- Navigation Chevrons -->
                    <button type="button" 
                            class="gallery-nav-btn prev-btn" 
                            onclick="event.stopPropagation(); changeGalleryImage(-1);" 
                            aria-label="Previous image">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <button type="button" 
                            class="gallery-nav-btn next-btn" 
                            onclick="event.stopPropagation(); changeGalleryImage(1);" 
                            aria-label="Next image">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                <?php endif; ?>
            </div>

            <!-- All Images Thumbnails Strip -->
            <?php if (count($previewImages) > 1): ?>
                <div style="margin-top: 0.85rem;">
                    <div style="font-size: 0.78rem; font-weight: 700; color: #64748b; margin-bottom: 0.4rem; display: flex; justify-content: space-between; align-items: center;">
                        <span><i class="fa-solid fa-layer-group" style="color: #4f46e5;"></i> All Product Images (<?= count($previewImages) ?>)</span>
                        <span style="font-size: 0.72rem; color: #94a3b8;">Click thumbnail to switch</span>
                    </div>
                    <div class="product-thumbnails-strip" id="productThumbnailsStrip">
                        <?php foreach ($previewImages as $idx => $imgUrl): ?>
                            <button type="button" 
                                    class="gallery-thumb-btn <?= $idx === 0 ? 'active' : '' ?>" 
                                    data-index="<?= $idx ?>" 
                                    onclick="selectGalleryImage(<?= $idx ?>)"
                                    title="View Image <?= $idx + 1 ?>">
                                <img src="<?= htmlspecialchars($imgUrl) ?>" 
                                     alt="Sample preview <?= $idx + 1 ?>"
                                     referrerpolicy="no-referrer"
                                     onerror="this.onerror=null; this.src='/images/default-study-material.png';">
                                <span class="gallery-thumb-index"><?= $idx + 1 ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div style="display: flex; gap: 0.75rem; justify-content: center; margin-top: 1.25rem; flex-wrap: wrap;">
            <button type="button" 
                    id="btnOpenPreviewModal"
                    class="btn-card-preview"
                    hx-get="/product/preview/<?= $product['id'] ?>" 
                    hx-target="#preview-modal-content"
                    onclick="openPreviewModal()"
                    style="font-size: 0.88rem; padding: 0.5rem 1rem;">
                <i class="fa-solid fa-magnifying-glass-plus"></i> View Fullscreen Preview (All Pages)
            </button>
            <?php if (!$product['is_free']): ?>
                <a href="/product/sample/<?= $product['id'] ?>" 
                   role="button" 
                   class="secondary outline"
                   download
                   style="font-size: 0.88rem; padding: 0.5rem 1rem; margin-bottom: 0; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem; border-color: #cbd5e1; color: #334155;">
                    <i class="fa-solid fa-file-arrow-down" style="color: #4f46e5;"></i> Download Sample
                </a>
            <?php endif; ?>
        </div>

        <script>
            (function() {
                const images = <?= json_encode($previewImages) ?>;
                let activeIndex = 0;
                const total = images.length;

                window.selectGalleryImage = function(index) {
                    if (index < 0 || index >= total) return;
                    activeIndex = index;
                    const mainImg = document.getElementById('mainGalleryImage');
                    const counter = document.getElementById('galleryCurrentIndex');
                    
                    if (mainImg) {
                        mainImg.style.opacity = '0.3';
                        mainImg.style.transform = 'scale(0.98)';
                        setTimeout(() => {
                            mainImg.src = images[activeIndex];
                            mainImg.style.opacity = '1';
                            mainImg.style.transform = 'scale(1)';
                        }, 120);
                    }
                    if (counter) {
                        counter.textContent = activeIndex + 1;
                    }

                    // Update active thumbnail border
                    const thumbBtns = document.querySelectorAll('.gallery-thumb-btn');
                    thumbBtns.forEach((btn, i) => {
                        if (i === activeIndex) {
                            btn.classList.add('active');
                            btn.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                        } else {
                            btn.classList.remove('active');
                        }
                    });
                };

                window.changeGalleryImage = function(step) {
                    let next = activeIndex + step;
                    if (next < 0) next = total - 1;
                    if (next >= total) next = 0;
                    window.selectGalleryImage(next);
                };

                window.triggerMainPreview = function() {
                    const btn = document.getElementById('btnOpenPreviewModal');
                    if (btn) btn.click();
                };

                // Keyboard support (left/right arrows)
                document.addEventListener('keydown', function(e) {
                    if (document.getElementById('preview-modal') && document.getElementById('preview-modal').open) return;
                    if (e.key === 'ArrowLeft') {
                        changeGalleryImage(-1);
                    } else if (e.key === 'ArrowRight') {
                        changeGalleryImage(1);
                    }
                });
            })();
        </script>
    </div>

    <!-- Right: Buying Options & Resource Details -->
    <div>
        <div style="display: flex; gap: 0.5rem; margin-bottom: 0.75rem; flex-wrap: wrap;">
            <span class="chip" style="background: #4f46e5; color: #ffffff;"><?= htmlspecialchars($product['board'] ?? 'CBSE') ?></span>
            <span class="chip"><?= htmlspecialchars($product['grade']) ?></span>
            <span class="chip" style="background: #475569;"><?= htmlspecialchars($product['subject']) ?></span>
            <?php if (!empty($product['is_ink_friendly'])): ?>
                <span class="chip" style="background: #059669;"><i class="fa-solid fa-print"></i> Ink-Friendly B&W</span>
            <?php endif; ?>
        </div>

        <h1 style="font-size: 1.75rem; font-weight: 800; line-height: 1.25; margin-bottom: 0.75rem;">
            <?= htmlspecialchars($product['title']) ?>
        </h1>

        <!-- Author / Publisher Card -->
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem;">
            <img src="<?= htmlspecialchars($product['store_avatar']) ?>" alt="Author" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover;">
            <div>
                <a href="/store/<?= htmlspecialchars($product['store_slug']) ?>" style="font-weight: 700; text-decoration: none;">
                    <?= htmlspecialchars($product['store_name']) ?>
                </a>
                <div style="font-size: 0.8rem; color: var(--pico-muted-color);">
                    <?= htmlspecialchars($product['author_name']) ?> &bull; ⭐ <?= number_format($product['store_rating'], 1) ?> rating
                </div>
            </div>
        </div>

        <!-- Rating & Sales -->
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.75rem; font-size: 0.9rem;">
            <span style="color: var(--tpt-orange); font-weight: 700;">
                <i class="fa-solid fa-star"></i> <?= number_format($product['rating'], 1) ?>
            </span>
            <a href="#reviews" style="color: var(--pico-muted-color); text-decoration: underline;">
                (<?= $product['review_count'] ?> reviews)
            </a>
            <span style="color: var(--pico-muted-color);">&bull;</span>
            <span style="color: var(--pico-muted-color);">
                <i class="fa-solid fa-download"></i> <?= $product['sales_count'] ?> downloads
            </span>
            <?php if (!$product['is_free'] && !empty($product['sample_downloads'])): ?>
                <span style="color: var(--pico-muted-color);">&bull;</span>
                <span style="color: #6366f1; font-weight: 600;">
                    <i class="fa-solid fa-file-arrow-down"></i> <?= $product['sample_downloads'] ?> sample downloads
                </span>
            <?php endif; ?>
        </div>

        <!-- Clickable Product Tags -->
        <?php if (!empty($product['tags'])): 
            $tagList = array_filter(array_map('trim', explode(',', $product['tags'])));
        ?>
            <?php if (!empty($tagList)): ?>
                <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap; margin-bottom: 1.25rem;">
                    <span style="font-size: 0.78rem; font-weight: 800; color: #64748b; text-transform: uppercase; display: inline-flex; align-items: center; gap: 0.25rem;">
                        <i class="fa-solid fa-tags" style="color: #4f46e5;"></i> Tags:
                    </span>
                    <?php foreach ($tagList as $t): ?>
                        <a href="/?tag=<?= urlencode($t) ?>" style="background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe; padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.78rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.25rem; transition: all 0.15s ease;">
                            #<?= htmlspecialchars($t) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Price & Buy Box -->
        <div style="background-color: var(--pico-card-background-color); border: 1px solid #cbd5e1; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
            <div style="margin-bottom: 1.25rem;">
                <span style="font-size: 0.78rem; color: #64748b; text-transform: uppercase; font-weight: 800; letter-spacing: 0.05em; display: block; margin-bottom: 0.35rem;">
                    Single-Student / Personal Study License
                </span>
                
                <div style="display: flex; align-items: baseline; gap: 0.75rem; flex-wrap: wrap;">
                    <span style="font-size: 2.2rem; font-weight: 900; color: <?= $product['is_free'] ? '#16a34a' : '#0f172a' ?>; line-height: 1;">
                        <?= $product['is_free'] ? 'FREE' : '₹' . number_format($product['price'], 0) ?>
                    </span>

                    <?php if (!empty($product['mrp']) && (float)$product['mrp'] > (float)$product['price']): 
                        $prodDiscountPct = round((((float)$product['mrp'] - (float)$product['price']) / (float)$product['mrp']) * 100);
                        $prodSavings = (float)$product['mrp'] - (float)$product['price'];
                    ?>
                        <span style="font-size: 1.05rem; color: #64748b; font-weight: 600;">
                            M.R.P.: <span style="text-decoration: line-through;">₹<?= number_format($product['mrp'], 0) ?></span>
                        </span>
                        <span style="background-color: #dcfce7; color: #15803d; font-size: 0.88rem; font-weight: 800; padding: 0.2rem 0.6rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 0.25rem;">
                            <i class="fa-solid fa-arrow-down" style="font-size: 0.75rem;"></i> <?= $prodDiscountPct ?>% OFF
                        </span>
                    <?php endif; ?>
                </div>

                <?php if (!empty($product['mrp']) && (float)$product['mrp'] > (float)$product['price']): ?>
                    <div style="font-size: 0.85rem; color: #16a34a; font-weight: 700; margin-top: 0.4rem;">
                        <i class="fa-solid fa-circle-check"></i> You save ₹<?= number_format($prodSavings, 0) ?> (<?= $prodDiscountPct ?>% discount)
                    </div>
                <?php endif; ?>
            </div>

            <?php $isInCart = isset($_SESSION['cart'][$product['id']]); ?>
            <?php if ($isInCart): ?>
                <a href="/cart" role="button" style="width: 100%; font-size: 1.05rem; padding: 0.75rem; font-weight: 700; margin-bottom: 0.5rem; background-color: #059669; border-color: #059669; display: flex; align-items: center; justify-content: center; gap: 0.5rem; text-decoration: none; color: #ffffff;">
                    <i class="fa-solid fa-circle-check"></i> In Cart — Proceed to Checkout &rarr;
                </a>
            <?php else: ?>
                <form action="/cart/add" method="POST" style="margin-bottom: 0;">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <button type="submit" style="width: 100%; font-size: 1.1rem; padding: 0.75rem; font-weight: 700; margin-bottom: 0.5rem; background-color: var(--tpt-green); border-color: var(--tpt-green); cursor: pointer;">
                        <i class="fa-solid fa-cart-shopping"></i> <?= $product['is_free'] ? 'Download Freebie' : 'Add to Cart (₹' . number_format($product['price'], 0) . ')' ?>
                    </button>
                </form>
            <?php endif; ?>

            <?php if (!$product['is_free']): ?>
                <div style="margin-top: 0.65rem;">
                    <a href="/product/sample/<?= $product['id'] ?>" 
                       role="button" 
                       class="btn-sample-download"
                       download
                       title="Download free sample questions and solution keys">
                        <i class="fa-solid fa-file-arrow-down"></i> Download Free Sample Pages (PDF)
                    </a>
                </div>
            <?php endif; ?>

            <div style="display: flex; justify-content: space-around; font-size: 0.78rem; color: var(--pico-muted-color); margin-top: 0.75rem;">
                <span><i class="fa-solid fa-bolt" style="color: #f59e0b;"></i> Instant UPI / QR Download</span>
                <span><i class="fa-brands fa-whatsapp" style="color: #25d366;"></i> WhatsApp Delivery Link</span>
            </div>
        </div>

        <!-- Specifications Table -->
        <div style="font-size: 0.85rem;">
            <table style="margin-bottom: 0;">
                <tbody>
                    <tr>
                        <td style="color: var(--pico-muted-color); width: 140px;"><strong>Board Alignment:</strong></td>
                        <td><strong><?= htmlspecialchars($product['board'] ?? 'CBSE') ?></strong></td>
                    </tr>
                    <tr>
                        <td style="color: var(--pico-muted-color);"><strong>Class:</strong></td>
                        <td><?= htmlspecialchars($product['grade']) ?></td>
                    </tr>
                    <tr>
                        <td style="color: var(--pico-muted-color);"><strong>Resource Type:</strong></td>
                        <td><?= htmlspecialchars($product['resource_type']) ?></td>
                    </tr>
                    <tr>
                        <td style="color: var(--pico-muted-color);"><strong>File Format:</strong></td>
                        <td><i class="fa-solid fa-file-pdf" style="color: var(--tpt-red);"></i> <?= htmlspecialchars($product['file_format']) ?> (Printable)</td>
                    </tr>
                    <tr>
                        <td style="color: var(--pico-muted-color);"><strong>Total Pages:</strong></td>
                        <td><?= $product['page_count'] ?> Pages (Includes worked solutions & keys)</td>
                    </tr>
                    <?php if (!empty($product['standards'])): ?>
                    <tr>
                        <td style="color: var(--pico-muted-color);"><strong>Curriculum Link:</strong></td>
                        <td><mark><?= htmlspecialchars($product['standards']) ?></mark></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($product['tags'])): ?>
                    <tr>
                        <td style="color: var(--pico-muted-color);"><strong>Topic Tags:</strong></td>
                        <td>
                            <div style="display: flex; gap: 0.35rem; flex-wrap: wrap;">
                                <?php foreach (array_filter(array_map('trim', explode(',', $product['tags']))) as $t): ?>
                                    <a href="/?tag=<?= urlencode($t) ?>" style="font-size: 0.75rem; background: #eef2ff; color: #4338ca; padding: 0.15rem 0.5rem; border-radius: 4px; font-weight: 700; text-decoration: none; border: 1px solid #c7d2fe;">
                                        #<?= htmlspecialchars($t) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</div>

<!-- Description -->
<div style="margin-bottom: 3rem;">
    <h3 style="font-size: 1.3rem; border-bottom: 2px solid var(--pico-primary); display: inline-block; padding-bottom: 0.25rem; margin-bottom: 1.25rem;">
        About this Teaching Material
    </h3>
    <div style="background-color: var(--pico-card-background-color); border: 1px solid var(--pico-muted-border-color); border-radius: 8px; padding: 1.5rem; line-height: 1.7;">
        <div class="markdown-content">
            <?= \App\Services\MarkdownParser::render($product['description'] ?? '') ?>
        </div>
        
        <?php 
        $descLower = strtolower($product['description'] ?? '');
        $hasCustomHighlights = str_contains($descLower, 'highlight') || str_contains($descLower, 'included');
        if (!$hasCustomHighlights): 
        ?>
        <h5 style="margin-top: 1.5rem; margin-bottom: 0.5rem;"><i class="fa-solid fa-check" style="color: var(--tpt-green);"></i> What is Included in this Pack:</h5>
        <ul>
            <li>Step-by-step student practice sheets with clean NCERT-pattern exercises</li>
            <li>Teacher answer keys with full calculation steps and hints</li>
            <li>High-contrast ink-friendly layout designed for monochrome printer toner saving</li>
            <li>Single-classroom or home-school license rights for unlimited printouts</li>
        </ul>
        <?php endif; ?>
    </div>
</div>

<!-- Ratings & Reviews Section -->
<div id="reviews" style="margin-bottom: 3.5rem; scroll-margin-top: 80px;">

    <!-- Alerts / Flash Messages -->
    <?php if (!empty($_SESSION['review_flash_success'])): ?>
        <div style="background-color: #dcfce7; border: 1px solid #86efac; color: #166534; padding: 0.9rem 1.25rem; border-radius: 8px; font-size: 0.92rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div><i class="fa-solid fa-circle-check" style="color: #16a34a; margin-right: 0.4rem;"></i> <?= htmlspecialchars($_SESSION['review_flash_success']) ?></div>
            <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; color: #166534; font-size: 1.2rem; cursor: pointer; padding: 0; line-height: 1;">&times;</button>
        </div>
        <?php unset($_SESSION['review_flash_success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['review_flash_error'])): ?>
        <div style="background-color: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; padding: 0.9rem 1.25rem; border-radius: 8px; font-size: 0.92rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div><i class="fa-solid fa-triangle-exclamation" style="color: #dc2626; margin-right: 0.4rem;"></i> <?= htmlspecialchars($_SESSION['review_flash_error']) ?></div>
            <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; color: #991b1b; font-size: 1.2rem; cursor: pointer; padding: 0; line-height: 1;">&times;</button>
        </div>
        <?php unset($_SESSION['review_flash_error']); ?>
    <?php endif; ?>

    <!-- Section Heading -->
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h3 style="font-size: 1.45rem; font-weight: 800; color: #0f172a; margin: 0 0 0.25rem 0;">
                Ratings & Customer Reviews
            </h3>
            <p style="color: #64748b; font-size: 0.92rem; margin: 0;">
                Feedback from students and educators who studied with this material
            </p>
        </div>
        <div>
            <button type="button" onclick="toggleReviewForm()" style="background: #4f46e5; border-color: #4f46e5; color: #ffffff !important; font-weight: 700; font-size: 0.9rem; padding: 0.6rem 1.25rem; border-radius: 8px; display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer; box-shadow: 0 2px 5px rgba(79, 70, 229, 0.2); margin-bottom: 0;">
                <i class="fa-solid fa-pen-to-square"></i> Write a Review
            </button>
        </div>
    </div>

    <!-- Rating Summary Dashboard Card -->
    <?php 
    $totalRev = count($reviews);
    $avgRating = (float)($product['rating'] ?? 5.0);
    ?>
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.5rem; align-items: center;">
            
            <!-- Overall Score Block -->
            <div style="text-align: center; padding-right: 1rem; border-right: 1px solid #f1f5f9;">
                <div style="font-size: 3.2rem; font-weight: 900; color: #0f172a; line-height: 1; margin-bottom: 0.35rem;">
                    <?= number_format($avgRating, 1) ?>
                    <span style="font-size: 1.1rem; color: #94a3b8; font-weight: 600;">/ 5</span>
                </div>
                <div style="color: #f59e0b; font-size: 1.25rem; margin-bottom: 0.5rem; display: flex; justify-content: center; gap: 0.2rem;">
                    <?php 
                    $fullStars = floor($avgRating);
                    for ($s = 1; $s <= 5; $s++) {
                        if ($s <= $fullStars) {
                            echo '<i class="fa-solid fa-star"></i>';
                        } elseif ($s - $avgRating < 1 && $avgRating - $fullStars >= 0.3) {
                            echo '<i class="fa-solid fa-star-half-stroke"></i>';
                        } else {
                            echo '<i class="fa-regular fa-star" style="color: #cbd5e1;"></i>';
                        }
                    }
                    ?>
                </div>
                <div style="color: #64748b; font-size: 0.88rem; font-weight: 600;">
                    Based on <?= $totalRev ?> verified review<?= $totalRev === 1 ? '' : 's' ?>
                </div>
            </div>

            <!-- Star Distribution Progress Bars -->
            <div style="display: flex; flex-direction: column; gap: 0.45rem;">
                <?php for ($star = 5; $star >= 1; $star--): 
                    $starCount = $starBreakdown[$star] ?? 0;
                    $percent = $totalRev > 0 ? round(($starCount / $totalRev) * 100) : 0;
                ?>
                    <div style="display: flex; align-items: center; gap: 0.75rem; font-size: 0.82rem;">
                        <span style="width: 45px; font-weight: 700; color: #475569; display: flex; align-items: center; gap: 0.25rem;">
                            <?= $star ?> <i class="fa-solid fa-star" style="color: #f59e0b; font-size: 0.75rem;"></i>
                        </span>
                        <div style="flex: 1; height: 8px; background: #e2e8f0; border-radius: 999px; overflow: hidden;">
                            <div style="height: 100%; width: <?= $percent ?>%; background: #f59e0b; border-radius: 999px; transition: width 0.3s ease;"></div>
                        </div>
                        <span style="width: 38px; text-align: right; color: #64748b; font-weight: 600;">
                            <?= $starCount ?>
                        </span>
                    </div>
                <?php endfor; ?>
            </div>

            <!-- Review Prompt Callout -->
            <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 10px; padding: 1.25rem; text-align: center;">
                <i class="fa-solid fa-shield-heart" style="font-size: 1.6rem; color: #4f46e5; margin-bottom: 0.5rem; display: block;"></i>
                <div style="font-weight: 700; color: #0f172a; font-size: 0.95rem; margin-bottom: 0.25rem;">Have you used this resource?</div>
                <p style="color: #64748b; font-size: 0.82rem; margin: 0 0 0.85rem 0;">Help other JEE & NEET students by sharing your honest feedback.</p>
                <button type="button" onclick="openReviewForm()" style="background: transparent; border: 1.5px solid #4f46e5; color: #4f46e5; font-weight: 700; font-size: 0.82rem; padding: 0.45rem 1rem; border-radius: 6px; cursor: pointer; margin-bottom: 0;">
                    Share Feedback
                </button>
            </div>

        </div>
    </div>

    <!-- Review Submission Form Card -->
    <div id="review-form-container" style="display: <?= !empty($_SESSION['review_flash_error']) ? 'block' : 'none' ?>; background: #ffffff; border: 1.5px solid #6366f1; border-radius: 12px; padding: 1.75rem; margin-bottom: 2rem; box-shadow: 0 8px 24px rgba(99, 102, 241, 0.08);">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem;">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
                <div style="width: 32px; height: 32px; background: #e0e7ff; color: #4338ca; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-pen"></i>
                </div>
                <div>
                    <h4 style="margin: 0; font-size: 1.15rem; font-weight: 800; color: #0f172a;">Write a Verified Review</h4>
                    <span style="font-size: 0.78rem; color: #64748b;">Protected by anti-spam verification</span>
                </div>
            </div>
            <button type="button" onclick="toggleReviewForm()" style="background: none; border: none; color: #64748b; font-size: 1.25rem; cursor: pointer; padding: 0.2rem 0.5rem;" title="Close form">&times;</button>
        </div>

        <form method="POST" action="/product/review" id="review-submission-form" style="margin: 0;">
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
            <input type="hidden" name="product_slug" value="<?= htmlspecialchars($product['slug']) ?>">
            <input type="hidden" name="review_token" value="<?= htmlspecialchars($_SESSION['review_token'] ?? '') ?>">
            <input type="hidden" name="rating" id="review-rating-input" value="5">

            <!-- Anti-Spam Honeypot Field -->
            <div style="position: absolute; left: -9999px; top: -9999px; opacity: 0; pointer-events: none;" aria-hidden="true">
                <label for="hp_website_field">Leave this empty</label>
                <input type="text" id="hp_website_field" name="hp_website" tabindex="-1" autocomplete="off" value="">
            </div>

            <!-- Two Columns: Author Name & Role -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">
                        Your Name <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" id="review-author-name" name="author_name" required placeholder="e.g. Aryan Verma" maxlength="60" style="font-size: 0.9rem; margin-bottom: 0;">
                </div>
                <div>
                    <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">
                        Your Profile / Role
                    </label>
                    <select name="author_role" style="font-size: 0.9rem; margin-bottom: 0;">
                        <option value="Verified Student" selected>🎓 Student / Aspirant</option>
                        <option value="Verified Educator">👨‍🏫 Educator / Teacher</option>
                        <option value="Verified Parent">👨‍👩‍👧 Parent</option>
                    </select>
                </div>
            </div>

            <!-- Rating Stars Selection -->
            <div style="margin-bottom: 1.25rem;">
                <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">
                    Overall Rating <span style="color: #ef4444;">*</span>
                </label>
                <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                    <div style="display: flex; gap: 0.35rem; font-size: 1.5rem; cursor: pointer;" onmouseleave="resetRatingHover()">
                        <?php for ($s = 1; $s <= 5; $s++): ?>
                            <i class="fa-solid fa-star star-rate-btn" 
                               id="star-btn-<?= $s ?>" 
                               onclick="setRating(<?= $s ?>)" 
                               onmouseenter="hoverRating(<?= $s ?>)" 
                               style="color: #f59e0b; transition: transform 0.1s ease;"
                               title="<?= $s ?> Star"></i>
                        <?php endfor; ?>
                    </div>
                    <span id="rating-label" style="font-weight: 700; font-size: 0.88rem; color: #0f172a; background: #fef3c7; color: #92400e; padding: 0.2rem 0.65rem; border-radius: 6px;">
                        5 Stars - Exceptional
                    </span>
                </div>
            </div>

            <!-- Feedback Comment -->
            <div style="margin-bottom: 1.25rem;">
                <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">
                    Your Review / Comments <span style="color: #ef4444;">*</span>
                </label>
                <textarea name="comment" rows="4" required minlength="10" maxlength="1000" placeholder="Share your specific thoughts on problem clarity, answer key step explanations, exam syllabus relevance, or overall usefulness..." style="font-size: 0.9rem; margin-bottom: 0.25rem;"></textarea>
                <div style="font-size: 0.76rem; color: #64748b; display: flex; justify-content: space-between;">
                    <span>Minimum 10 characters. Keep it constructive and helpful.</span>
                    <span style="color: #94a3b8;"><i class="fa-solid fa-shield-halved"></i> Links & promotional spam are blocked</span>
                </div>
            </div>

            <!-- Anti-Spam Human Verification Challenge -->
            <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.6rem;">
                    <div style="width: 32px; height: 32px; background: #0f172a; color: #ffffff; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 0.85rem;">
                        <i class="fa-solid fa-robot"></i>
                    </div>
                    <div>
                        <div style="font-weight: 700; color: #0f172a; font-size: 0.88rem;">Spam Shield Verification</div>
                        <div style="font-size: 0.8rem; color: #475569;">
                            Please solve this math check: <strong><?= $captchaNum1 ?> + <?= $captchaNum2 ?></strong> = ?
                        </div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="number" name="captcha_answer" required placeholder="Answer" style="width: 100px; margin-bottom: 0; padding: 0.45rem 0.6rem; font-size: 0.95rem; text-align: center; font-weight: 800; border-radius: 6px;" autocomplete="off">
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                <button type="submit" style="background: #4f46e5; border-color: #4f46e5; color: #ffffff !important; font-weight: 700; font-size: 0.92rem; padding: 0.65rem 1.5rem; border-radius: 8px; display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer; margin-bottom: 0;">
                    <i class="fa-solid fa-paper-plane"></i> Submit Verified Review
                </button>
                <button type="button" onclick="toggleReviewForm()" style="background: #f1f5f9; border-color: #cbd5e1; color: #475569 !important; font-size: 0.88rem; font-weight: 600; padding: 0.65rem 1.1rem; border-radius: 8px; cursor: pointer; margin-bottom: 0;">
                    Cancel
                </button>
            </div>
        </form>
    </div>

    <!-- Review Listing -->
    <?php if (empty($reviews)): ?>
        <div style="text-align: center; padding: 3rem 1.5rem; background: #ffffff; border: 1px dashed #cbd5e1; border-radius: 12px;">
            <i class="fa-regular fa-comments" style="font-size: 2.75rem; color: #cbd5e1; margin-bottom: 0.75rem; display: block;"></i>
            <h4 style="margin: 0 0 0.35rem 0; font-size: 1.15rem; color: #1e293b;">No reviews yet</h4>
            <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 1.25rem;">Be the first student or educator to review this resource!</p>
            <button type="button" onclick="openReviewForm()" style="background: #4f46e5; border-color: #4f46e5; color: #ffffff !important; font-weight: 700; font-size: 0.88rem; padding: 0.55rem 1.25rem; border-radius: 8px; cursor: pointer; margin-bottom: 0;">
                <i class="fa-solid fa-pen"></i> Write the First Review
            </button>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <?php foreach ($reviews as $rev): 
                $role = $rev['author_role'] ?? 'Verified Student';
                $roleBg = '#ecfdf5';
                $roleColor = '#047857';
                $roleIcon = 'fa-user-graduate';
                if (stripos($role, 'educator') !== false || stripos($role, 'teacher') !== false) {
                    $roleBg = '#eff6ff';
                    $roleColor = '#1d4ed8';
                    $roleIcon = 'fa-chalkboard-user';
                } elseif (stripos($role, 'parent') !== false) {
                    $roleBg = '#faf5ff';
                    $roleColor = '#7e22ce';
                    $roleIcon = 'fa-users';
                }

                // Generate initials for avatar
                $nameParts = explode(' ', trim($rev['author_name']));
                $initials = '';
                if (!empty($nameParts[0])) $initials .= mb_strtoupper(mb_substr($nameParts[0], 0, 1));
                if (count($nameParts) > 1 && !empty($nameParts[1])) $initials .= mb_strtoupper(mb_substr($nameParts[1], 0, 1));
                if (empty($initials)) $initials = 'ST';
            ?>
                <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.35rem; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                    
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <!-- Initials Avatar -->
                            <div style="width: 40px; height: 40px; border-radius: 50%; background: #e0e7ff; color: #4338ca; font-weight: 800; font-size: 0.88rem; display: flex; align-items: center; justify-content: center; letter-spacing: -0.5px; border: 2px solid #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                                <?= htmlspecialchars($initials) ?>
                            </div>
                            <div>
                                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                    <strong style="color: #0f172a; font-size: 0.95rem;"><?= htmlspecialchars($rev['author_name']) ?></strong>
                                    <span style="background: <?= $roleBg ?>; color: <?= $roleColor ?>; font-size: 0.72rem; font-weight: 700; padding: 0.15rem 0.5rem; border-radius: 999px; display: inline-flex; align-items: center; gap: 0.25rem;">
                                        <i class="fa-solid <?= $roleIcon ?>"></i> <?= htmlspecialchars($role) ?>
                                    </span>
                                </div>
                                <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 0.15rem;">
                                    Reviewed on <?= !empty($rev['created_at']) ? date('M d, Y', strtotime($rev['created_at'])) : 'Recently' ?>
                                </div>
                            </div>
                        </div>

                        <!-- Stars -->
                        <div style="color: #f59e0b; font-size: 0.88rem; display: flex; gap: 0.15rem;">
                            <?php 
                            $rVal = (int)($rev['rating'] ?? 5);
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $rVal) {
                                    echo '<i class="fa-solid fa-star"></i>';
                                } else {
                                    echo '<i class="fa-regular fa-star" style="color: #cbd5e1;"></i>';
                                }
                            }
                            ?>
                        </div>
                    </div>

                    <p style="margin: 0; color: #334155; font-size: 0.92rem; line-height: 1.55;">
                        <?= nl2br(htmlspecialchars($rev['comment'])) ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<script>
const starDesc = {
    1: '1 Star - Poor',
    2: '2 Stars - Fair',
    3: '3 Stars - Good',
    4: '4 Stars - Very Good',
    5: '5 Stars - Exceptional'
};

function setRating(val) {
    document.getElementById('review-rating-input').value = val;
    document.getElementById('rating-label').textContent = starDesc[val] || (val + ' Stars');
    renderStars(val);
}

function hoverRating(val) {
    renderStars(val);
}

function resetRatingHover() {
    const cur = parseInt(document.getElementById('review-rating-input').value || 5, 10);
    renderStars(cur);
}

function renderStars(val) {
    for (let i = 1; i <= 5; i++) {
        const star = document.getElementById('star-btn-' + i);
        if (star) {
            if (i <= val) {
                star.style.color = '#f59e0b';
            } else {
                star.style.color = '#cbd5e1';
            }
        }
    }
}

function openReviewForm() {
    const box = document.getElementById('review-form-container');
    if (box) {
        box.style.display = 'block';
        box.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        const nameInp = document.getElementById('review-author-name');
        if (nameInp) nameInp.focus();
    }
}

function toggleReviewForm() {
    const box = document.getElementById('review-form-container');
    if (box) {
        if (box.style.display === 'none' || !box.style.display) {
            openReviewForm();
        } else {
            box.style.display = 'none';
        }
    }
}
</script>

<!-- Related Products Section: Same Course Recommendations -->
<?php if (!empty($related)): ?>
<div class="related-products-section" style="margin-bottom: 3.5rem; border-top: 1px solid #e2e8f0; padding-top: 2.5rem;">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.75rem;">
        <div>
            <div style="display: inline-flex; align-items: center; gap: 0.4rem; background: #e0e7ff; color: #3730a3; padding: 0.25rem 0.65rem; border-radius: 20px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; margin-bottom: 0.4rem;">
                <i class="fa-solid fa-graduation-cap"></i> <?= htmlspecialchars($product['grade']) ?> Curriculum
            </div>
            <h3 style="font-size: 1.4rem; font-weight: 900; color: #0f172a; margin: 0;">
                You Will Also Be Interested In
            </h3>
            <p style="color: #64748b; font-size: 0.9rem; margin: 0.2rem 0 0 0;">
                Related high-yield question banks, study sheets, and problem sets for <strong><?= htmlspecialchars($product['grade']) ?></strong>
            </p>
        </div>
        <a href="/?class=<?= urlencode($product['grade']) ?>" style="font-size: 0.88rem; font-weight: 700; color: #4f46e5; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem;">
            Explore All <?= htmlspecialchars($product['grade']) ?> Resources &rarr;
        </a>
    </div>

    <div class="product-grid">
        <?php foreach ($related as $rel): ?>
            <?php $relThumb = !empty($rel['thumbnail_url']) ? $rel['thumbnail_url'] : '/images/default-study-material.png'; ?>
            <div class="product-card">
                <a href="/product/<?= htmlspecialchars($rel['slug']) ?>" class="product-thumbnail-wrap" aria-label="View <?= htmlspecialchars($rel['title']) ?>">
                    <img src="<?= htmlspecialchars($relThumb) ?>" 
                         alt="<?= htmlspecialchars($rel['title']) ?>" 
                         referrerpolicy="no-referrer"
                         onerror="this.onerror=null; this.src='/images/default-study-material.png';">
                    <div class="badge-overlay">
                        <span class="chip" style="background: #0284c7;"><?= htmlspecialchars($rel['board'] ?? 'CBSE') ?></span>
                        <span class="chip"><?= htmlspecialchars($rel['grade']) ?></span>
                        <?php if ($rel['is_free']): ?>
                            <span class="chip chip-free">FREEBIE</span>
                        <?php endif; ?>
                    </div>
                </a>

                <div class="product-card-body">
                    <a href="/product/<?= htmlspecialchars($rel['slug']) ?>" class="product-title" style="font-size: 0.92rem; font-weight: 800; line-height: 1.35;">
                        <?= htmlspecialchars($rel['title']) ?>
                    </a>

                    <div class="product-author" style="margin-top: 0.25rem;">
                        <i class="fa-solid fa-circle-check" style="color: #0284c7; font-size: 0.8rem;"></i>
                        <span style="color: #475569; font-weight: 500; font-size: 0.78rem;">By <?= htmlspecialchars($rel['store_name'] ?? 'Lockinrank Academic Team') ?></span>
                    </div>

                    <div class="rating-stars" style="margin: 0.35rem 0;">
                        <?php
                        $fullStars = floor($rel['rating']);
                        for ($i = 0; $i < $fullStars; $i++): ?>
                            <i class="fa-solid fa-star"></i>
                        <?php endfor; ?>
                        <strong style="color: #b45309; font-size: 0.82rem;"><?= number_format($rel['rating'], 1) ?></strong>
                        <span class="rating-count" style="font-size: 0.75rem;">(<?= $rel['review_count'] ?>)</span>
                    </div>

                    <div class="product-card-footer" style="margin-top: auto; padding-top: 0.65rem; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; flex-direction: column; align-items: flex-start; line-height: 1.2;">
                            <div style="display: flex; align-items: baseline; gap: 0.35rem; flex-wrap: wrap;">
                                <?php if ($rel['is_free']): ?>
                                    <span class="product-price price-free" style="font-size: 1rem; font-weight: 800;">FREE</span>
                                <?php else: ?>
                                    <span class="product-price" style="font-size: 1rem; font-weight: 800; color: #0f172a;">₹<?= number_format($rel['price'], 0) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($rel['mrp']) && (float)$rel['mrp'] > (float)$rel['price']): ?>
                                    <span style="text-decoration: line-through; color: #94a3b8; font-size: 0.78rem; font-weight: 600;">₹<?= number_format($rel['mrp'], 0) ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($rel['mrp']) && (float)$rel['mrp'] > (float)$rel['price']): 
                                $relDiscount = round((((float)$rel['mrp'] - (float)$rel['price']) / (float)$rel['mrp']) * 100);
                            ?>
                                <span style="font-size: 0.68rem; font-weight: 800; color: #16a34a; margin-top: 0.1rem;">
                                    <?= $relDiscount ?>% OFF
                                </span>
                            <?php endif; ?>
                        </div>

                        <div style="display: flex; gap: 0.35rem;">
                            <button type="button" 
                                    class="btn-card-preview"
                                    hx-get="/product/preview/<?= $rel['id'] ?>" 
                                    hx-target="#preview-modal-content"
                                    onclick="openPreviewModal()"
                                    title="Quick Preview">
                                <i class="fa-solid fa-eye"></i>
                            </button>

                            <form action="/cart/add" method="POST" style="margin-bottom: 0;" 
                                  hx-post="/cart/add" 
                                  hx-target="#nav-cart-btn" 
                                  hx-swap="outerHTML">
                                <input type="hidden" name="product_id" value="<?= $rel['id'] ?>">
                                <button type="submit" 
                                        class="btn-card-cart"
                                        title="Add to Cart">
                                    <i class="fa-solid fa-cart-plus"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Mobile Sticky Bottom Purchase Bar (Docked to bottom on mobile devices) -->
<div class="mobile-sticky-purchase-bar" id="mobileStickyPurchaseBar">
    <div class="mobile-sticky-price-box">
        <div style="display: flex; align-items: baseline; gap: 0.35rem;">
            <span class="mobile-sticky-price">
                <?= $product['is_free'] ? '<span class="price-free">FREE</span>' : '₹' . number_format($product['price'], 0) ?>
            </span>
            <?php if (!empty($product['mrp']) && (float)$product['mrp'] > (float)$product['price']): 
                $mobDiscountPct = round((((float)$product['mrp'] - (float)$product['price']) / (float)$product['mrp']) * 100);
            ?>
                <span style="text-decoration: line-through; color: #94a3b8; font-size: 0.78rem; font-weight: 600;">₹<?= number_format($product['mrp'], 0) ?></span>
                <span style="background: #dcfce7; color: #15803d; font-size: 0.68rem; font-weight: 800; padding: 0.05rem 0.35rem; border-radius: 4px;"><?= $mobDiscountPct ?>% OFF</span>
            <?php endif; ?>
        </div>
        <span class="mobile-sticky-label">Single License</span>
    </div>
    <div class="mobile-sticky-action-box">
        <?php if ($isInCart): ?>
            <a href="/cart" role="button" class="btn-mobile-sticky btn-in-cart">
                <i class="fa-solid fa-circle-check"></i> In Cart &bull; Checkout &rarr;
            </a>
        <?php else: ?>
            <form action="/cart/add" method="POST" style="margin: 0; width: 100%;">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <button type="submit" class="btn-mobile-sticky btn-add-cart">
                    <i class="fa-solid fa-cart-shopping"></i> 
                    <?= $product['is_free'] ? 'Download Freebie' : 'Add to Cart (₹' . number_format($product['price'], 0) . ')' ?>
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/layout-footer.php'; ?>
