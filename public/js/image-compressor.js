/**
 * Lockinrank Shop — Client-Side Image Pre-Compression & Resizer
 * Automatically shrinks heavy photos in the browser before upload,
 * saving network bandwidth, preventing server timeouts, and ensuring
 * fast page load speeds.
 */

async function compressImageFile(file, maxDimension = 1200, quality = 0.82) {
    // SVGs are vector XML and do not need raster compression
    if (file.type === 'image/svg+xml' || file.name.toLowerCase().endsWith('.svg')) {
        file._originalSize = file.size;
        file._compressedSize = file.size;
        file._savingsPercent = 0;
        return file;
    }

    // Only process standard raster image types
    if (!file.type.startsWith('image/')) {
        return file;
    }

    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                let w = img.naturalWidth || img.width;
                let h = img.naturalHeight || img.height;

                // Scale down proportionally if larger than maxDimension
                if (w > maxDimension || h > maxDimension) {
                    if (w >= h) {
                        h = Math.round((h / w) * maxDimension);
                        w = maxDimension;
                    } else {
                        w = Math.round((w / h) * maxDimension);
                        h = maxDimension;
                    }
                }

                const canvas = document.createElement('canvas');
                canvas.width = Math.max(1, w);
                canvas.height = Math.max(1, h);
                const ctx = canvas.getContext('2d');

                // Fill with transparent or white background for clean rendering
                ctx.imageSmoothingEnabled = true;
                ctx.imageSmoothingQuality = 'high';
                ctx.drawImage(img, 0, 0, w, h);

                // Try exporting as WebP (supported by all modern browsers)
                const mimeType = 'image/webp';
                canvas.toBlob(function(blob) {
                    if (blob && (blob.size < file.size || w !== img.width || h !== img.height)) {
                        const baseName = file.name.replace(/\.[^.]+$/, '');
                        const newName = baseName + '.webp';
                        const compressedFile = new File([blob], newName, {
                            type: mimeType,
                            lastModified: Date.now()
                        });
                        compressedFile._originalSize = file.size;
                        compressedFile._compressedSize = blob.size;
                        compressedFile._savingsPercent = Math.max(0, Math.round((1 - (blob.size / file.size)) * 100));
                        resolve(compressedFile);
                    } else {
                        // If already highly compressed, return original
                        file._originalSize = file.size;
                        file._compressedSize = file.size;
                        file._savingsPercent = 0;
                        resolve(file);
                    }
                }, mimeType, quality);
            };

            img.onerror = function() {
                resolve(file);
            };

            img.src = e.target.result;
        };

        reader.onerror = function() {
            resolve(file);
        };

        reader.readAsDataURL(file);
    });
}

function formatBytes(bytes, decimals = 1) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

/**
 * Compresses all files in a file input, updates the input with compressed files,
 * and renders an enhanced preview with compression metrics.
 */
async function processAndCompressInput(input, containerId, badgePrefix = '#') {
    const container = document.getElementById(containerId);
    if (!container) return;

    if (!input.files || input.files.length === 0) {
        container.innerHTML = '';
        container.style.display = 'none';
        return;
    }

    container.style.display = 'block';
    container.innerHTML = `
        <div style="background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 8px; padding: 0.75rem 1rem; color: #4338ca; font-size: 0.88rem; font-weight: 700; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
            <i class="fa-solid fa-circle-notch fa-spin"></i>
            <span>Optimizing & compressing ${input.files.length} image${input.files.length > 1 ? 's' : ''} for instant upload...</span>
        </div>
    `;

    const rawFiles = Array.from(input.files);
    // If any image has 'cover' in its name, prioritize it as the first image (cover thumbnail)
    rawFiles.sort((a, b) => {
        const aName = (a.name || '').toLowerCase();
        const bName = (b.name || '').toLowerCase();
        const aCover = aName.includes('cover');
        const bCover = bName.includes('cover');
        if (aCover && !bCover) return -1;
        if (!aCover && bCover) return 1;
        return 0;
    });

    const compressedFiles = [];

    let totalOriginal = 0;
    let totalCompressed = 0;

    for (let i = 0; i < rawFiles.length; i++) {
        const comp = await compressImageFile(rawFiles[i]);
        compressedFiles.push(comp);
        totalOriginal += (comp._originalSize || comp.size);
        totalCompressed += (comp._compressedSize || comp.size);
    }

    // Replace input files with compressed files via DataTransfer
    try {
        const dt = new DataTransfer();
        compressedFiles.forEach(f => dt.items.add(f));
        input.files = dt.files;
    } catch (e) {
        console.warn('DataTransfer file replacement not supported:', e);
    }

    const totalSavings = totalOriginal > 0 ? Math.max(0, Math.round((1 - (totalCompressed / totalOriginal)) * 100)) : 0;

    // Render previews
    container.innerHTML = '';

    // Summary banner
    if (totalOriginal > totalCompressed && totalSavings > 0) {
        const summary = document.createElement('div');
        summary.style.cssText = 'background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 0.65rem 1rem; color: #065f46; font-size: 0.85rem; font-weight: 700; display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.85rem; flex-wrap: wrap; gap: 0.5rem;';
        summary.innerHTML = `
            <span>
                <i class="fa-solid fa-bolt" style="color: #10b981;"></i>
                Pre-compressed ${compressedFiles.length} images: 
                <strong>${formatBytes(totalOriginal)}</strong> &rarr; <strong>${formatBytes(totalCompressed)}</strong> 
                <span style="background: #10b981; color: #ffffff; padding: 0.1rem 0.45rem; border-radius: 4px; font-size: 0.75rem; margin-left: 0.35rem;">-${totalSavings}% lighter</span>
            </span>
            <span style="font-size: 0.78rem; color: #047857; font-weight: 600;">
                <i class="fa-solid fa-circle-check"></i> Ready for fast upload
            </span>
        `;
        container.appendChild(summary);
    }

    const grid = document.createElement('div');
    grid.className = 'admin-preview-grid';
    grid.style.display = 'grid';
    grid.style.gridTemplateColumns = 'repeat(auto-fill, minmax(130px, 1fr))';
    grid.style.gap = '0.75rem';

    compressedFiles.forEach((file, index) => {
        const item = document.createElement('div');
        item.className = 'admin-preview-item';
        item.style.cssText = 'position: relative; border-radius: 8px; overflow: hidden; border: 1px solid #cbd5e1; background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.06);';

        const imgUrl = URL.createObjectURL(file);
        const isCover = (file.name || '').toLowerCase().includes('cover') || index === 0;
        const badgeLabel = isCover ? '<i class="fa-solid fa-star"></i> Cover' : (badgePrefix === '#' ? '#' + (index + 1) : badgePrefix + ' ' + (index + 1));
        const badgeBg = isCover ? 'background: #059669;' : 'background: rgba(15, 23, 42, 0.85);';
        const savingsText = file._savingsPercent > 0 ? `-${file._savingsPercent}%` : 'Optimized';

        item.innerHTML = `
            <img src="${imgUrl}" alt="Preview" style="width: 100%; height: 110px; object-fit: cover; display: block;">
            <div style="position: absolute; top: 4px; left: 4px; ${badgeBg} color: #ffffff; font-size: 0.68rem; font-weight: 700; padding: 0.15rem 0.45rem; border-radius: 4px; display: inline-flex; align-items: center; gap: 0.25rem;">
                ${badgeLabel}
            </div>
            <div style="position: absolute; bottom: 4px; left: 4px; right: 4px; background: rgba(16, 185, 129, 0.9); color: #ffffff; font-size: 0.65rem; font-weight: 800; padding: 0.15rem 0.35rem; border-radius: 4px; text-align: center; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                <i class="fa-solid fa-leaf"></i> ${formatBytes(file.size)} (${savingsText})
            </div>
            <button type="button" onclick="removeCompressedFile(${index}, '${input.id}', '${containerId}', '${badgePrefix}')" style="position: absolute; top: 4px; right: 4px; width: 22px; height: 22px; background: #ef4444; color: #ffffff; border: none; border-radius: 50%; font-size: 0.72rem; display: flex; align-items: center; justify-content: center; cursor: pointer; padding: 0; line-height: 1;" title="Remove this image">&times;</button>
        `;
        grid.appendChild(item);
    });

    container.appendChild(grid);
}

function removeCompressedFile(removeIndex, inputId, containerId, badgePrefix) {
    const input = document.getElementById(inputId);
    if (!input || !input.files) return;

    const dt = new DataTransfer();
    Array.from(input.files).forEach((file, idx) => {
        if (idx !== removeIndex) {
            dt.items.add(file);
        }
    });
    input.files = dt.files;
    processAndCompressInput(input, containerId, badgePrefix);
}

