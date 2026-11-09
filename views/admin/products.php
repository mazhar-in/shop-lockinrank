<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Manage Products — Lockinrank Control Panel') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel="stylesheet" href="/css/app.min.css?v=2.5">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body style="background-color: #f8fafc;">

    <?php 
    $activeTab = 'products';
    include __DIR__ . '/layout-nav.php'; 
    ?>

    <main class="container" style="max-width: 1300px; padding: 2rem 1.5rem;">
        
        <!-- Header & Action -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h1 style="font-size: 1.7rem; font-weight: 900; margin: 0 0 0.25rem 0; color: #0f172a;">
                    Resource & Product Catalog
                </h1>
                <p style="color: #64748b; margin: 0; font-size: 0.92rem;">
                    Manage course notes, problem sets, sample previews, and prices (<?= number_format($totalProducts) ?> total items)
                </p>
            </div>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <button type="button" onclick="openImportModal()" style="background-color: #059669; border-color: #059669; color: #ffffff !important; font-weight: 700; font-size: 0.9rem; padding: 0.55rem 1.15rem; display: inline-flex; align-items: center; gap: 0.5rem; border-radius: 8px; cursor: pointer; margin-bottom: 0;">
                    <i class="fa-solid fa-file-arrow-up"></i> Import JSON
                </button>
                <a href="/admin/upload" role="button" style="background-color: #4f46e5; border-color: #4f46e5; color: #ffffff !important; font-weight: 700; font-size: 0.9rem; padding: 0.55rem 1.25rem; display: inline-flex; align-items: center; gap: 0.5rem; border-radius: 8px; text-decoration: none; margin-bottom: 0;">
                    <i class="fa-solid fa-plus"></i> Publish New Material
                </a>
            </div>
        </div>

        <!-- Search & Filter Controls -->
        <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04); margin-bottom: 1.5rem;">
            <form method="GET" action="/admin/products" style="margin: 0; display: grid; grid-template-columns: 2fr 1fr 1fr auto auto; gap: 0.75rem; align-items: center;">
                <div>
                    <input type="search" name="q" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Search by title, subject..." style="margin-bottom: 0; font-size: 0.88rem;">
                </div>
                <div>
                    <select name="grade" style="margin-bottom: 0; font-size: 0.88rem;">
                        <option value="">All Exams / Grades</option>
                        <?php foreach ($grades as $g): ?>
                            <option value="<?= htmlspecialchars($g) ?>" <?= ($gradeFilter === $g) ? 'selected' : '' ?>><?= htmlspecialchars($g) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <select name="board" style="margin-bottom: 0; font-size: 0.88rem;">
                        <option value="">All Boards</option>
                        <?php foreach ($boards as $b): ?>
                            <option value="<?= htmlspecialchars($b) ?>" <?= ($boardFilter === $b) ? 'selected' : '' ?>><?= htmlspecialchars($b) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <button type="submit" style="background-color: #0f172a; border-color: #0f172a; color: #fff; margin-bottom: 0; font-size: 0.88rem; padding: 0.55rem 1.2rem; font-weight: 700;">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>
                </div>
                <?php if (!empty($search) || !empty($gradeFilter) || !empty($boardFilter)): ?>
                    <div>
                        <a href="/admin/products" role="button" style="background-color: #f1f5f9; color: #475569 !important; border-color: #cbd5e1; margin-bottom: 0; font-size: 0.88rem; padding: 0.55rem 0.9rem;">
                            Reset
                        </a>
                    </div>
                <?php endif; ?>
            </form>
        </div>

        <!-- Products Table & Bulk Delete Form -->
        <form id="bulk-delete-form" method="POST" action="/admin/products/bulk-delete" style="margin: 0;">
            <!-- Bulk Actions Floating/Sticky Bar -->
            <div id="bulk-action-bar" style="display: none; background: #0f172a; border: 1px solid #334155; color: #ffffff; padding: 0.85rem 1.25rem; border-radius: 8px; margin-bottom: 1.25rem; align-items: center; justify-content: space-between; box-shadow: 0 4px 14px rgba(0,0,0,0.12); flex-wrap: wrap; gap: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <i class="fa-solid fa-list-check" style="color: #60a5fa; font-size: 1.1rem;"></i>
                    <span style="font-size: 0.95rem; font-weight: 600;">
                        <strong id="selected-count" style="color: #fbbf24; font-size: 1.1rem;">0</strong> products selected
                    </span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <button type="button" onclick="deselectAll()" style="background: #334155; border: 1px solid #475569; color: #f1f5f9; padding: 0.45rem 0.9rem; font-size: 0.85rem; border-radius: 6px; cursor: pointer; margin-bottom: 0;">
                        <i class="fa-solid fa-xmark"></i> Deselect All
                    </button>
                    <button type="button" onclick="confirmBulkDelete()" style="background: #dc2626; border: 1px solid #b91c1c; color: #ffffff; padding: 0.45rem 1.1rem; font-size: 0.85rem; font-weight: 700; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 0;">
                        <i class="fa-solid fa-trash-can"></i> Delete Selected (<span id="delete-btn-count">0</span>)
                    </button>
                </div>
            </div>

            <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 1.5rem;">
                <?php if (empty($products)): ?>
                    <div style="text-align: center; padding: 3rem 1rem; color: #64748b;">
                        <i class="fa-solid fa-box-open" style="font-size: 2.5rem; color: #cbd5e1; margin-bottom: 0.75rem;"></i>
                        <p style="margin: 0; font-size: 1rem;">No products match your search or filter criteria.</p>
                        <a href="/admin/products" style="margin-top: 0.75rem; display: inline-block; font-size: 0.88rem; color: #4f46e5; font-weight: 700;">Clear Filters</a>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table style="font-size: 0.88rem; width: 100%;">
                            <thead>
                                <tr style="border-bottom: 2px solid #e2e8f0;">
                                    <th style="padding: 0.75rem 0.5rem; width: 40px; text-align: center;">
                                        <input type="checkbox" id="select-all" title="Select All on this page" onchange="toggleSelectAll(this)" style="cursor: pointer; margin: 0; width: 17px; height: 17px;">
                                    </th>
                                    <th style="padding: 0.75rem 0.5rem;">Resource Details</th>
                                    <th style="padding: 0.75rem 0.5rem;">Exam & Board</th>
                                    <th style="padding: 0.75rem 0.5rem;">Price</th>
                                    <th style="padding: 0.75rem 0.5rem;">Sample Downloads</th>
                                    <th style="padding: 0.75rem 0.5rem;">Paid Sales</th>
                                    <th style="padding: 0.75rem 0.5rem;">Rating</th>
                                    <th style="padding: 0.75rem 0.5rem; text-align: right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $p): ?>
                                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background-color 0.15s ease;">
                                        <td style="padding: 0.85rem 0.5rem; text-align: center;">
                                            <input type="checkbox" name="product_ids[]" value="<?= $p['id'] ?>" class="product-checkbox" onchange="updateBulkBar()" style="cursor: pointer; margin: 0; width: 17px; height: 17px;">
                                        </td>
                                        <td style="padding: 0.85rem 0.5rem;">
                                            <div style="font-weight: 700; color: #0f172a; font-size: 0.95rem;"><?= htmlspecialchars($p['title']) ?></div>
                                            <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem; display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                                                <span>Subject: <strong><?= htmlspecialchars($p['subject']) ?></strong></span>
                                                <span>&bull;</span>
                                                <span><?= $p['page_count'] ?> Pages</span>
                                                <span>&bull;</span>
                                                <span><?= htmlspecialchars($p['file_format']) ?></span>
                                                <span>&bull;</span>
                                                <?php if (!empty($p['material_file_path'])): ?>
                                                    <span style="background: #ecfdf5; color: #047857; padding: 0.05rem 0.4rem; border-radius: 4px; font-weight: 700; font-size: 0.7rem;">
                                                        <i class="fa-solid fa-file-circle-check"></i> File Ready
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (!empty($p['sample_file_path'])): ?>
                                                    <span style="background: #eff6ff; color: #1d4ed8; padding: 0.05rem 0.4rem; border-radius: 4px; font-weight: 700; font-size: 0.7rem;">
                                                        <i class="fa-solid fa-file-lines"></i> Sample Ready
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td style="padding: 0.85rem 0.5rem;">
                                            <span class="chip" style="background: #4f46e5; color: #ffffff;"><?= htmlspecialchars($p['grade']) ?></span>
                                            <span class="chip" style="background: #e0e7ff; color: #3730a3;"><?= htmlspecialchars($p['board']) ?></span>
                                        </td>
                                        <td style="padding: 0.85rem 0.5rem;">
                                            <div style="display: flex; flex-direction: column; gap: 0.2rem;">
                                                <div style="display: flex; align-items: baseline; gap: 0.35rem; flex-wrap: wrap;">
                                                    <?php if ($p['is_free']): ?>
                                                        <span style="color: #16a34a; font-weight: 900; font-size: 0.95rem;">FREE</span>
                                                    <?php else: ?>
                                                        <span style="color: #0f172a; font-weight: 900; font-size: 0.95rem;">₹<?= number_format($p['price'], 0) ?></span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($p['mrp']) && (float)$p['mrp'] > (float)$p['price']): ?>
                                                        <span style="text-decoration: line-through; color: #94a3b8; font-size: 0.78rem; font-weight: 600;">₹<?= number_format($p['mrp'], 0) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if (!empty($p['mrp']) && (float)$p['mrp'] > (float)$p['price']): 
                                                    $discountPct = round((((float)$p['mrp'] - (float)$p['price']) / (float)$p['mrp']) * 100);
                                                ?>
                                                    <div>
                                                        <span style="background-color: #dcfce7; color: #15803d; font-size: 0.7rem; font-weight: 800; padding: 0.1rem 0.4rem; border-radius: 4px; display: inline-block;">
                                                            <?= $discountPct ?>% OFF
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td style="padding: 0.85rem 0.5rem; font-weight: 700; color: #4338ca;">
                                            <?php if ($p['is_free']): ?>
                                                <span style="color: #94a3b8; font-size: 0.78rem;">100% Freebie</span>
                                            <?php else: ?>
                                                <i class="fa-solid fa-file-arrow-down" style="color: #818cf8;"></i> <?= number_format($p['sample_downloads'] ?? 0) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 0.85rem 0.5rem; font-weight: 700; color: #0f172a;">
                                            <?= number_format($p['sales_count']) ?>
                                        </td>
                                        <td style="padding: 0.85rem 0.5rem; color: #d97706; font-weight: 700;">
                                            <i class="fa-solid fa-star"></i> <?= number_format($p['rating'], 1) ?> (<?= $p['review_count'] ?>)
                                        </td>
                                        <td style="padding: 0.85rem 0.5rem; text-align: right; white-space: nowrap;">
                                            <a href="/admin/product/edit/<?= $p['id'] ?>" style="font-size: 0.8rem; padding: 0.35rem 0.65rem; background: #eef2ff; border-radius: 6px; color: #4f46e5; text-decoration: none; margin-right: 0.35rem; font-weight: 700;">
                                                <i class="fa-solid fa-pen-to-square"></i> Edit
                                            </a>
                                            <a href="/product/<?= htmlspecialchars($p['slug']) ?>" target="_blank" style="font-size: 0.8rem; padding: 0.35rem 0.65rem; background: #f1f5f9; border-radius: 6px; color: #334155; text-decoration: none; margin-right: 0.35rem;">
                                                <i class="fa-solid fa-eye"></i> View
                                            </a>
                                            <a href="/admin/product/delete/<?= $p['id'] ?>" onclick="return confirm('Are you sure you want to permanently delete \'<?= addslashes(htmlspecialchars($p['title'])) ?>\'?')" style="font-size: 0.8rem; padding: 0.35rem 0.65rem; background: #fee2e2; border-radius: 6px; color: #dc2626; text-decoration: none;" title="Delete Resource">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </form>

        <script>
        function toggleSelectAll(master) {
            const checkboxes = document.querySelectorAll('.product-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = master.checked;
                highlightRow(cb);
            });
            updateBulkBar();
        }

        function updateBulkBar() {
            const checkboxes = document.querySelectorAll('.product-checkbox');
            const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
            const count = checkedBoxes.length;
            const bar = document.getElementById('bulk-action-bar');
            const countSpan = document.getElementById('selected-count');
            const btnCountSpan = document.getElementById('delete-btn-count');
            const selectAll = document.getElementById('select-all');

            if (countSpan) countSpan.textContent = count;
            if (btnCountSpan) btnCountSpan.textContent = count;

            if (selectAll) {
                selectAll.checked = checkboxes.length > 0 && count === checkboxes.length;
                selectAll.indeterminate = count > 0 && count < checkboxes.length;
            }

            checkboxes.forEach(cb => highlightRow(cb));

            if (bar) {
                if (count > 0) {
                    bar.style.display = 'flex';
                } else {
                    bar.style.display = 'none';
                }
            }
        }

        function highlightRow(checkbox) {
            const row = checkbox.closest('tr');
            if (!row) return;
            if (checkbox.checked) {
                row.style.backgroundColor = '#fef2f2';
            } else {
                row.style.backgroundColor = '';
            }
        }

        function deselectAll() {
            const selectAll = document.getElementById('select-all');
            if (selectAll) selectAll.checked = false;
            document.querySelectorAll('.product-checkbox').forEach(cb => {
                cb.checked = false;
                highlightRow(cb);
            });
            updateBulkBar();
        }

        function confirmBulkDelete() {
            const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
            const count = checkedBoxes.length;
            if (count === 0) {
                alert('Please select at least one product to delete.');
                return;
            }

            if (confirm(`⚠️ Are you sure you want to permanently delete these ${count} selected products?\n\nThis will remove them immediately. This cannot be undone.`)) {
                document.getElementById('bulk-delete-form').submit();
            }
        }
        </script>

        <!-- Pagination (10 per page) -->
        <?php if ($totalPages > 1): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <span style="font-size: 0.88rem; color: #64748b;">
                    Showing Page <strong><?= $page ?></strong> of <strong><?= $totalPages ?></strong> (<?= number_format($totalProducts) ?> total resources)
                </span>
                <div style="display: flex; gap: 0.5rem;">
                    <?php 
                        $queryParams = $_GET;
                        $prevPageUrl = '?' . http_build_query(array_merge($queryParams, ['page' => max(1, $page - 1)]));
                        $nextPageUrl = '?' . http_build_query(array_merge($queryParams, ['page' => min($totalPages, $page + 1)]));
                    ?>
                    <?php if ($page > 1): ?>
                        <a href="<?= $prevPageUrl ?>" role="button" style="padding: 0.4rem 0.85rem; font-size: 0.85rem; background: #ffffff; color: #0f172a !important; border: 1px solid #cbd5e1; text-decoration: none;">
                            &laquo; Previous
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i === $page): ?>
                            <span style="padding: 0.4rem 0.85rem; font-size: 0.85rem; background: #4f46e5; color: #ffffff; border-radius: 6px; font-weight: 700;">
                                <?= $i ?>
                            </span>
                        <?php elseif ($i == 1 || $i == $totalPages || abs($i - $page) <= 2): ?>
                            <a href="?<?= http_build_query(array_merge($queryParams, ['page' => $i])) ?>" role="button" style="padding: 0.4rem 0.85rem; font-size: 0.85rem; background: #ffffff; color: #0f172a !important; border: 1px solid #cbd5e1; text-decoration: none;">
                                <?= $i ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?= $nextPageUrl ?>" role="button" style="padding: 0.4rem 0.85rem; font-size: 0.85rem; background: #ffffff; color: #0f172a !important; border: 1px solid #cbd5e1; text-decoration: none;">
                            Next &raquo;
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </main>

    <!-- Modal for JSON Import -->
    <dialog id="import-json-modal" style="border: none; border-radius: 12px; padding: 0; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25), 0 10px 10px -5px rgba(0, 0, 0, 0.08); max-width: 650px; width: 92%; margin: auto;">
        <div style="background: #ffffff; border-radius: 12px; overflow: hidden;">
            <!-- Modal Header -->
            <div style="background: #0f172a; color: #ffffff; padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 0.6rem;">
                    <div style="width: 32px; height: 32px; border-radius: 6px; background: #059669; display: flex; align-items: center; justify-content: center; font-size: 1rem;">
                        <i class="fa-solid fa-file-import"></i>
                    </div>
                    <div>
                        <h3 style="margin: 0; font-size: 1.15rem; font-weight: 800; color: #ffffff;">Import Products from JSON</h3>
                        <p style="margin: 0; font-size: 0.78rem; color: #94a3b8;">Upload single product or bulk product array</p>
                    </div>
                </div>
                <button type="button" onclick="closeImportModal()" style="background: none; border: none; color: #94a3b8; font-size: 1.4rem; cursor: pointer; padding: 0.2rem 0.5rem; line-height: 1;">&times;</button>
            </div>

            <!-- Modal Body -->
            <div style="padding: 1.5rem;">
                <form method="POST" action="/admin/products/import-json" enctype="multipart/form-data" style="margin-bottom: 0;">
                    
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1.25rem; font-size: 0.85rem; color: #334155; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                        <div>
                            <strong>Full Product JSON Specification:</strong>
                            <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.2rem;">
                                Supports <code>title</code>, <code>category</code>, <code>grade</code>, <code>board</code>, <code>subject</code>, <code>price</code>, <code>mrp</code>, <code>description</code>, <code>key_highlights</code>, <code>tags</code>, <code>thumbnail_url</code>, etc.
                            </div>
                        </div>
                        <a href="/admin/products/export-sample-json" download="sample-products.json" style="color: #059669; font-weight: 700; text-decoration: underline; display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.82rem;">
                            <i class="fa-solid fa-download"></i> Download Full Sample JSON
                        </a>
                    </div>

                    <!-- Option 1: File Upload -->
                    <div style="margin-bottom: 1.25rem;">
                        <label style="font-weight: 800; font-size: 0.9rem; color: #0f172a; margin-bottom: 0.4rem; display: block;">
                            <i class="fa-solid fa-file-circle-plus" style="color: #059669;"></i> Upload JSON File (.json)
                        </label>
                        <input type="file" name="json_file" accept=".json,application/json" style="font-size: 0.88rem; margin-bottom: 0.25rem;">
                        <small style="color: #64748b; font-size: 0.78rem;">Select a valid .json file from your computer.</small>
                    </div>

                    <div style="text-align: center; color: #94a3b8; font-weight: 700; font-size: 0.82rem; margin: 1rem 0; position: relative;">
                        <span style="background: #ffffff; padding: 0 0.75rem; position: relative; z-index: 1;">OR PASTE JSON DIRECTLY</span>
                        <hr style="position: absolute; top: 50%; left: 0; right: 0; margin: 0; border: none; border-top: 1px solid #e2e8f0; z-index: 0;">
                    </div>

                    <!-- Option 2: Paste JSON Content -->
                    <div style="margin-bottom: 1.5rem;">
                        <label style="font-weight: 800; font-size: 0.9rem; color: #0f172a; margin-bottom: 0.4rem; display: block;">
                            <i class="fa-solid fa-code" style="color: #4f46e5;"></i> Raw JSON Text
                        </label>
                        <textarea name="json_content" rows="6" placeholder='[&#10;  {&#10;    "title": "51 DPPs for NEET Physics Class 11",&#10;    "description": "# Syllabus...",&#10;    "price": 299,&#10;    "mrp": 699,&#10;    "grade": "NEET (Medical)",&#10;    "subject": "Physics"&#10;  }&#10;]' style="font-family: monospace; font-size: 0.82rem; margin-bottom: 0.25rem;"></textarea>
                    </div>

                    <!-- Modal Actions -->
                    <div style="display: flex; justify-content: flex-end; gap: 0.75rem; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 1.25rem;">
                        <button type="button" onclick="closeImportModal()" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 0.5rem 1.1rem; font-size: 0.88rem; border-radius: 6px; cursor: pointer; margin-bottom: 0;">
                            Cancel
                        </button>
                        <button type="submit" style="background: #059669; border-color: #059669; color: #ffffff; padding: 0.5rem 1.4rem; font-size: 0.88rem; font-weight: 700; border-radius: 6px; cursor: pointer; margin-bottom: 0; display: inline-flex; align-items: center; gap: 0.4rem;">
                            <i class="fa-solid fa-upload"></i> Import Products
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </dialog>

    <script>
    function openImportModal() {
        const modal = document.getElementById('import-json-modal');
        if (modal) {
            if (typeof modal.showModal === 'function') {
                modal.showModal();
            } else {
                modal.setAttribute('open', '');
            }
        }
    }
    function closeImportModal() {
        const modal = document.getElementById('import-json-modal');
        if (modal) {
            if (typeof modal.close === 'function') {
                modal.close();
            } else {
                modal.removeAttribute('open');
            }
        }
    }
    </script>
</body>
</html>

