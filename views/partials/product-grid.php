<?php if (empty($products)): ?>
    <div style="grid-column: 1 / -1; text-align: center; padding: 3rem 1rem; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px;">
        <i class="fa-solid fa-folder-open" style="font-size: 3rem; color: #94a3b8; margin-bottom: 1rem;"></i>
        <h4 style="color: #0f172a;">No matching teaching materials found</h4>
        <p style="color: #64748b;">Try clearing some board/class filters or searching for different topics (e.g., Fractions, Phonics, Science).</p>
    </div>
<?php else: ?>
    <?php foreach ($products as $p): ?>
        <?php $thumbImg = !empty($p['thumbnail_url']) ? $p['thumbnail_url'] : '/images/default-study-material.png'; ?>
        <div class="product-card">
            <a href="/product/<?= htmlspecialchars($p['slug']) ?>" class="product-thumbnail-wrap" aria-label="View <?= htmlspecialchars($p['title']) ?>">
                <img src="<?= htmlspecialchars($thumbImg) ?>" 
                     alt="<?= htmlspecialchars($p['title']) ?>" 
                     referrerpolicy="no-referrer"
                     onerror="this.onerror=null; this.src='/images/default-study-material.png';">
                <div class="badge-overlay">
                    <span class="chip" style="background: #0284c7;"><?= htmlspecialchars($p['board'] ?? 'CBSE') ?></span>
                    <span class="chip"><?= htmlspecialchars($p['grade']) ?></span>
                    <?php if ($p['is_free']): ?>
                        <span class="chip chip-free">FREEBIE</span>
                    <?php elseif (!empty($p['mrp']) && (float)$p['mrp'] > (float)$p['price']): 
                        $cardDiscount = round((((float)$p['mrp'] - (float)$p['price']) / (float)$p['mrp']) * 100);
                    ?>
                        <span class="chip" style="background: #16a34a; color: #ffffff; font-weight: 800;">
                            <?= $cardDiscount ?>% OFF
                        </span>
                    <?php endif; ?>
                </div>
            </a>

            <div class="product-card-body">
                <a href="/product/<?= htmlspecialchars($p['slug']) ?>" class="product-title">
                    <?= htmlspecialchars($p['title']) ?>
                </a>

                <div class="product-author">
                    <i class="fa-solid fa-circle-check" style="color: #0284c7; font-size: 0.85rem;"></i>
                    <span style="color: #475569; font-weight: 500;">By <?= htmlspecialchars($p['store_name'] ?? 'Lockinrank Academic Team') ?></span>
                </div>

                <?php if (!empty($p['tags'])): 
                    $cardTags = array_slice(array_filter(array_map('trim', explode(',', $p['tags']))), 0, 3);
                ?>
                    <?php if (!empty($cardTags)): ?>
                        <div style="display: flex; gap: 0.3rem; flex-wrap: wrap; margin: 0.35rem 0;">
                            <?php foreach ($cardTags as $ct): ?>
                                <a href="/?tag=<?= urlencode($ct) ?>" style="font-size: 0.68rem; font-weight: 700; color: #4338ca; background: #eef2ff; border: 1px solid #c7d2fe; padding: 0.1rem 0.4rem; border-radius: 4px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.2rem;">
                                    #<?= htmlspecialchars($ct) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <div class="rating-stars">
                    <?php
                    $fullStars = floor($p['rating']);
                    for ($i = 0; $i < $fullStars; $i++): ?>
                        <i class="fa-solid fa-star"></i>
                    <?php endfor; ?>
                    <strong style="color: #b45309;"><?= number_format($p['rating'], 1) ?></strong>
                    <span class="rating-count">(<?= $p['review_count'] ?>)</span>

                    <?php if (!empty($p['is_ink_friendly'])): ?>
                        <span style="margin-left: auto; font-size: 0.72rem; color: #059669; font-weight: 700;" title="Printer Friendly / Black & White layout">
                            <i class="fa-solid fa-print"></i> B&W Eco
                        </span>
                    <?php endif; ?>
                </div>

                <div class="product-card-footer">
                    <div style="display: flex; flex-direction: column; align-items: flex-start; line-height: 1.2;">
                        <div style="display: flex; align-items: baseline; gap: 0.35rem; flex-wrap: wrap;">
                            <?php if ($p['is_free']): ?>
                                <span class="product-price price-free">FREE</span>
                            <?php else: ?>
                                <span class="product-price">₹<?= number_format($p['price'], 0) ?></span>
                            <?php endif; ?>

                            <?php if (!empty($p['mrp']) && (float)$p['mrp'] > (float)$p['price']): ?>
                                <span style="text-decoration: line-through; color: #94a3b8; font-size: 0.8rem; font-weight: 600;">₹<?= number_format($p['mrp'], 0) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($p['mrp']) && (float)$p['mrp'] > (float)$p['price']): 
                            $footerDiscount = round((((float)$p['mrp'] - (float)$p['price']) / (float)$p['mrp']) * 100);
                        ?>
                            <span style="font-size: 0.68rem; font-weight: 800; color: #16a34a; margin-top: 0.15rem;">
                                Save <?= $footerDiscount ?>%
                            </span>
                        <?php endif; ?>
                    </div>

                    <div style="display: flex; gap: 0.4rem;">
                        <!-- Quick Preview Modal Button (High Contrast) -->
                        <button type="button" 
                                class="btn-card-preview"
                                hx-get="/product/preview/<?= $p['id'] ?>" 
                                hx-target="#preview-modal-content"
                                onclick="openPreviewModal()"
                                title="Quick Watermarked Preview">
                            <i class="fa-solid fa-eye"></i> Preview
                        </button>

                        <!-- Add to Cart Form (High Contrast) -->
                        <form action="/cart/add" method="POST" style="margin-bottom: 0;" 
                              hx-post="/cart/add" 
                              hx-target="#nav-cart-btn" 
                              hx-swap="outerHTML">
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
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
<?php endif; ?>
