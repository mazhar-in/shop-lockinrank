<?php

namespace App\Services;

class ImageOptimizer
{
    /**
     * Maximum dimension (width or height) for stored product images.
     */
    public const MAX_DIMENSION = 1200;

    /**
     * Target WebP compression quality (0-100).
     * 82 is recommended for optimal fidelity and light size.
     */
    public const QUALITY_WEBP = 82;

    /**
     * Target JPEG compression quality fallback if WebP is unsupported.
     */
    public const QUALITY_JPEG = 82;

    /**
     * Compress, resize, and store an uploaded image.
     *
     * @param string $tmpPath Path to the uploaded temporary file.
     * @param string $originalName Original file name as uploaded by user.
     * @param string $targetDir Destination directory on disk.
     * @param int $maxDimension Maximum width/height boundary.
     * @return string|null Relative stored URL path e.g. '/uploads/products/prod_...webp', or null on failure.
     */
    public static function compressAndStore(
        string $tmpPath,
        string $originalName,
        string $targetDir,
        int $maxDimension = self::MAX_DIMENSION
    ): ?string {
        if (!file_exists($tmpPath) || filesize($tmpPath) === 0) {
            return null;
        }

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        // SVG files are vector XML; do not rasterize, store as clean SVG
        if ($ext === 'svg') {
            $safeName = 'prod_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.svg';
            $dest = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $safeName;
            if (move_uploaded_file($tmpPath, $dest) || copy($tmpPath, $dest)) {
                return '/uploads/products/' . $safeName;
            }
            return null;
        }

        // Check if GD extension is available
        if (!extension_loaded('gd')) {
            // Fallback: store original file safely
            $safeName = 'prod_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $dest = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $safeName;
            if (move_uploaded_file($tmpPath, $dest) || copy($tmpPath, $dest)) {
                return '/uploads/products/' . $safeName;
            }
            return null;
        }

        try {
            // Load source image
            $srcImage = self::createImageFromFile($tmpPath, $ext);
            if (!$srcImage) {
                // Could not decode with GD; fallback to direct move
                $safeName = 'prod_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $dest = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $safeName;
                if (move_uploaded_file($tmpPath, $dest) || copy($tmpPath, $dest)) {
                    return '/uploads/products/' . $safeName;
                }
                return null;
            }

            // Correct EXIF orientation for photos from mobile devices
            $srcImage = self::fixExifOrientation($srcImage, $tmpPath);

            $origW = imagesx($srcImage);
            $origH = imagesy($srcImage);

            // Compute scaled dimensions preserving aspect ratio
            list($targetW, $targetH) = self::calculateTargetDimensions($origW, $origH, $maxDimension);

            // Create truecolor destination image
            $dstImage = imagecreatetruecolor($targetW, $targetH);

            // Preserve alpha transparency for PNG / WebP / GIF
            imagealphablending($dstImage, false);
            imagesavealpha($dstImage, true);
            $transparent = imagecolorallocatealpha($dstImage, 255, 255, 255, 127);
            imagefilledrectangle($dstImage, 0, 0, $targetW, $targetH, $transparent);

            // High-quality bicubic resampling
            imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $targetW, $targetH, $origW, $origH);

            // Determine optimal format and save
            $canWebP = function_exists('imagewebp');
            $safeBase = 'prod_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4));

            if ($canWebP) {
                $safeName = $safeBase . '.webp';
                $dest = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $safeName;
                imagewebp($dstImage, $dest, self::QUALITY_WEBP);
            } else {
                $safeName = $safeBase . '.jpg';
                $dest = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $safeName;
                imagejpeg($dstImage, $dest, self::QUALITY_JPEG);
            }

            // Free GD memory
            imagedestroy($srcImage);
            imagedestroy($dstImage);

            if (file_exists($dest) && filesize($dest) > 0) {
                @unlink($tmpPath);
                return '/uploads/products/' . $safeName;
            }
        } catch (\Throwable $e) {
            error_log("ImageOptimizer error: " . $e->getMessage());
        }

        // Final safety fallback
        $safeName = 'prod_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest = rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $safeName;
        if (move_uploaded_file($tmpPath, $dest) || copy($tmpPath, $dest)) {
            return '/uploads/products/' . $safeName;
        }

        return null;
    }

    /**
     * Create GD image resource based on file type.
     */
    private static function createImageFromFile(string $path, string $ext)
    {
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                return @imagecreatefromjpeg($path);
            case 'png':
                return @imagecreatefrompng($path);
            case 'webp':
                return function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null;
            case 'gif':
                return @imagecreatefromgif($path);
            default:
                $info = @getimagesize($path);
                if (!$info) return null;
                switch ($info[2]) {
                    case IMAGETYPE_JPEG: return @imagecreatefromjpeg($path);
                    case IMAGETYPE_PNG: return @imagecreatefrompng($path);
                    case IMAGETYPE_WEBP: return function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null;
                    case IMAGETYPE_GIF: return @imagecreatefromgif($path);
                }
                return null;
        }
    }

    /**
     * Calculate proportional target dimensions.
     */
    private static function calculateTargetDimensions(int $origW, int $origH, int $maxDim): array
    {
        if ($origW <= $maxDim && $origH <= $maxDim) {
            return [$origW, $origH];
        }

        if ($origW >= $origH) {
            $targetW = $maxDim;
            $targetH = (int)round(($origH / $origW) * $maxDim);
        } else {
            $targetH = $maxDim;
            $targetW = (int)round(($origW / $origH) * $maxDim);
        }

        return [max(1, $targetW), max(1, $targetH)];
    }

    /**
     * Fix orientation if JPEG has EXIF orientation tag from camera.
     */
    private static function fixExifOrientation($image, string $path)
    {
        if (!function_exists('exif_read_data')) {
            return $image;
        }

        try {
            $exif = @exif_read_data($path);
            if (!empty($exif['Orientation'])) {
                switch ($exif['Orientation']) {
                    case 3:
                        $image = imagerotate($image, 180, 0);
                        break;
                    case 6:
                        $image = imagerotate($image, -90, 0);
                        break;
                    case 8:
                        $image = imagerotate($image, 90, 0);
                        break;
                }
            }
        } catch (\Throwable $e) {}

        return $image;
    }
}
