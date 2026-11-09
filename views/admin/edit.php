<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Edit Material — Lockinrank Shop Admin') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <!-- KaTeX CSS for Math Formula Rendering -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/css/app.min.css?v=2.1">
    <style>
        body { background-color: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b; }
        .edit-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 2.25rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .existing-img-card { position: relative; border: 2px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: #ffffff; transition: all 0.2s; }
        .existing-img-card.is-primary { border-color: #4f46e5; box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.25); }
    </style>
</head>
<body style="background-color: #f8fafc;">

    <?php 
    $activeTab = 'products';
    include __DIR__ . '/layout-nav.php'; 
    ?>

    <main class="container" style="max-width: 900px; margin-top: 2rem; margin-bottom: 4rem;">
        
        <div class="edit-card">
            
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 1.25rem;">
                <div>
                    <h2 style="font-size: 1.5rem; font-weight: 900; color: #0f172a; margin-bottom: 0.35rem;">
                        <i class="fa-solid fa-pen-to-square" style="color: #4f46e5;"></i> Edit Study Material
                    </h2>
                    <p style="color: #64748b; font-size: 0.9rem; margin: 0;">
                        Manage resource details, pricing, and all gallery preview images.
                    </p>
                </div>
                <a href="/product/<?= htmlspecialchars($product['slug']) ?>" target="_blank" role="button" class="outline" style="font-size: 0.82rem; padding: 0.4rem 0.85rem; margin-bottom: 0;">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> View on Store
                </a>
            </div>

            <form action="/admin/product/edit/<?= $product['id'] ?>" method="POST" enctype="multipart/form-data" style="margin-bottom: 0;">
                
                <!-- Title -->
                <div style="margin-bottom: 1.5rem;">
                    <label for="title" style="font-weight: 800; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.4rem;">
                        Resource Title <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" id="title" name="title" value="<?= htmlspecialchars($product['title']) ?>" required style="font-size: 0.95rem;">
                </div>

                <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                    <!-- Resource Category -->
                    <div>
                        <label for="category_id" style="font-weight: 800; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.4rem;">
                            Resource Category
                        </label>
                        <select id="category_id" name="category_id" style="font-size: 0.95rem;">
                            <option value="">-- No Category Assigned --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ((int)($product['category_id'] ?? 0) === (int)$cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Target Exam -->
                    <div>
                        <label for="grade" style="font-weight: 800; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.4rem;">
                            Target Exam / Academic Level <span style="color: #ef4444;">*</span>
                        </label>
                        <select id="grade" name="grade" required style="font-size: 0.95rem;">
                            <?php foreach ($targetExams as $exam): ?>
                                <option value="<?= htmlspecialchars($exam) ?>" <?= $product['grade'] === $exam ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($exam) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                    <!-- Curriculum Board -->
                    <div>
                        <label for="board" style="font-weight: 800; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.4rem;">
                            Curriculum Board Standard <span style="color: #ef4444;">*</span>
                        </label>
                        <select id="board" name="board" required style="font-size: 0.95rem;">
                            <?php foreach ($boards as $b): ?>
                                <option value="<?= htmlspecialchars($b) ?>" <?= $product['board'] === $b ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($b) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                <div class="grid" style="grid-template-columns: 1fr 1fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem;">
                    <!-- Subject -->
                    <div>
                        <label for="subject" style="font-weight: 800; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.4rem;">
                            Subject <span style="color: #ef4444;">*</span>
                        </label>
                        <select id="subject" name="subject" required style="font-size: 0.95rem;">
                            <?php foreach ($subjects as $s): ?>
                                <option value="<?= htmlspecialchars($s) ?>" <?= $product['subject'] === $s ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Resource Type -->
                    <div>
                        <label for="resource_type" style="font-weight: 800; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.4rem;">
                            Resource Type
                        </label>
                        <select id="resource_type" name="resource_type" style="font-size: 0.95rem;">
                            <option value="Unit Plan" <?= $product['resource_type'] === 'Unit Plan' ? 'selected' : '' ?>>Unit Plan & Drills</option>
                            <option value="Worksheet" <?= $product['resource_type'] === 'Worksheet' ? 'selected' : '' ?>>DPP / Worksheet</option>
                            <option value="Printable" <?= $product['resource_type'] === 'Printable' ? 'selected' : '' ?>>Formula Cards & Cheatsheet</option>
                            <option value="Lesson Plan" <?= $product['resource_type'] === 'Lesson Plan' ? 'selected' : '' ?>>Mock Test / Complete Pack</option>
                        </select>
                    </div>

                    <!-- File Format -->
                    <div>
                        <label for="file_format" style="font-weight: 800; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.4rem;">
                            File Format
                        </label>
                        <select id="file_format" name="file_format" style="font-size: 0.95rem;">
                            <option value="PDF (Printable)" <?= $product['file_format'] === 'PDF (Printable)' ? 'selected' : '' ?>>PDF (High-Res Printable)</option>
                            <option value="PDF" <?= $product['file_format'] === 'PDF' ? 'selected' : '' ?>>Standard PDF</option>
                            <option value="ZIP (Multi-File)" <?= $product['file_format'] === 'ZIP (Multi-File)' ? 'selected' : '' ?>>ZIP Archive</option>
                        </select>
                    </div>
                </div>

                <div class="grid" style="grid-template-columns: 1fr 1fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem; align-items: start;">
                    <!-- Selling Price -->
                    <div>
                        <label for="price" style="font-weight: 800; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.4rem;">
                            Selling Price in INR (₹) <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="number" id="price" name="price" step="1" min="0" value="<?= (int)$product['price'] ?>" style="font-size: 0.95rem;" oninput="calcDiscount()">
                        <small style="color: #64748b; font-size: 0.78rem;">Customer pays this amount.</small>
                    </div>

                    <!-- MRP / Original List Price -->
                    <div>
                        <label for="mrp" style="font-weight: 800; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.4rem;">
                            M.R.P. / Original Price (₹)
                        </label>
                        <input type="number" id="mrp" name="mrp" step="1" min="0" value="<?= !empty($product['mrp']) ? (int)$product['mrp'] : '' ?>" placeholder="e.g. 499" style="font-size: 0.95rem;" oninput="calcDiscount()">
                        <div id="discount_preview" style="margin-top: 0.25rem; font-size: 0.78rem; font-weight: 800; color: #16a34a;"></div>
                    </div>

                    <!-- Page Count -->
                    <div>
                        <label for="page_count" style="font-weight: 800; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.4rem;">
                            Total Pages <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="number" id="page_count" name="page_count" min="1" value="<?= (int)$product['page_count'] ?>" style="font-size: 0.95rem;">
                        <small style="color: #64748b; font-size: 0.78rem;">Approx. total pages.</small>
                    </div>
                </div>

                <!-- Toggles -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.25rem; margin-bottom: 1.5rem;">
                    <div style="margin-bottom: 0.75rem;">
                        <label style="display: flex; align-items: center; gap: 0.6rem; cursor: pointer; margin-bottom: 0;">
                            <input type="checkbox" id="is_free" name="is_free" value="1" <?= !empty($product['is_free']) ? 'checked' : '' ?> onchange="if(this.checked) document.getElementById('price').value = 0; calcDiscount();">
                            <span style="font-weight: 700; color: #16a34a; font-size: 0.88rem;">
                                <i class="fa-solid fa-gift"></i> Mark as 100% Freebie (₹0)
                            </span>
                        </label>
                    </div>
                    <div>
                        <label style="display: flex; align-items: center; gap: 0.6rem; cursor: pointer; margin-bottom: 0;">
                            <input type="checkbox" id="is_ink_friendly" name="is_ink_friendly" value="1" <?= !empty($product['is_ink_friendly']) ? 'checked' : '' ?>>
                            <span style="font-weight: 700; color: #0f172a; font-size: 0.88rem;">
                                <i class="fa-solid fa-print" style="color: #0284c7;"></i> Printer-Friendly Eco Layout
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Standards & Syllabus reference -->
                <div style="margin-bottom: 1.5rem;">
                    <label for="standards" style="font-weight: 800; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.4rem;">
                        Curriculum Reference / Chapter Alignment
                    </label>
                    <input type="text" id="standards" name="standards" value="<?= htmlspecialchars($product['standards'] ?? '') ?>" placeholder="e.g. NTA JEE Main Syllabus Section 2" style="font-size: 0.95rem;">
                </div>

                <!-- Clickable Product Tags -->
                <div style="margin-bottom: 1.5rem;">
                    <label for="tags" style="font-weight: 800; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.4rem;">
                        <i class="fa-solid fa-tags" style="color: #4f46e5;"></i> Product Tags (Clickable in Store)
                    </label>
                    <input type="text" id="tags" name="tags" value="<?= htmlspecialchars($product['tags'] ?? '') ?>" placeholder="e.g. Mechanics, Rotation, Angular Momentum, JEE Advanced, High Yield" style="font-size: 0.95rem; margin-bottom: 0.25rem;">
                    <small style="color: #64748b; font-size: 0.78rem;">Enter comma-separated keywords. These will appear as clickable tag chips on the product page and allow students to filter related topics instantly.</small>
                </div>

                <!-- Digital Resource Downloads (Full Material & Sample File) -->
                <div style="margin-bottom: 2rem; background: #ffffff; border: 2px solid #e0e7ff; border-radius: 10px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(79, 70, 229, 0.08);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <h4 style="margin: 0; font-size: 1.05rem; font-weight: 900; color: #1e1b4b; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fa-solid fa-file-arrow-up" style="color: #4f46e5;"></i> Downloadable Files Fulfillment (ZIP, PDF, MS Word)
                        </h4>
                        <span style="background: #e0e7ff; color: #3730a3; font-size: 0.72rem; font-weight: 800; padding: 0.15rem 0.5rem; border-radius: 4px;">
                            PDF &bull; ZIP &bull; DOC &bull; DOCX
                        </span>
                    </div>
                    <p style="font-size: 0.83rem; color: #64748b; margin-bottom: 1.25rem;">
                        Manage the actual material delivered to paying buyers upon checkout, and the free sample downloaded on the product detail page.
                    </p>

                    <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <!-- Full Paid Material -->
                        <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 1.25rem;">
                            <label for="material_file" style="font-weight: 800; font-size: 0.88rem; color: #0f172a; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.4rem;">
                                <i class="fa-solid fa-lock" style="color: #4f46e5;"></i> Full Paid Material File
                            </label>
                            
                            <?php if (!empty($product['material_file_path'])): ?>
                                <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; padding: 0.5rem 0.75rem; margin-bottom: 0.75rem; font-size: 0.8rem; color: #065f46;">
                                    <div style="font-weight: 700; display: flex; align-items: center; gap: 0.4rem;">
                                        <i class="fa-solid fa-circle-check" style="color: #10b981;"></i> Current Material Active
                                    </div>
                                    <div style="font-size: 0.75rem; color: #047857; margin-top: 0.2rem; word-break: break-all;">
                                        <?= htmlspecialchars(basename($product['material_file_path'])) ?>
                                    </div>
                                    <label style="display: flex; align-items: center; gap: 0.35rem; margin-top: 0.4rem; margin-bottom: 0; font-size: 0.75rem; color: #dc2626; cursor: pointer;">
                                        <input type="checkbox" name="remove_material_file" value="1" style="width: 14px; height: 14px; margin: 0;">
                                        <span>Remove this file</span>
                                    </label>
                                </div>
                            <?php else: ?>
                                <small style="color: #64748b; font-size: 0.75rem; display: block; margin-bottom: 0.6rem;">
                                    Delivered to student immediately after successful payment.
                                </small>
                            <?php endif; ?>

                            <label style="font-size: 0.78rem; font-weight: 700; color: #475569; margin-bottom: 0.25rem;">
                                <?= !empty($product['material_file_path']) ? 'Upload New / Replacement Material:' : 'Upload Material File:' ?>
                            </label>
                            <input type="file" id="material_file" name="material_file" accept=".pdf,.zip,.doc,.docx,application/pdf,application/zip,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" style="font-size: 0.85rem; margin-bottom: 0.25rem;">
                            <div style="font-size: 0.7rem; color: #64748b;">
                                Accepted: <strong>.pdf, .zip, .doc, .docx</strong> (Up to 100MB)
                            </div>
                        </div>

                        <!-- Free Sample File -->
                        <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 1.25rem;">
                            <label for="sample_file" style="font-weight: 800; font-size: 0.88rem; color: #0f172a; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.4rem;">
                                <i class="fa-solid fa-file-arrow-down" style="color: #10b981;"></i> Free Sample Material File
                            </label>

                            <?php if (!empty($product['sample_file_path'])): ?>
                                <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 0.5rem 0.75rem; margin-bottom: 0.75rem; font-size: 0.8rem; color: #1e40af;">
                                    <div style="font-weight: 700; display: flex; align-items: center; gap: 0.4rem;">
                                        <i class="fa-solid fa-circle-check" style="color: #3b82f6;"></i> Current Sample Active
                                    </div>
                                    <div style="font-size: 0.75rem; color: #1d4ed8; margin-top: 0.2rem; word-break: break-all;">
                                        <?= htmlspecialchars(basename($product['sample_file_path'])) ?>
                                    </div>
                                    <label style="display: flex; align-items: center; gap: 0.35rem; margin-top: 0.4rem; margin-bottom: 0; font-size: 0.75rem; color: #dc2626; cursor: pointer;">
                                        <input type="checkbox" name="remove_sample_file" value="1" style="width: 14px; height: 14px; margin: 0;">
                                        <span>Remove this sample</span>
                                    </label>
                                </div>
                            <?php else: ?>
                                <small style="color: #64748b; font-size: 0.75rem; display: block; margin-bottom: 0.6rem;">
                                    Downloaded on the product detail page via "Download Sample".
                                </small>
                            <?php endif; ?>

                            <label style="font-size: 0.78rem; font-weight: 700; color: #475569; margin-bottom: 0.25rem;">
                                <?= !empty($product['sample_file_path']) ? 'Upload New / Replacement Sample:' : 'Upload Sample File:' ?>
                            </label>
                            <input type="file" id="sample_file" name="sample_file" accept=".pdf,.zip,.doc,.docx,application/pdf,application/zip,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" style="font-size: 0.85rem; margin-bottom: 0.25rem;">
                            <div style="font-size: 0.7rem; color: #64748b;">
                                Accepted: <strong>.pdf, .zip, .doc, .docx</strong> (Up to 50MB)
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Visuals & Multi-Image Manager -->
                <div style="margin-bottom: 2rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <label style="font-weight: 800; font-size: 0.95rem; color: #0f172a; margin-bottom: 0;">
                            <i class="fa-solid fa-images" style="color: #4f46e5;"></i> Product Images & Gallery Management
                        </label>
                        <span style="font-size: 0.75rem; background: #e0e7ff; color: #3730a3; padding: 0.15rem 0.5rem; border-radius: 4px; font-weight: 700;">
                            <?= count($previewImages) ?> Existing Images
                        </span>
                    </div>
                    <p style="font-size: 0.82rem; color: #64748b; margin-bottom: 1rem;">
                        Select which existing images to keep, choose the primary cover image, or upload/add additional preview pages below.
                    </p>

                    <!-- Existing Images Grid -->
                    <?php if (!empty($previewImages)): ?>
                        <div style="font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.4rem;">Current Images:</div>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 0.75rem; margin-bottom: 1.25rem;">
                            <?php foreach ($previewImages as $idx => $img): ?>
                                <?php $isPrimary = ($img === $product['thumbnail_url']) || ($idx === 0 && empty($product['thumbnail_url'])); ?>
                                <div class="existing-img-card <?= $isPrimary ? 'is-primary' : '' ?>" style="padding: 0.5rem; text-align: center;">
                                    <div style="width: 100%; height: 85px; border-radius: 6px; overflow: hidden; background: #0f172a; margin-bottom: 0.4rem;">
                                        <img src="<?= htmlspecialchars($img) ?>" alt="Img <?= $idx + 1 ?>" style="width: 100%; height: 100%; object-fit: contain;">
                                    </div>
                                    <div style="display: flex; flex-direction: column; gap: 0.25rem; font-size: 0.72rem; text-align: left;">
                                        <label style="display: flex; align-items: center; gap: 0.35rem; margin-bottom: 0; cursor: pointer;">
                                            <input type="checkbox" name="kept_images[]" value="<?= htmlspecialchars($img) ?>" checked style="width: 14px; height: 14px; margin: 0;">
                                            <span>Keep image</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 0.35rem; margin-bottom: 0; cursor: pointer; color: <?= $isPrimary ? '#4f46e5' : '#64748b' ?>; font-weight: <?= $isPrimary ? '800' : '500' ?>;">
                                            <input type="radio" name="primary_image_index" value="<?= $idx ?>" <?= $isPrimary ? 'checked' : '' ?> style="width: 14px; height: 14px; margin: 0;">
                                            <span><?= $isPrimary ? 'Primary Cover' : 'Set as Cover' ?></span>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Upload Additional Images -->
                    <div style="font-size: 0.8rem; font-weight: 700; color: #334155; margin-bottom: 0.4rem;">Upload Additional Images:</div>
                    <div class="image-upload-dropzone" id="dropzone" onclick="document.getElementById('imagesInput').click()">
                        <input type="file" id="imagesInput" name="images[]" multiple accept="image/png,image/jpeg,image/webp,image/gif,image/svg+xml" style="display: none;" onchange="previewSelectedFiles(this)">
                        <i class="fa-solid fa-cloud-arrow-up" style="font-size: 2rem; color: #6366f1; margin-bottom: 0.5rem;"></i>
                        <div style="font-weight: 700; color: #1e293b; font-size: 0.95rem;">
                            Click to Upload More Images (Multiple Selection)
                        </div>
                        <small style="color: #64748b; font-size: 0.78rem; display: block;">
                            <i class="fa-solid fa-bolt" style="color: #10b981;"></i> Auto-compressed &amp; optimized on upload. PNG, JPG, WEBP, GIF, SVG. Select multiple files at once.
                        </small>
                        <div style="font-size: 0.78rem; color: #4338ca; margin-top: 0.4rem; font-weight: 700;">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> Tip: Any file named with <code>cover</code> (e.g. <code>cover.jpg</code> or <code>notes_cover.png</code>) is automatically detected and set as the 1st primary cover!
                        </div>
                    </div>

                    <!-- New Files Preview Grid with Compression Stats -->
                    <div id="filePreviewContainer" style="display: none; margin-top: 1rem;"></div>

                    <!-- Additional URLs -->
                    <div style="margin-top: 1.25rem;">
                        <label for="preview_urls" style="font-weight: 700; font-size: 0.85rem; color: #475569; margin-bottom: 0.35rem;">
                            Or Paste Additional Image URLs (One URL per line):
                        </label>
                        <textarea id="preview_urls" name="preview_urls" rows="2" placeholder="https://images.unsplash.com/...&#10;https://images.unsplash.com/..." style="font-size: 0.88rem; font-family: monospace;"></textarea>
                    </div>
                </div>

                <!-- Description with Markdown & KaTeX Toolbar -->
                <div style="margin-bottom: 2rem;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 0.4rem; flex-wrap: wrap; gap: 0.5rem;">
                        <label for="description" style="font-weight: 800; font-size: 0.9rem; color: #1e293b; margin-bottom: 0;">
                            Description & Highlights <span style="color: #ef4444;">*</span>
                            <span style="font-size: 0.75rem; font-weight: 600; color: #4338ca; background: #e0e7ff; padding: 0.15rem 0.5rem; border-radius: 4px; margin-left: 0.4rem;">
                                <i class="fa-brands fa-markdown"></i> Markdown
                            </span>
                            <span style="font-size: 0.75rem; font-weight: 600; color: #047857; background: #d1fae5; padding: 0.15rem 0.5rem; border-radius: 4px; margin-left: 0.3rem;">
                                <i class="fa-solid fa-square-root-variable"></i> KaTeX Math
                            </span>
                        </label>
                        <small style="color: #64748b; font-size: 0.78rem;">Supports **bold**, *italics*, tables, lists, links &amp; KaTeX formulas ($x$ or $$)</small>
                    </div>

                    <!-- Markdown Toolbar -->
                    <div style="background: #f1f5f9; border: 1px solid #cbd5e1; border-bottom: none; border-radius: 6px 6px 0 0; padding: 0.4rem 0.6rem; display: flex; flex-wrap: wrap; gap: 0.3rem; align-items: center;">
                        <button type="button" onclick="insertMd('**', '**', 'bold text')" title="Bold" style="background: #fff; border: 1px solid #cbd5e1; color: #334155; padding: 0.25rem 0.55rem; font-size: 0.82rem; border-radius: 4px; cursor: pointer; margin-bottom: 0; font-weight: 700;">
                            <strong>B</strong>
                        </button>
                        <button type="button" onclick="insertMd('*', '*', 'italic text')" title="Italic" style="background: #fff; border: 1px solid #cbd5e1; color: #334155; padding: 0.25rem 0.55rem; font-size: 0.82rem; border-radius: 4px; cursor: pointer; margin-bottom: 0; font-style: italic;">
                            <em>I</em>
                        </button>
                        <span style="color: #cbd5e1; margin: 0 0.1rem;">|</span>
                        <button type="button" onclick="insertMd('## ', '', 'Heading 2')" title="Heading 2" style="background: #fff; border: 1px solid #cbd5e1; color: #334155; padding: 0.25rem 0.55rem; font-size: 0.82rem; border-radius: 4px; cursor: pointer; margin-bottom: 0; font-weight: 700;">
                            H2
                        </button>
                        <button type="button" onclick="insertMd('### ', '', 'Heading 3')" title="Heading 3" style="background: #fff; border: 1px solid #cbd5e1; color: #334155; padding: 0.25rem 0.55rem; font-size: 0.82rem; border-radius: 4px; cursor: pointer; margin-bottom: 0; font-weight: 700;">
                            H3
                        </button>
                        <span style="color: #cbd5e1; margin: 0 0.1rem;">|</span>
                        <button type="button" onclick="insertMd('- ', '', 'Bullet item')" title="Bullet List" style="background: #fff; border: 1px solid #cbd5e1; color: #334155; padding: 0.25rem 0.55rem; font-size: 0.82rem; border-radius: 4px; cursor: pointer; margin-bottom: 0;">
                            <i class="fa-solid fa-list-ul"></i>
                        </button>
                        <button type="button" onclick="insertMd('1. ', '', 'Numbered item')" title="Numbered List" style="background: #fff; border: 1px solid #cbd5e1; color: #334155; padding: 0.25rem 0.55rem; font-size: 0.82rem; border-radius: 4px; cursor: pointer; margin-bottom: 0;">
                            <i class="fa-solid fa-list-ol"></i>
                        </button>
                        <button type="button" onclick="insertMd('> ', '', 'Quoted note')" title="Blockquote" style="background: #fff; border: 1px solid #cbd5e1; color: #334155; padding: 0.25rem 0.55rem; font-size: 0.82rem; border-radius: 4px; cursor: pointer; margin-bottom: 0;">
                            <i class="fa-solid fa-quote-left"></i>
                        </button>
                        <button type="button" onclick="insertMd('[', '](https://example.com)', 'Link text')" title="Insert Link" style="background: #fff; border: 1px solid #cbd5e1; color: #334155; padding: 0.25rem 0.55rem; font-size: 0.82rem; border-radius: 4px; cursor: pointer; margin-bottom: 0;">
                            <i class="fa-solid fa-link"></i>
                        </button>
                        <button type="button" onclick="insertMd('`', '`', 'code')" title="Inline Code" style="background: #fff; border: 1px solid #cbd5e1; color: #334155; padding: 0.25rem 0.55rem; font-size: 0.82rem; border-radius: 4px; cursor: pointer; margin-bottom: 0; font-family: monospace;">
                            &lt;/&gt;
                        </button>
                        <button type="button" onclick="insertTableTemplate()" title="Insert Table" style="background: #fff; border: 1px solid #cbd5e1; color: #334155; padding: 0.25rem 0.55rem; font-size: 0.82rem; border-radius: 4px; cursor: pointer; margin-bottom: 0;">
                            <i class="fa-solid fa-table"></i> Table
                        </button>
                        <span style="color: #cbd5e1; margin: 0 0.1rem;">|</span>
                        <button type="button" onclick="insertMd('$', '$', 'E = mc^2')" title="Inline Math ($formula$)" style="background: #fff; border: 1px solid #c7d2fe; color: #4338ca; padding: 0.25rem 0.55rem; font-size: 0.82rem; border-radius: 4px; cursor: pointer; margin-bottom: 0; font-weight: 700; font-family: serif;">
                            <i class="fa-solid fa-square-root-variable"></i> $x$ Math
                        </button>
                        <button type="button" onclick="insertMd('$$\n', '\n$$', '\\int_0^\\infty f(x) dx')" title="Display Math ($$formula$$)" style="background: #fff; border: 1px solid #a7f3d0; color: #047857; padding: 0.25rem 0.55rem; font-size: 0.82rem; border-radius: 4px; cursor: pointer; margin-bottom: 0; font-weight: 700; font-family: serif;">
                            $$ Display
                        </button>
                        <button type="button" onclick="toggleMdPreview()" id="previewToggleBtn" title="Toggle Rendered Preview" style="background: #eef2ff; border: 1px solid #c7d2fe; color: #4338ca; padding: 0.25rem 0.65rem; font-size: 0.82rem; border-radius: 4px; cursor: pointer; margin-left: auto; font-weight: 700; margin-bottom: 0;">
                            <i class="fa-solid fa-eye"></i> Live Preview
                        </button>
                    </div>
                    <textarea id="description" name="description" rows="7" required style="font-size: 0.95rem; border-top-left-radius: 0; border-top-right-radius: 0; margin-bottom: 0;"><?= htmlspecialchars($product['description']) ?></textarea>

                    <!-- Live Markdown & KaTeX Preview Box -->
                    <div id="mdPreviewContainer" style="display: none; background: #ffffff; border: 1.5px solid #c7d2fe; border-radius: 0 0 8px 8px; border-top: none; padding: 1.25rem; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02); margin-top: -1px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.4rem;">
                            <span style="font-size: 0.75rem; font-weight: 800; color: #4338ca; text-transform: uppercase; letter-spacing: 0.05em;">
                                <i class="fa-solid fa-sparkles"></i> Live Storefront Preview (Markdown + KaTeX)
                            </span>
                            <span style="font-size: 0.72rem; color: #64748b;">Updates on click</span>
                        </div>
                        <div id="mdPreviewContent" class="markdown-content" style="min-height: 50px;">
                            <em style="color: #94a3b8;">Click "Live Preview" to preview formatted description...</em>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <button type="submit" style="padding: 0.85rem 2rem; font-size: 1rem; font-weight: 800; background-color: #4f46e5; border-color: #4f46e5; color: #ffffff; border-radius: 8px; cursor: pointer;">
                        <i class="fa-solid fa-floppy-disk"></i> Save & Update Material
                    </button>
                    <a href="/admin" role="button" class="secondary" style="margin-bottom: 0; text-decoration: none; border-radius: 8px;">
                        Cancel
                    </a>
                </div>

            </form>

        </div>

    </main>

    <script src="/js/image-compressor.min.js?v=2.1"></script>
    <script>
        function insertMd(prefix, suffix, defaultText) {
            const textarea = document.getElementById('description');
            if (!textarea) return;
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = textarea.value;
            const selected = text.substring(start, end) || defaultText;
            const replacement = prefix + selected + suffix;
            textarea.value = text.substring(0, start) + replacement + text.substring(end);
            textarea.focus();
            textarea.setSelectionRange(start + prefix.length, start + prefix.length + selected.length);
        }

        function insertTableTemplate() {
            const table = "\n| Feature | Specification |\n| :--- | :--- |\n| Pages | 50 Pages |\n| Difficulty | Moderate to High |\n| Answer Keys | Full Step-by-Step Solutions |\n";
            const textarea = document.getElementById('description');
            if (!textarea) return;
            const start = textarea.selectionStart;
            const text = textarea.value;
            textarea.value = text.substring(0, start) + table + text.substring(start);
            textarea.focus();
        }

        function calcDiscount() {
            const price = parseFloat(document.getElementById('price').value) || 0;
            const mrp = parseFloat(document.getElementById('mrp').value) || 0;
            const preview = document.getElementById('discount_preview');
            if (!preview) return;

            if (mrp > price && price > 0) {
                const diff = mrp - price;
                const pct = Math.round((diff / mrp) * 100);
                preview.innerHTML = `<span style="background: #dcfce7; color: #15803d; padding: 0.15rem 0.5rem; border-radius: 4px; display: inline-block;"><i class="fa-solid fa-tag"></i> ${pct}% OFF (Save ₹${diff.toLocaleString()})</span>`;
            } else if (price === 0 && mrp > 0) {
                preview.innerHTML = `<span style="background: #dcfce7; color: #15803d; padding: 0.15rem 0.5rem; border-radius: 4px; display: inline-block;"><i class="fa-solid fa-gift"></i> 100% FREE (Worth ₹${mrp.toLocaleString()})</span>`;
            } else {
                preview.innerHTML = `<span style="color: #94a3b8; font-size: 0.75rem;">Enter MRP higher than price to show discount</span>`;
            }
        }
        calcDiscount();

        function previewSelectedFiles(input) {
            processAndCompressInput(input, 'filePreviewContainer', 'New');
        }

        const dropzone = document.getElementById('dropzone');
        if (dropzone) {
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.add('dragover');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.remove('dragover');
                }, false);
            });

            dropzone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const files = dt.files;
                const input = document.getElementById('imagesInput');
                input.files = files;
                previewSelectedFiles(input);
            }, false);
        }

        let isPreviewOpen = false;
        async function toggleMdPreview() {
            const container = document.getElementById('mdPreviewContainer');
            const content = document.getElementById('mdPreviewContent');
            const btn = document.getElementById('previewToggleBtn');
            const textarea = document.getElementById('description');

            if (!container || !content) return;

            if (isPreviewOpen) {
                container.style.display = 'none';
                isPreviewOpen = false;
                if (btn) btn.innerHTML = '<i class="fa-solid fa-eye"></i> Live Preview';
                return;
            }

            container.style.display = 'block';
            isPreviewOpen = true;
            if (btn) btn.innerHTML = '<i class="fa-solid fa-eye-slash"></i> Close Preview';
            content.innerHTML = '<span style="color: #64748b;"><i class="fa-solid fa-spinner fa-spin"></i> Rendering markdown & math...</span>';

            try {
                const resp = await fetch('/admin/preview-markdown', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ markdown: textarea.value })
                });
                const data = await resp.json();
                content.innerHTML = data.html || '<em style="color:#94a3b8;">(Empty description)</em>';

                if (typeof renderMathInElement === 'function') {
                    renderMathInElement(content, {
                        delimiters: [
                            { left: '$$', right: '$$', display: true },
                            { left: '\\[', right: '\\]', display: true },
                            { left: '\\(', right: '\\)', display: false },
                            { left: '$', right: '$', display: false }
                        ],
                        ignoredTags: ['script', 'noscript', 'style', 'textarea', 'pre', 'code', 'option'],
                        throwOnError: false
                    });
                }
            } catch (err) {
                content.innerHTML = '<span style="color:#ef4444;">Preview failed to load.</span>';
            }
        }
    </script>

    <!-- KaTeX Math Engine for Admin Preview -->
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.js" crossorigin="anonymous"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/contrib/auto-render.min.js" crossorigin="anonymous"></script>
</body>
</html>
