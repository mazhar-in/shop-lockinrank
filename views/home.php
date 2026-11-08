<?php 
$pageTitle = "Lockinrank Shop — Top-Rank Exam Prep, JEE, NEET & AP Physics";
$selectedClass = $selectedClass ?? 'All';
$selectedBoard = $selectedBoard ?? 'All';
$selectedSubject = $selectedSubject ?? 'All';
$selectedType = $selectedType ?? 'All';
$selectedPrice = $selectedPrice ?? 'all';
$searchQuery = $searchQuery ?? '';
include __DIR__ . '/layout-header.php'; 
?>

<!-- Hero Banner (Lockinrank Official Storefront) -->
<div class="hero-banner" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4338ca 100%);">
    <div style="max-width: 760px;">
        <span style="background: rgba(255,255,255,0.18); color: #ffffff !important; padding: 0.25rem 0.85rem; border-radius: 20px; font-size: 0.8rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; display: inline-flex; align-items: center; gap: 0.4rem;">
            <i class="fa-solid fa-circle-check" style="color: #6ee7b7;"></i> OFFICIAL LOCKINRANK STORE &bull; shop.lockinrank.com
        </span>
        <h1 style="color: #ffffff !important; font-size: 2.15rem; margin: 0.85rem 0 0.5rem 0; font-weight: 900; line-height: 1.25; letter-spacing: -0.5px;">
            Top-Rank Physics, Math & NEET Biology Prep Materials
        </h1>
        <p style="color: #e0e7ff !important; font-size: 1.05rem; opacity: 0.95; margin-bottom: 0; line-height: 1.5;">
            Master complex concepts with high-yield problem sets, formula cheatsheets, and <strong>College Board AP Physics 1 & C</strong> workbooks. Direct downloads starting at ₹99 & 100% Freebies.
        </p>
    </div>
</div>

<!-- Exam Quick Navigation Pills (High Contrast) -->
<div class="grade-bar">
    <span style="font-size: 0.84rem; font-weight: 800; color: #475569; display: flex; align-items: center; margin-right: 0.5rem; text-transform: uppercase;">
        Select Exam:
    </span>
    <?php foreach ($classes as $c): ?>
        <button type="button" 
                class="grade-pill <?= $c === $selectedClass ? 'active' : '' ?>" 
                onclick="selectClassPill(this, '<?= htmlspecialchars($c) ?>')">
            <?= htmlspecialchars($c) ?>
        </button>
    <?php endforeach; ?>
</div>

<!-- Two-Column Store Layout -->
<div class="catalog-layout">

    <!-- Mobile Backdrop for Filter Bottom Sheet -->
    <div class="filter-backdrop" id="filterBackdrop" onclick="closeFilterSheet()"></div>

    <!-- Filters Sidebar / Mobile Bottom Sheet -->
    <aside class="filters-sidebar" id="filtersSidebar">
        <!-- Drag Handle for Mobile Bottom Sheet -->
        <div class="sheet-drag-handle" onclick="closeFilterSheet()"></div>

        <form id="filter-form" 
              hx-get="/search" 
              hx-trigger="change" 
              hx-target="#product-grid" 
              hx-indicator="#search-loading">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h5 style="margin-bottom: 0; font-weight: 800;"><i class="fa-solid fa-sliders" style="color: #4f46e5;"></i> Filters</h5>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <a href="/" class="btn-reset" role="button" style="text-decoration: none; padding: 0.2rem 0.6rem; font-size: 0.78rem; display: inline-block;">Reset</a>
                    <button type="button" class="btn-close-sheet" onclick="closeFilterSheet()" aria-label="Close filters">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            <?php if (!empty($categories)): ?>
            <!-- Category Select -->
            <div class="filter-group">
                <label for="category-select" style="font-size: 0.85rem; font-weight: 700;">Curriculum Category</label>
                <select id="category-select" name="category" style="font-size: 0.85rem; color: #0f172a; background-color: #ffffff;">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['slug']) ?>" <?= (!empty($selectedCategory) && ($selectedCategory === $cat['slug'] || $selectedCategory == $cat['id'])) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <!-- Target Exam Select -->
            <div class="filter-group">
                <label for="class-select" style="font-size: 0.85rem; font-weight: 700;">Target Exam / Course</label>
                <select id="class-select" name="class" style="font-size: 0.85rem; color: #0f172a; background-color: #ffffff;">
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= htmlspecialchars($c) ?>" <?= ($c === $selectedClass) ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Board Pattern Select -->
            <div class="filter-group">
                <label for="board-select" style="font-size: 0.85rem; font-weight: 700;">Board / Authority Pattern</label>
                <select id="board-select" name="board" style="font-size: 0.85rem; color: #0f172a; background-color: #ffffff;">
                    <?php foreach ($boards as $b): ?>
                        <option value="<?= htmlspecialchars($b) ?>" <?= ($b === $selectedBoard) ? 'selected' : '' ?>><?= htmlspecialchars($b) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Subject Select -->
            <div class="filter-group">
                <label for="subject-select" style="font-size: 0.85rem; font-weight: 700;">Subject Area</label>
                <select id="subject-select" name="subject" style="font-size: 0.85rem; color: #0f172a; background-color: #ffffff;">
                    <?php foreach ($subjects as $s): ?>
                        <option value="<?= htmlspecialchars($s) ?>" <?= ($s === $selectedSubject) ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Resource Type -->
            <div class="filter-group">
                <label for="type-select" style="font-size: 0.85rem; font-weight: 700;">Format Type</label>
                <select id="type-select" name="type" style="font-size: 0.85rem; color: #0f172a; background-color: #ffffff;">
                    <?php foreach ($types as $t): ?>
                        <option value="<?= htmlspecialchars($t) ?>" <?= ($t === $selectedType) ? 'selected' : '' ?>><?= htmlspecialchars($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Eco Ink-Friendly Toggle -->
            <div class="filter-group">
                <label style="font-size: 0.84rem; font-weight: 700; cursor: pointer; color: #059669; display: flex; align-items: center; gap: 0.4rem;">
                    <input type="checkbox" name="ink_friendly" value="1">
                    <span><i class="fa-solid fa-leaf"></i> B&W Eco / Ink Friendly</span>
                </label>
            </div>

            <!-- Price Radios in INR -->
            <div class="filter-group">
                <label style="font-size: 0.85rem; font-weight: 700; margin-bottom: 0.5rem; display: block;">Price in INR (₹)</label>
                <label style="font-size: 0.84rem; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.4rem;">
                    <input type="radio" name="price" value="all" <?= ($selectedPrice === 'all') ? 'checked' : '' ?>> Any Price
                </label>
                <label style="font-size: 0.84rem; margin-bottom: 0.35rem; color: #16a34a; font-weight: 800; display: flex; align-items: center; gap: 0.4rem;">
                    <input type="radio" name="price" value="free" <?= ($selectedPrice === 'free') ? 'checked' : '' ?>> 100% Free Resources
                </label>
                <label style="font-size: 0.84rem; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.4rem;">
                    <input type="radio" name="price" value="under99" <?= ($selectedPrice === 'under99') ? 'checked' : '' ?>> Under ₹99
                </label>
                <label style="font-size: 0.84rem; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.4rem;">
                    <input type="radio" name="price" value="under199" <?= ($selectedPrice === 'under199') ? 'checked' : '' ?>> ₹99 to ₹199
                </label>
                <label style="font-size: 0.84rem; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.4rem;">
                    <input type="radio" name="price" value="above199" <?= ($selectedPrice === 'above199') ? 'checked' : '' ?>> Above ₹199
                </label>
            </div>

            <!-- Sort By -->
            <div class="filter-group">
                <label for="sort-select" style="font-size: 0.85rem; font-weight: 700;">Sort By</label>
                <select id="sort-select" name="sort" style="font-size: 0.85rem; color: #0f172a; background-color: #ffffff;">
                    <option value="popular">Most Downloaded</option>
                    <option value="rating">Highest Rated</option>
                    <option value="price_asc">Price: Low to High</option>
                    <option value="price_desc">Price: High to Low</option>
                    <option value="newest">Recently Added</option>
                </select>
            </div>

            <!-- Mobile Apply Button -->
            <div class="mobile-apply-filters">
                <button type="button" class="btn-apply-sheet" onclick="closeFilterSheet()">
                    <i class="fa-solid fa-check"></i> Apply Filters
                </button>
            </div>

        </form>
    </aside>

    <!-- Main Results Column -->
    <section>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
            <h4 style="margin-bottom: 0; font-size: 1.15rem; font-weight: 800; color: #0f172a;">
                Available Study Packs &amp; Question Banks
            </h4>
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <!-- Mobile Filter Trigger Button (Inline above grid) -->
                <button type="button" class="btn-mobile-filter-trigger" onclick="openFilterSheet()">
                    <i class="fa-solid fa-sliders"></i> Filter &amp; Sort
                </button>
                <span class="desktop-badge-signed" style="font-size: 0.82rem; color: #64748b; font-weight: 600;">
                    <i class="fa-solid fa-bolt" style="color: #f59e0b;"></i> Instant Signed PDF Access
                </span>
            </div>
        </div>

        <?php if (!empty($selectedTag)): ?>
            <div style="background: #eef2ff; border: 1px solid #c7d2fe; color: #3730a3; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.88rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.6rem; margin-bottom: 1rem;">
                <span><i class="fa-solid fa-tag" style="color: #4f46e5;"></i> Filtered by Tag: <strong>#<?= htmlspecialchars($selectedTag) ?></strong></span>
                <a href="/" style="background: #ffffff; color: #dc2626; border: 1px solid #fca5a5; padding: 0.15rem 0.5rem; border-radius: 4px; font-size: 0.78rem; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 0.25rem;">
                    Clear Filter <i class="fa-solid fa-xmark"></i>
                </a>
            </div>
        <?php endif; ?>

        <!-- The HTMX Target Grid -->
        <div id="product-grid" class="product-grid">
            <?php include __DIR__ . '/partials/product-grid.php'; ?>
        </div>
    </section>

</div>

<!-- Floating Action Button for Mobile Filter & Sort (Bottom Right) -->
<button type="button" class="floating-filter-btn" id="floatingFilterBtn" onclick="openFilterSheet()" aria-label="Filter & Sort">
    <i class="fa-solid fa-sliders"></i> Filters
</button>

<script>
function selectClassPill(btn, className) {
    document.querySelectorAll('.grade-pill').forEach(el => el.classList.remove('active'));
    btn.classList.add('active');
    
    const classSelect = document.getElementById('class-select');
    if (classSelect) {
        classSelect.value = className;
        htmx.trigger('#filter-form', 'change');
    }
}

function openFilterSheet() {
    const sidebar = document.getElementById('filtersSidebar');
    const backdrop = document.getElementById('filterBackdrop');
    if (sidebar) sidebar.classList.add('sheet-open');
    if (backdrop) backdrop.classList.add('active');
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
}

function closeFilterSheet() {
    const sidebar = document.getElementById('filtersSidebar');
    const backdrop = document.getElementById('filterBackdrop');
    if (sidebar) sidebar.classList.remove('sheet-open');
    if (backdrop) backdrop.classList.remove('active');
    document.body.style.overflow = '';
}
</script>

<?php include __DIR__ . '/layout-footer.php'; ?>
