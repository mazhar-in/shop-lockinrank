<header>
    <button aria-label="Close" rel="prev" onclick="closePreviewModal()"></button>
    <h4 style="margin-bottom: 0.25rem;"><?= htmlspecialchars($product['title']) ?></h4>
    <small style="color: var(--pico-muted-color);">
        By <?= htmlspecialchars($product['store_name']) ?> &bull; 
        <strong><?= htmlspecialchars($product['board'] ?? 'CBSE') ?></strong> &bull; 
        <?= htmlspecialchars($product['grade']) ?>
    </small>
</header>

<div style="text-align: center;">
    <div class="watermark-wrap" style="position: relative; background: #0f172a; min-height: 280px; display: flex; align-items: center; justify-content: center; border-radius: 8px;">
        <img id="modalPreviewImage" src="<?= htmlspecialchars($previewImages[0] ?? $product['thumbnail_url']) ?>" alt="Preview" style="width: 100%; max-height: 420px; object-fit: contain;">
        <div class="watermark-overlay" style="font-size: 1.8rem; padding: 0.35rem 1.25rem;">LOCKINRANK PREVIEW</div>

        <?php if (count($previewImages) > 1): ?>
            <button type="button" 
                    class="gallery-nav-btn prev-btn" 
                    onclick="stepModalImage(-1)" 
                    style="left: 10px; position: absolute;"
                    aria-label="Previous">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <button type="button" 
                    class="gallery-nav-btn next-btn" 
                    onclick="stepModalImage(1)" 
                    style="right: 10px; position: absolute;"
                    aria-label="Next">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
            <div class="gallery-counter-badge" style="top: 10px; right: 10px;">
                <span id="modalImgIndex">1</span> / <?= count($previewImages) ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if (count($previewImages) > 1): ?>
        <div style="display: flex; gap: 0.5rem; justify-content: center; margin: 0.75rem 0; overflow-x: auto; padding-bottom: 0.25rem;">
            <?php foreach ($previewImages as $mIdx => $mUrl): ?>
                <button type="button" 
                        class="gallery-thumb-btn <?= $mIdx === 0 ? 'active' : '' ?>" 
                        id="modalThumb_<?= $mIdx ?>"
                        onclick="setModalImage(<?= $mIdx ?>)" 
                        style="width: 54px !important; height: 54px !important; flex: 0 0 54px !important;">
                    <img src="<?= htmlspecialchars($mUrl) ?>" alt="Thumb <?= $mIdx + 1 ?>">
                </button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <p style="font-size: 0.85rem; color: var(--pico-muted-color); margin-bottom: 0;">
        <i class="fa-solid fa-lock"></i> Watermarked sample worksheets (<?= count($previewImages) ?> preview page<?= count($previewImages) > 1 ? 's' : '' ?>). Full <?= $product['page_count'] ?>-page printable unlocked upon checkout.
    </p>
</div>

<script>
    (function() {
        const modalImgs = <?= json_encode($previewImages) ?>;
        let curModalIdx = 0;

        window.setModalImage = function(idx) {
            if (idx < 0 || idx >= modalImgs.length) return;
            curModalIdx = idx;
            const img = document.getElementById('modalPreviewImage');
            const counter = document.getElementById('modalImgIndex');
            if (img) img.src = modalImgs[curModalIdx];
            if (counter) counter.textContent = curModalIdx + 1;

            modalImgs.forEach((_, i) => {
                const thumb = document.getElementById('modalThumb_' + i);
                if (thumb) {
                    if (i === curModalIdx) thumb.classList.add('active');
                    else thumb.classList.remove('active');
                }
            });
        };

        window.stepModalImage = function(delta) {
            let next = curModalIdx + delta;
            if (next < 0) next = modalImgs.length - 1;
            if (next >= modalImgs.length) next = 0;
            setModalImage(next);
        };
    })();
</script>

<footer>
    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
        <span style="font-size: 1.35rem; font-weight: 800;">
            <?= $product['is_free'] ? '<span class="price-free">FREE</span>' : '₹' . number_format($product['price'], 0) ?>
        </span>
        <div style="display: flex; gap: 0.5rem;">
            <a href="/product/<?= htmlspecialchars($product['slug']) ?>" role="button" class="secondary outline" style="margin-bottom: 0;">
                Full Details
            </a>
            <form action="/cart/add" method="POST" style="margin-bottom: 0;" hx-post="/cart/add" hx-target="#nav-cart-btn" hx-swap="outerHTML">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <button type="submit" style="margin-bottom: 0;" onclick="closePreviewModal()">
                    <i class="fa-solid fa-cart-plus"></i> Add to Cart
                </button>
            </form>
        </div>
    </div>
</footer>
