<?php 
$pageTitle = htmlspecialchars($store['name']) . " — Lockinrank Official Store";
include __DIR__ . '/layout-header.php'; 
?>

<!-- Storefront Banner Header -->
<div class="store-header">
    <img src="<?= htmlspecialchars($store['avatar']) ?>" alt="<?= htmlspecialchars($store['name']) ?>" class="store-avatar-large">
    <div style="flex: 1;">
        <span class="chip" style="margin-bottom: 0.5rem; display: inline-block; background-color: #4f46e5;">Official Lockinrank Repository</span>
        <h1 style="margin: 0.25rem 0 0.5rem 0; font-size: 1.8rem; font-weight: 800;"><?= htmlspecialchars($store['name']) ?></h1>
        <p style="color: var(--pico-muted-color); margin-bottom: 0.75rem; font-weight: 600;"><?= htmlspecialchars($store['author_name']) ?></p>
        <p style="margin-bottom: 0.75rem; max-width: 700px;"><?= htmlspecialchars($store['bio']) ?></p>
        
        <div style="display: flex; gap: 1.5rem; font-size: 0.9rem; flex-wrap: wrap;">
            <span><strong style="color: var(--tpt-orange);"><i class="fa-solid fa-star"></i> <?= number_format($store['rating'], 1) ?></strong> (<?= $store['total_reviews'] ?> reviews)</span>
            <span><strong><i class="fa-solid fa-bag-shopping"></i> <?= $store['total_sales'] ?></strong> Downloads</span>
            <span style="color: var(--tpt-green);"><i class="fa-solid fa-circle-check"></i> UPI Verified: <?= htmlspecialchars($store['upi_id'] ?? 'creator@upi') ?></span>
        </div>
    </div>
</div>

<h3 style="margin-bottom: 1.5rem; font-size: 1.3rem;">
    Classroom Resources by <?= htmlspecialchars($store['author_name']) ?> (<?= count($products) ?>)
</h3>

<div class="product-grid">
    <?php include __DIR__ . '/partials/product-grid.php'; ?>
</div>

<?php include __DIR__ . '/layout-footer.php'; ?>
