<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Publish Material — Lockinrank Shop Admin') ?></title>
    <!-- Pico CSS v2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel="stylesheet" href="/css/app.css">
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

                <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                    <!-- Pricing -->
                    <div>
                        <label for="price" style="font-weight: 800; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.4rem;">
                            Price in INR (₹) <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="number" id="price" name="price" value="149" step="1" min="0" required style="font-size: 0.95rem;">
                        <small style="color: #64748b; font-size: 0.78rem;">Set to ₹0 or check below to offer as a 100% Freebie.</small>
                    </div>

                    <!-- Page Count -->
                    <div>
                        <label for="page_count" style="font-weight: 800; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.4rem;">
                            Estimated Page Count
                        </label>
                        <input type="number" id="page_count" name="page_count" value="45" min="1" required style="font-size: 0.95rem;">
                    </div>
                </div>

                <!-- Toggles -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.25rem; margin-bottom: 1.5rem;">
                    <div style="margin-bottom: 0.75rem;">
                        <label style="display: flex; align-items: center; gap: 0.6rem; cursor: pointer; margin-bottom: 0;">
                            <input type="checkbox" id="is_free" name="is_free" value="1" onchange="document.getElementById('price').value = this.checked ? 0 : 149;">
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
                        <small style="color: #64748b; font-size: 0.78rem;">
                            <i class="fa-solid fa-bolt" style="color: #10b981;"></i> Auto-compressed &amp; optimized on upload. PNG, JPG, WEBP, GIF, or SVG. Select multiple files at once.
                        </small>
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

                <!-- Description -->
                <div style="margin-bottom: 2rem;">
                    <label for="description" style="font-weight: 800; font-size: 0.9rem; color: #1e293b; margin-bottom: 0.4rem;">
                        Description & Key Highlights <span style="color: #ef4444;">*</span>
                    </label>
                    <textarea id="description" name="description" rows="5" placeholder="Detail the questions, difficulty levels, answer keys, and problem types included in this resource..." required style="font-size: 0.95rem;"></textarea>
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

    <script src="/js/image-compressor.js"></script>
    <script>
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
    </script>
</body>
</html>
