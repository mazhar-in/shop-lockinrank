<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Category Management — Lockinrank Control Panel') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel="stylesheet" href="/css/app.min.css?v=2.4">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body style="background-color: #f8fafc;">

    <?php 
    $activeTab = 'categories';
    include __DIR__ . '/layout-nav.php'; 
    ?>

    <main class="container" style="max-width: 1300px; padding: 2rem 1.5rem;">
        
        <!-- Header & Action -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h1 style="font-size: 1.7rem; font-weight: 900; margin: 0 0 0.25rem 0; color: #0f172a;">
                    Curriculum & Resource Categories
                </h1>
                <p style="color: #64748b; margin: 0; font-size: 0.92rem;">
                    Organize your academic catalog by Exam, Curriculum, and Resource Themes (<?= count($categories) ?> total categories)
                </p>
            </div>
            <div>
                <button type="button" onclick="openCreateModal()" style="background-color: #4f46e5; border-color: #4f46e5; color: #ffffff !important; font-weight: 700; font-size: 0.9rem; padding: 0.55rem 1.25rem; display: inline-flex; align-items: center; gap: 0.5rem; border-radius: 8px; cursor: pointer; margin-bottom: 0;">
                    <i class="fa-solid fa-plus"></i> Add New Category
                </button>
            </div>
        </div>

        <!-- Categories Table Card -->
        <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 1.5rem;">
            <?php if (empty($categories)): ?>
                <div style="text-align: center; padding: 3rem 1rem; color: #64748b;">
                    <i class="fa-solid fa-folder-open" style="font-size: 2.5rem; color: #cbd5e1; margin-bottom: 0.75rem; display: block;"></i>
                    <p style="margin: 0; font-size: 1rem;">No categories have been created yet.</p>
                    <button type="button" onclick="openCreateModal()" style="margin-top: 0.75rem; font-size: 0.88rem; background: #4f46e5; color: #fff; border-radius: 6px; padding: 0.4rem 1rem;">
                        Create First Category
                    </button>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="font-size: 0.88rem; width: 100%;">
                        <thead>
                            <tr style="border-bottom: 2px solid #e2e8f0;">
                                <th style="padding: 0.75rem 0.5rem; width: 60px; text-align: center;">Order</th>
                                <th style="padding: 0.75rem 0.5rem;">Category Info</th>
                                <th style="padding: 0.75rem 0.5rem;">Slug</th>
                                <th style="padding: 0.75rem 0.5rem; text-align: center;">Assigned Items</th>
                                <th style="padding: 0.75rem 0.5rem; text-align: center;">Status</th>
                                <th style="padding: 0.75rem 0.5rem; text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 0.85rem 0.5rem; text-align: center; font-weight: 700; color: #64748b;">
                                        #<?= (int)$cat['display_order'] ?>
                                    </td>
                                    <td style="padding: 0.85rem 0.5rem;">
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <div style="width: 36px; height: 36px; border-radius: 8px; background: #e0e7ff; color: #4338ca; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                                                <i class="<?= htmlspecialchars($cat['icon'] ?: 'fa-solid fa-folder') ?>"></i>
                                            </div>
                                            <div>
                                                <div style="font-weight: 800; color: #0f172a; font-size: 0.95rem;">
                                                    <?= htmlspecialchars($cat['name']) ?>
                                                </div>
                                                <?php if (!empty($cat['description'])): ?>
                                                    <div style="font-size: 0.78rem; color: #64748b; margin-top: 0.15rem; max-width: 450px;">
                                                        <?= htmlspecialchars($cat['description']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="padding: 0.85rem 0.5rem; font-family: monospace; font-size: 0.82rem; color: #475569;">
                                        <code><?= htmlspecialchars($cat['slug']) ?></code>
                                    </td>
                                    <td style="padding: 0.85rem 0.5rem; text-align: center;">
                                        <span style="background: #eef2ff; color: #4338ca; padding: 0.2rem 0.65rem; border-radius: 999px; font-weight: 700; font-size: 0.78rem;">
                                            <i class="fa-solid fa-cubes"></i> <?= number_format($cat['product_count']) ?> products
                                        </span>
                                    </td>
                                    <td style="padding: 0.85rem 0.5rem; text-align: center;">
                                        <?php if (!empty($cat['is_active'])): ?>
                                            <span style="background: #ecfdf5; color: #047857; padding: 0.15rem 0.55rem; border-radius: 999px; font-weight: 700; font-size: 0.75rem;">
                                                Active
                                            </span>
                                        <?php else: ?>
                                            <span style="background: #f1f5f9; color: #64748b; padding: 0.15rem 0.55rem; border-radius: 999px; font-weight: 700; font-size: 0.75rem;">
                                                Hidden
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 0.85rem 0.5rem; text-align: right; white-space: nowrap;">
                                        <button type="button" 
                                                onclick='openEditModal(<?= json_encode($cat) ?>)'
                                                style="font-size: 0.8rem; padding: 0.35rem 0.65rem; background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 6px; color: #4f46e5; margin-right: 0.35rem; font-weight: 700; cursor: pointer;">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </button>
                                        <a href="/?category=<?= urlencode($cat['slug']) ?>" target="_blank" style="font-size: 0.8rem; padding: 0.35rem 0.65rem; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px; color: #334155; text-decoration: none; margin-right: 0.35rem; display: inline-block;">
                                            <i class="fa-solid fa-eye"></i> View
                                        </a>
                                        <a href="/admin/category/delete/<?= $cat['id'] ?>" 
                                           onclick="return confirm('⚠️ Are you sure you want to delete category \'<?= addslashes(htmlspecialchars($cat['name'])) ?>\'? Any assigned products will remain in store as unassigned.')" 
                                           style="font-size: 0.8rem; padding: 0.35rem 0.65rem; background: #fee2e2; border: 1px solid #fca5a5; border-radius: 6px; color: #dc2626; text-decoration: none; display: inline-block;">
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

    </main>

    <!-- Modal Dialog for Add / Edit Category -->
    <dialog id="category-modal" style="max-width: 580px; width: 95%; border-radius: 12px; padding: 0; border: 1px solid #e2e8f0; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <article style="margin: 0; padding: 1.5rem; background: #ffffff;">
            <header style="margin-bottom: 1.25rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem; display: flex; justify-content: space-between; align-items: center;">
                <h3 id="modal-title" style="margin: 0; font-size: 1.25rem; font-weight: 800; color: #0f172a;">
                    Add New Category
                </h3>
                <button type="button" onclick="closeModal()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #64748b; padding: 0;">&times;</button>
            </header>

            <form id="category-form" method="POST" action="/admin/category/create" style="margin: 0;">
                <input type="hidden" name="category_id" id="cat-id" value="">

                <div style="margin-bottom: 1rem;">
                    <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">
                        Category Name <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" name="name" id="cat-name" required placeholder="e.g. JEE Main & Advanced" style="font-size: 0.9rem; margin-bottom: 0;" oninput="syncSlug(this.value)">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">
                        URL Slug <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" name="slug" id="cat-slug" required placeholder="e.g. jee-main-advanced" style="font-size: 0.88rem; font-family: monospace; margin-bottom: 0;">
                    <small style="color: #64748b; font-size: 0.75rem;">Used in URLs: /?category=slug</small>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">
                            Icon (FontAwesome Class)
                        </label>
                        <input type="text" name="icon" id="cat-icon" placeholder="fa-solid fa-atom" value="fa-solid fa-folder" style="font-size: 0.88rem; margin-bottom: 0;">
                        <small style="color: #64748b; font-size: 0.72rem;">e.g. fa-solid fa-atom, fa-solid fa-dna, fa-solid fa-trophy</small>
                    </div>
                    <div>
                        <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">
                            Display Order (Sort)
                        </label>
                        <input type="number" name="display_order" id="cat-order" value="0" min="0" style="font-size: 0.88rem; margin-bottom: 0;">
                    </div>
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <label style="font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 0.35rem; display: block;">
                        Description / Overview
                    </label>
                    <textarea name="description" id="cat-desc" rows="3" placeholder="Brief explanation of resources under this category..." style="font-size: 0.88rem; margin-bottom: 0;"></textarea>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="font-weight: 700; font-size: 0.85rem; color: #047857; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="is_active" id="cat-active" value="1" checked style="margin-bottom: 0;">
                        <span>Show this category in live store filters (Active)</span>
                    </label>
                </div>

                <footer style="display: flex; justify-content: flex-end; gap: 0.75rem; border-top: 1px solid #f1f5f9; padding-top: 1rem;">
                    <button type="button" onclick="closeModal()" style="background: #f1f5f9; border: 1px solid #cbd5e1; color: #475569; font-size: 0.85rem; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; margin-bottom: 0;">
                        Cancel
                    </button>
                    <button type="submit" id="btn-save-category" style="background: #4f46e5; border-color: #4f46e5; color: #ffffff; font-size: 0.85rem; font-weight: 700; padding: 0.5rem 1.25rem; border-radius: 6px; cursor: pointer; margin-bottom: 0;">
                        Save Category
                    </button>
                </footer>
            </form>
        </article>
    </dialog>

    <script>
    let isEditMode = false;

    function openCreateModal() {
        isEditMode = false;
        document.getElementById('modal-title').textContent = 'Add New Category';
        document.getElementById('category-form').action = '/admin/category/create';
        document.getElementById('cat-id').value = '';
        document.getElementById('cat-name').value = '';
        document.getElementById('cat-slug').value = '';
        document.getElementById('cat-icon').value = 'fa-solid fa-folder';
        document.getElementById('cat-order').value = '0';
        document.getElementById('cat-desc').value = '';
        document.getElementById('cat-active').checked = true;
        document.getElementById('btn-save-category').textContent = 'Create Category';
        document.getElementById('category-modal').showModal();
    }

    function openEditModal(category) {
        isEditMode = true;
        document.getElementById('modal-title').textContent = 'Edit Category: ' + category.name;
        document.getElementById('category-form').action = '/admin/category/edit/' + category.id;
        document.getElementById('cat-id').value = category.id;
        document.getElementById('cat-name').value = category.name;
        document.getElementById('cat-slug').value = category.slug;
        document.getElementById('cat-icon').value = category.icon || 'fa-solid fa-folder';
        document.getElementById('cat-order').value = category.display_order || 0;
        document.getElementById('cat-desc').value = category.description || '';
        document.getElementById('cat-active').checked = (parseInt(category.is_active, 10) === 1);
        document.getElementById('btn-save-category').textContent = 'Update Category';
        document.getElementById('category-modal').showModal();
    }

    function closeModal() {
        document.getElementById('category-modal').close();
    }

    function syncSlug(val) {
        if (!isEditMode) {
            const slugInput = document.getElementById('cat-slug');
            slugInput.value = val.toLowerCase().trim()
                .replace(/[^a-z0-9-]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }
    }
    </script>

</body>
</html>

