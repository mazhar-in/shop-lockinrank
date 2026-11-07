<?php 
$pageTitle = htmlspecialchars($product['title']) . " — Lockinrank Shop";
$hideMobileBottomNav = true; // Replace bottom nav with Sticky Purchase Bar on product page
include __DIR__ . '/layout-header.php'; 
?>

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
                     src="<?= htmlspecialchars($previewImages[0] ?? $product['thumbnail_url']) ?>" 
                     alt="<?= htmlspecialchars($product['title']) ?>">
                
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
                                <img src="<?= htmlspecialchars($imgUrl) ?>" alt="Sample preview <?= $idx + 1 ?>">
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
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; font-size: 0.9rem;">
            <span style="color: var(--tpt-orange); font-weight: 700;">
                <i class="fa-solid fa-star"></i> <?= number_format($product['rating'], 1) ?>
            </span>
            <span style="color: var(--pico-muted-color);">
                (<?= $product['review_count'] ?> reviews)
            </span>
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

        <!-- Price & Buy Box -->
        <div style="background-color: var(--pico-card-background-color); border: 1px solid var(--pico-muted-border-color); border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <span style="font-size: 0.88rem; color: var(--pico-muted-color); text-transform: uppercase; font-weight: 700;">Single-Student / Personal Study License</span>
                <span style="font-size: 1.8rem; font-weight: 800;">
                    <?= $product['is_free'] ? '<span class="price-free">FREE</span>' : '₹' . number_format($product['price'], 0) ?>
                </span>
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
        <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
        
        <h5 style="margin-top: 1.5rem; margin-bottom: 0.5rem;"><i class="fa-solid fa-check" style="color: var(--tpt-green);"></i> What is Included in this Pack:</h5>
        <ul>
            <li>Step-by-step student practice sheets with clean NCERT-pattern exercises</li>
            <li>Teacher answer keys with full calculation steps and hints</li>
            <li>High-contrast ink-friendly layout designed for monochrome printer toner saving</li>
            <li>Single-classroom or home-school license rights for unlimited printouts</li>
        </ul>
    </div>
</div>

<!-- Educator Reviews -->
<div style="margin-bottom: 3rem;">
    <h3 style="font-size: 1.3rem; margin-bottom: 1.25rem;">
        Educator Reviews & Feedback (<?= count($reviews) ?>)
    </h3>
    
    <div style="display: flex; flex-direction: column; gap: 1rem;">
        <?php foreach ($reviews as $rev): ?>
            <div style="background-color: var(--pico-card-background-color); border: 1px solid var(--pico-muted-border-color); border-radius: 8px; padding: 1.25rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <strong><?= htmlspecialchars($rev['author_name']) ?> <span class="brand-badge" style="background-color: #0284c7; font-size: 0.65rem;">Verified Educator</span></strong>
                    <span style="color: var(--tpt-orange); font-size: 0.85rem;">
                        <?php for ($i=0; $i<$rev['rating']; $i++): ?>
                            <i class="fa-solid fa-star"></i>
                        <?php endfor; ?>
                    </span>
                </div>
                <p style="margin-bottom: 0; color: var(--pico-color);"><?= htmlspecialchars($rev['comment']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Mobile Sticky Bottom Purchase Bar (Docked to bottom on mobile devices) -->
<div class="mobile-sticky-purchase-bar" id="mobileStickyPurchaseBar">
    <div class="mobile-sticky-price-box">
        <span class="mobile-sticky-price">
            <?= $product['is_free'] ? '<span class="price-free">FREE</span>' : '₹' . number_format($product['price'], 0) ?>
        </span>
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
