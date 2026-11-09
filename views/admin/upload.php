<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Publish Material — Lockinrank Shop Admin') ?></title>
    <!-- Pico CSS v2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <!-- KaTeX CSS for Math Formula Rendering -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="/css/app.min.css?v=2.5">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body style="background-color: #f8fafc;">

    <?php 
    $activeTab = 'upload';
    include __DIR__ . '/layout-nav.php'; 
    ?>

    <main class="container" style="max-width: 900px; padding: 2.5rem 1.5rem;">
        
        <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 2.5rem 2rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
            
            <div style="margin-bottom: 2rem;">
                <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: #e0e7ff; color: #4338ca; padding: 0.3rem 0.85rem; border-radius: 20px; font-size: 0.78rem; font-weight: 800; margin-bottom: 0.75rem;">
                    <i class="fa-solid fa-cloud-arrow-up"></i> ADMIN PUBLISHER
                </div>
                <h2 style="font-size: 1.7rem; font-weight: 900; margin: 0 0 0.5rem 0; color: #0f172a;">
                    Publish New Study Resource
                </h2>
                <p style="color: #64748b; margin: 0; font-size: 0.95rem;">
                    Upload question banks, formula cheatsheets, DPPs, or AP Physics test packs to <strong>shop.lockinrank.com</strong>.
                </p>
            </div>

            <form action="/admin/upload" method="POST" enctype="multipart/form-data" style="margin-bottom: 0;">
                
                <!-- JSON Auto-fill Card -->
                <div style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); border: 1.5px dashed #10b981; border-radius: 10px; padding: 1.1rem 1.25rem; margin-bottom: 2rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="width: 36px; height: 36px; border-radius: 8px; background: #10b981; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                                <i class="fa-solid fa-file-code"></i>
                            </div>
                            <div>
                                <div style="font-weight: 800; font-size: 0.92rem; color: #065f46;">
                                    Auto-Fill Form from JSON File
                                </div>
                                <div style="font-size: 0.78rem; color: #047857;">
                                    Select or drop a <code>.json</code> file to populate title, price, tags, Description &amp; Key Highlights instantly.
                                </div>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <input type="file" id="jsonAutofillInput" accept=".json,application/json" style="display: none;" onchange="handleJsonAutofill(this)">
                            <button type="button" onclick="document.getElementById('jsonAutofillInput').click()" style="background: #10b981; border-color: #059669; color: #ffffff; padding: 0.45rem 1rem; font-size: 0.85rem; font-weight: 700; border-radius: 6px; cursor: pointer; margin-bottom: 0; display: inline-flex; align-items: center; gap: 0.4rem;">
                                <i class="fa-solid fa-folder-open"></i> Choose JSON File
                            </button>
                            <a href="/admin/products/export-sample-json" download="sample-product.json" style="font-size: 0.8rem; color: #065f46; text-decoration: underline; font-weight: 600; padding: 0.35rem 0.5rem;">
                                Sample JSON
                            </a>
                        </div>
                    </div>
                    <div id="jsonAutofillStatus" style="display: none; margin-top: 0.6rem; font-size: 0.8rem; padding: 0.4rem 0.75rem; border-radius: 6px; background: #ffffff; border: 1px solid #a7f3d0; color: #065f46; font-weight: 600;"></div>
                </div>

                <!-- Title -->
                <div style="margin-bottom: 1.5rem;">
                    <label for="title" style="font-weight: 800; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.4rem;">
                        Resource Title <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" id="title" name="title" placeholder="e.g. JEE Advanced 2026 Physics: Rotational Mechanics 100 Hard Drills" required style="font-size: 0.95rem;">
                </div>

                <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                    <!-- Resource Category -->
                    <div>
                        <label for="category_id" style="font-weight: 800; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.4rem;">
                            Resource Category
                        </label>
                        <select id="category_id" name="category_id" style="font-size: 0.9rem;">
                            <option value="">-- Select Category (Optional) --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Target Exam -->
                    <div>
                        <label for="grade" style="font-weight: 800; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.4rem;">
                            Target Exam / Course <span style="color: #ef4444;">*</span>
                        </label>
                        <select id="grade" name="grade" required style="font-size: 0.9rem;">
                            <?php foreach ($targetExams as $ex): ?>
                                <?php if ($ex !== 'All Exams'): ?>
                                    <option value="<?= htmlspecialchars($ex) ?>"><?= htmlspecialchars($ex) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                    <!-- Examination Board -->
                    <div>
                        <label for="board" style="font-weight: 800; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.4rem;">
                            Board / Authority Pattern <span style="color: #ef4444;">*</span>
                        </label>
                        <select id="board" name="board" required style="font-size: 0.9rem;">
                            <?php foreach ($boards as $b): ?>
                                <?php if ($b !== 'All Boards'): ?>
                                    <option value="<?= htmlspecialchars($b) ?>"><?= htmlspecialchars($b) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                    <!-- Subject -->
                    <div>
                        <label for="subject" style="font-weight: 800; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.4rem;">
                            Subject Area <span style="color: #ef4444;">*</span>
                        </label>
                        <select id="subject" name="subject" required style="font-size: 0.9rem;">
                            <?php foreach ($subjects as $s): ?>
                                <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Resource Type -->
                    <div>
                        <label for="resource_type" style="font-weight: 800; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.4rem;">
                            Format Type <span style="color: #ef4444;">*</span>
                        </label>
                        <select id="resource_type" name="resource_type" required style="font-size: 0.9rem;">
                            <option value="Unit Plan">Full Unit Pack / Master Bundle</option>
                            <option value="Worksheet" selected>Daily Practice Paper (DPP)</option>
                            <option value="Printable">Quick Revision Cheatsheet / Formula Card</option>
                        </select>
                    </div>
                </div>

                <div class="grid" style="grid-template-columns: 1fr 1fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem; align-items: start;">
                    <!-- Selling Price -->
                    <div>
                        <label for="price" style="font-weight: 800; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.4rem;">
                            Selling Price in INR (₹) <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="number" id="price" name="price" value="149" step="1" min="0" required style="font-size: 0.95rem;" oninput="calcDiscount()">
                        <small style="color: #64748b; font-size: 0.78rem;">Customer pays this amount.</small>
                    </div>

                    <!-- MRP / Original List Price -->
                    <div>
                        <label for="mrp" style="font-weight: 800; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.4rem;">
                            M.R.P. / Original Price (₹)
                        </label>
                        <input type="number" id="mrp" name="mrp" value="399" step="1" min="0" placeholder="e.g. 399" style="font-size: 0.95rem;" oninput="calcDiscount()">
                        <div id="discount_preview" style="margin-top: 0.25rem; font-size: 0.78rem; font-weight: 800; color: #16a34a;">
                            <span style="background: #dcfce7; padding: 0.15rem 0.5rem; border-radius: 4px; display: inline-block;">
                                63% OFF (Save ₹250)
                            </span>
                        </div>
                    </div>

                    <!-- Page Count -->
                    <div>
                        <label for="page_count" style="font-weight: 800; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.4rem;">
                            Estimated Page Count <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="number" id="page_count" name="page_count" value="45" min="1" required style="font-size: 0.95rem;">
                        <small style="color: #64748b; font-size: 0.78rem;">Approx. total pages.</small>
                    </div>
                </div>

                <!-- Toggles -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.25rem; margin-bottom: 1.5rem;">
                    <div style="margin-bottom: 0.75rem;">
                        <label style="display: flex; align-items: center; gap: 0.6rem; cursor: pointer; margin-bottom: 0;">
                            <input type="checkbox" id="is_free" name="is_free" value="1" onchange="document.getElementById('price').value = this.checked ? 0 : 149; calcDiscount();">
                            <span style="font-weight: 700; color: #0f172a; font-size: 0.9rem;">
                                <i class="fa-solid fa-gift" style="color: #10b981;"></i> 100% Freebie (Bypasses payment gateway, enables instant 1-click download)
                            </span>
                        </label>
                    </div>

                    <div>
                        <label style="display: flex; align-items: center; gap: 0.6rem; cursor: pointer; margin-bottom: 0;">
                            <input type="checkbox" id="is_ink_friendly" name="is_ink_friendly" value="1" checked>
                            <span style="font-weight: 700; color: #0f172a; font-size: 0.9rem;">
                                <i class="fa-solid fa-print" style="color: #0284c7;"></i> Printer-Friendly / B&W Eco-Ink Layout
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Standards & Syllabus reference -->
                <div style="margin-bottom: 1.5rem;">
                    <label for="standards" style="font-weight: 800; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.4rem;">
                        Curriculum Reference / Chapter Alignment
                    </label>
                    <input type="text" id="standards" name="standards" placeholder="e.g. NTA JEE Main Syllabus Section 2 / College Board AP Physics 1 Unit 3" style="font-size: 0.95rem;">
                </div>

                <!-- Clickable Product Tags -->
                <div style="margin-bottom: 1.5rem;">
                    <label for="tags" style="font-weight: 800; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.4rem;">
                        <i class="fa-solid fa-tags" style="color: #4f46e5;"></i> Product Tags (Clickable in Store)
                    </label>
                    <input type="text" id="tags" name="tags" placeholder="e.g. Mechanics, Rotation, Angular Momentum, JEE Advanced, High Yield" style="font-size: 0.95rem; margin-bottom: 0.25rem;">
                    <small style="color: #64748b; font-size: 0.78rem;">Enter comma-separated keywords. These will appear as clickable tag chips on the product page and allow students to filter related topics instantly.</small>
                </div>

                <!-- Digital Resource Downloads (Full Material & Sample File) -->
                <div style="margin-bottom: 2rem; background: #ffffff; border: 2px solid #e0e7ff; border-radius: 10px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(79, 70, 229, 0.08);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <h4 style="margin: 0; font-size: 1.05rem; font-weight: 900; color: #1e1b4b; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fa-solid fa-file-arrow-up" style="color: #4f46e5;"></i> Digital Files for Fulfillment (ZIP, PDF, MS Word)
                        </h4>
                        <span style="background: #e0e7ff; color: #3730a3; font-size: 0.72rem; font-weight: 800; padding: 0.15rem 0.5rem; border-radius: 4px;">
                            PDF &bull; ZIP &bull; DOC &bull; DOCX
                        </span>
                    </div>
                    <p style="font-size: 0.83rem; color: #64748b; margin-bottom: 1.25rem;">
                        Upload the actual files that students download. The <strong>Full Material</strong> is unlocked upon checkout, while the <strong>Sample</strong> is available for free on the product page.
                    </p>

                    <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <!-- Full Paid Material -->
                        <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 1.25rem;">
                            <label for="material_file" style="font-weight: 800; font-size: 0.88rem; color: #0f172a; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.4rem;">
                                <i class="fa-solid fa-lock" style="color: #4f46e5;"></i> Full Paid Material File
                            </label>
                            <small style="color: #64748b; font-size: 0.75rem; display: block; margin-bottom: 0.6rem;">
                                Delivered to student immediately after successful payment.
                            </small>
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
                            <small style="color: #64748b; font-size: 0.75rem; display: block; margin-bottom: 0.6rem;">
                                Downloaded on the product detail page via "Download Sample".
                            </small>
                            <input type="file" id="sample_file" name="sample_file" accept=".pdf,.zip,.doc,.docx,application/pdf,application/zip,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" style="font-size: 0.85rem; margin-bottom: 0.25rem;">
                            <div style="font-size: 0.7rem; color: #64748b;">
                                Accepted: <strong>.pdf, .zip, .doc, .docx</strong> (Up to 50MB)
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Visuals & Multi-Image Upload -->
                <div style="margin-bottom: 2rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <label style="font-weight: 800; font-size: 0.95rem; color: #0f172a; margin-bottom: 0;">
                            <i class="fa-solid fa-images" style="color: #4f46e5;"></i> Product Images & Sample Preview Pages
                        </label>
                        <span style="font-size: 0.75rem; background: #e0e7ff; color: #3730a3; padding: 0.15rem 0.5rem; border-radius: 4px; font-weight: 700;">
                            Multiple Images Supported
                        </span>
                    </div>
                    <p style="font-size: 0.82rem; color: #64748b; margin-bottom: 1rem;">
                        Upload multiple sample problem sheets, formula summaries, or diagram previews. All images will be showcased in the interactive gallery on the product page.
                    </p>

                    <!-- Drag & Drop / File Input -->
                    <div class="image-upload-dropzone" id="dropzone" onclick="document.getElementById('imagesInput').click()">
                        <input type="file" id="imagesInput" name="images[]" multiple accept="image/png,image/jpeg,image/webp,image/gif,image/svg+xml" style="display: none;" onchange="previewSelectedFiles(this)">
                        <i class="fa-solid fa-cloud-arrow-up" style="font-size: 2rem; color: #6366f1; margin-bottom: 0.5rem;"></i>
                        <div style="font-weight: 700; color: #1e293b; font-size: 0.95rem;">
                            Click to Browse or Drag & Drop Multiple Images
                        </div>
                        <small style="color: #64748b; font-size: 0.78rem; display: block;">
                            <i class="fa-solid fa-bolt" style="color: #10b981;"></i> Auto-compressed &amp; optimized on upload. PNG, JPG, WEBP, GIF, or SVG. Select multiple files at once.
                        </small>
                        <div style="font-size: 0.78rem; color: #4338ca; margin-top: 0.4rem; font-weight: 700;">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> Tip: Any file named with <code>cover</code> (e.g. <code>cover.jpg</code> or <code>physics_cover.png</code>) is automatically detected and set as the 1st primary cover!
                        </div>
                    </div>

                    <!-- Client-side Preview of Chosen Files with Compression Stats -->
                    <div id="filePreviewContainer" style="display: none; margin-top: 1rem;"></div>

                    <!-- External Image URLs (Optional) -->
                    <div style="margin-top: 1.25rem;">
                        <label for="preview_urls" style="font-weight: 700; font-size: 0.85rem; color: #475569; margin-bottom: 0.35rem;">
                            Or Add Remote Image URLs (One URL per line):
                        </label>
                        <textarea id="preview_urls" name="preview_urls" rows="2" placeholder="https://images.unsplash.com/...&#10;https://images.unsplash.com/..." style="font-size: 0.88rem; font-family: monospace;"></textarea>
                    </div>

                    <div style="margin-top: 0.75rem;">
                        <label for="thumbnail_url" style="font-weight: 700; font-size: 0.85rem; color: #475569; margin-bottom: 0.35rem;">
                            Optional Primary Cover URL Fallback:
                        </label>
                        <input type="url" id="thumbnail_url" name="thumbnail_url" placeholder="https://images.unsplash.com/... or leave blank for automatic primary" style="font-size: 0.88rem;">
                    </div>
                </div>

                <!-- Description with Markdown & KaTeX Toolbar -->
                <div style="margin-bottom: 2rem;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 0.4rem; flex-wrap: wrap; gap: 0.5rem;">
                        <label for="description" style="font-weight: 800; font-size: 0.9rem; color: #1e293b; margin-bottom: 0;">
                            Description & Key Highlights <span style="color: #ef4444;">*</span>
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
                    <textarea id="description" name="description" rows="7" placeholder="Detail the questions, difficulty levels, answer keys, and problem types included in this resource... (Markdown & KaTeX supported)" required style="font-size: 0.95rem; border-top-left-radius: 0; border-top-right-radius: 0; margin-bottom: 0;"></textarea>

                    <!-- Live Markdown & KaTeX Preview Box -->
                    <div id="mdPreviewContainer" style="display: none; background: #ffffff; border: 1.5px solid #c7d2fe; border-radius: 0 0 8px 8px; border-top: none; padding: 1.25rem; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02); margin-top: -1px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.4rem;">
                            <span style="font-size: 0.75rem; font-weight: 800; color: #4338ca; text-transform: uppercase; letter-spacing: 0.05em;">
                                <i class="fa-solid fa-sparkles"></i> Live Storefront Preview (Markdown + KaTeX)
                            </span>
                            <span style="font-size: 0.72rem; color: #64748b;">Updates on click</span>
                        </div>
                        <div id="mdPreviewContent" class="markdown-content" style="min-height: 50px;">
                            <em style="color: #94a3b8;">Type description above and click "Live Preview" to preview...</em>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <button type="submit" style="padding: 0.85rem 2rem; font-size: 1rem; font-weight: 800; background-color: #4f46e5; border-color: #4f46e5; color: #ffffff; border-radius: 8px; cursor: pointer;">
                        <i class="fa-solid fa-check"></i> Publish to Lockinrank Store
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

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function handleJsonAutofill(input) {
            if (!input.files || input.files.length === 0) return;
            const file = input.files[0];
            const reader = new FileReader();
            const statusBox = document.getElementById('jsonAutofillStatus');

            reader.onload = function(e) {
                try {
                    let data = JSON.parse(e.target.result);
                    if (Array.isArray(data) && data.length > 0) {
                        data = data[0]; // take first item if array
                    }
                    if (!data || typeof data !== 'object') {
                        throw new Error("Invalid product JSON object");
                    }

                    if (data.title) document.getElementById('title').value = data.title;
                    if (data.price !== undefined) document.getElementById('price').value = data.price;
                    if (data.mrp !== undefined && data.mrp !== null) document.getElementById('mrp').value = data.mrp;
                    if (data.is_free !== undefined) {
                        const freeCheck = document.getElementById('is_free');
                        if (freeCheck) freeCheck.checked = Boolean(Number(data.is_free));
                    }

                    // Category select matching by id or name
                    if (data.category_id) {
                        const catSel = document.getElementById('category_id');
                        if (catSel) catSel.value = data.category_id;
                    } else if (data.category) {
                        const catSel = document.getElementById('category_id');
                        if (catSel) {
                            for (let opt of catSel.options) {
                                if (opt.text.toLowerCase().trim() === data.category.toLowerCase().trim()) {
                                    catSel.value = opt.value;
                                    break;
                                }
                            }
                        }
                    }

                    // Exam / Board / Subject / Resource details
                    ['grade', 'board', 'subject', 'resource_type', 'file_format', 'page_count', 'standards', 'tags'].forEach(field => {
                        const el = document.getElementById(field);
                        if (el && data[field] !== undefined) {
                            el.value = data[field];
                        }
                    });

                    // Description & Key Highlights
                    let desc = data.description || data.description_and_key_highlights || '';
                    const rawHighlights = data.key_highlights || data.highlights || data.what_is_included;
                    if (rawHighlights) {
                        let hlText = '';
                        if (Array.isArray(rawHighlights)) {
                            const bullets = rawHighlights.map(h => "- " + String(h).replace(/^[-*\s]+/, '')).filter(Boolean);
                            if (bullets.length > 0) {
                                hlText = "### Key Highlights & What's Included:\n\n" + bullets.join("\n");
                            }
                        } else if (typeof rawHighlights === 'string' && rawHighlights.trim()) {
                            const trimmedHl = rawHighlights.trim();
                            hlText = trimmedHl.startsWith('#') ? trimmedHl : ("### Key Highlights & What's Included:\n\n" + trimmedHl);
                        }
                        if (hlText && !desc.includes(hlText)) {
                            desc = desc ? (desc.trim() + "\n\n" + hlText) : hlText;
                        }
                    }
                    if (desc) {
                        const descEl = document.getElementById('description');
                        if (descEl) descEl.value = desc;
                    }

                    if (data.is_ink_friendly !== undefined) {
                        const inkCheck = document.getElementById('is_ink_friendly');
                        if (inkCheck) inkCheck.checked = Boolean(Number(data.is_ink_friendly));
                    }

                    // Preview URLs & Cover Image prioritization
                    if (data.preview_images && Array.isArray(data.preview_images)) {
                        // Prioritize image with 'cover' in its URL or filename to index 0
                        data.preview_images.sort((a, b) => {
                            const aCover = String(a).toLowerCase().includes('cover');
                            const bCover = String(b).toLowerCase().includes('cover');
                            if (aCover && !bCover) return -1;
                            if (!aCover && bCover) return 1;
                            return 0;
                        });

                        const urlsEl = document.getElementById('preview_urls');
                        if (urlsEl) urlsEl.value = data.preview_images.join("\n");

                        // If cover image exists or thumbnail not explicitly given, use first image
                        if (!data.thumbnail_url && data.preview_images[0]) {
                            const thumbEl = document.getElementById('thumbnail_url');
                            if (thumbEl) thumbEl.value = data.preview_images[0];
                        }
                    }

                    // Thumbnail URL
                    if (data.thumbnail_url) {
                        const thumbEl = document.getElementById('thumbnail_url');
                        if (thumbEl) thumbEl.value = data.thumbnail_url;
                    }

                    calcDiscount();

                    if (statusBox) {
                        statusBox.style.display = 'block';
                        statusBox.style.color = '#065f46';
                        statusBox.style.background = '#d1fae5';
                        statusBox.style.borderColor = '#6ee7b7';
                        statusBox.innerHTML = '<i class="fa-solid fa-check-circle"></i> Successfully loaded data from <strong>' + escapeHtml(file.name) + '</strong> (' + (data.title ? escapeHtml(data.title) : 'Product') + ')';
                    }
                } catch (err) {
                    if (statusBox) {
                        statusBox.style.display = 'block';
                        statusBox.style.color = '#991b1b';
                        statusBox.style.background = '#fee2e2';
                        statusBox.style.borderColor = '#fca5a5';
                        statusBox.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Error parsing JSON: ' + escapeHtml(err.message);
                    }
                }
            };
            reader.readAsText(file);
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
            processAndCompressInput(input, 'filePreviewContainer', '#');
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
