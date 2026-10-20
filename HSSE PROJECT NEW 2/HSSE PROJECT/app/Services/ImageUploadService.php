<?php

namespace App\Services;

use App\Models\MediaAttachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUploadService
{
    /**
     * Supported MIME types for images.
     */
    public const SUPPORTED_MIMES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
        'image/bmp',
        'image/x-ms-bmp',
        'image/avif',
    ];

    /**
     * Supported file extensions.
     */
    public const SUPPORTED_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'avif'
    ];

    /**
     * Upload, validate, compress and record image metadata in database.
     *
     * @param UploadedFile $file
     * @param string $folder Relative path inside storage disk (e.g. 'issue-follow-up', 'events', 'daily-works')
     * @param Model|null $model Optional model to link polymorphically
     * @param string|null $module Module identifier name
     * @param string|null $caption Optional description/caption
     * @param string $disk Disk name (default: 'public')
     * @param int $maxWidth Max width before resizing (default: 1920)
     * @param int $maxHeight Max height before resizing (default: 1920)
     * @param int $quality JPEG/WebP quality 1-100 (default: 82)
     * @return MediaAttachment
     */
    public function uploadImage(
        UploadedFile $file,
        string $folder = 'uploads',
        ?Model $model = null,
        ?string $module = null,
        ?string $caption = null,
        string $disk = 'public',
        int $maxWidth = 1920,
        int $maxHeight = 1920,
        int $quality = 82
    ): MediaAttachment {
        $originalName = $file->getClientOriginalName();
        $originalSize = $file->getSize();
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg');
        $mimeType = $file->getMimeType() ?: 'image/jpeg';
        $uploadedBy = Auth::user()?->name ?? 'Petugas HSSE Dieng';

        $rawContent = file_get_contents($file->getRealPath());

        // Process image using GD (resize, auto-orient, compress)
        $processed = $this->processImageWithGd($file->getRealPath(), $mimeType, $maxWidth, $maxHeight, $quality);

        if ($processed !== null) {
            $saveContent = $processed['content'];
            $targetExt = $processed['extension'];
            $targetMime = $processed['mime'];
            $width = $processed['width'];
            $height = $processed['height'];
        } else {
            // Fallback: save original content safely
            $saveContent = $rawContent;
            $targetExt = $extension;
            $targetMime = $mimeType;
            $dimensions = @getimagesizefromstring($rawContent);
            $width = $dimensions ? $dimensions[0] : null;
            $height = $dimensions ? $dimensions[1] : null;
        }

        $compressedSize = strlen($saveContent);
        $fileName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '_' . date('Ymd_His') . '_' . Str::random(6) . '.' . $targetExt;
        $relativePath = trim($folder, '/') . '/' . $fileName;

        // Store file in designated filesystem disk
        Storage::disk($disk)->put($relativePath, $saveContent);

        // Record structured metadata in database
        return MediaAttachment::create([
            'mediable_type' => $model ? get_class($model) : null,
            'mediable_id' => $model ? $model->getKey() : null,
            'module' => $module ?? ($model ? Str::slug(class_basename($model)) : 'general'),
            'disk' => $disk,
            'file_path' => $relativePath,
            'original_name' => $originalName,
            'file_name' => $fileName,
            'mime_type' => $targetMime,
            'extension' => $targetExt,
            'original_size' => $originalSize,
            'compressed_size' => $compressedSize,
            'width' => $width,
            'height' => $height,
            'uploaded_by' => $uploadedBy,
            'caption' => $caption,
        ]);
    }

    /**
     * Process image using PHP GD (resizing, auto-orienting EXIF, and compressing).
     */
    protected function processImageWithGd(
        string $sourcePath,
        string $mimeType,
        int $maxWidth,
        int $maxHeight,
        int $quality
    ): ?array {
        if (!function_exists('imagecreatefromstring')) {
            return null;
        }

        $rawData = @file_get_contents($sourcePath);
        if (!$rawData) {
            return null;
        }

        $sourceImage = @imagecreatefromstring($rawData);
        if (!$sourceImage) {
            return null;
        }

        // Auto-orient based on EXIF orientation if JPEG
        if (function_exists('exif_read_data') && in_array($mimeType, ['image/jpeg', 'image/jpg'])) {
            $exif = @exif_read_data($sourcePath);
            if (!empty($exif['Orientation'])) {
                $sourceImage = match ($exif['Orientation']) {
                    3 => imagerotate($sourceImage, 180, 0),
                    6 => imagerotate($sourceImage, -90, 0),
                    8 => imagerotate($sourceImage, 90, 0),
                    default => $sourceImage,
                };
            }
        }

        $origWidth = imagesx($sourceImage);
        $origHeight = imagesy($sourceImage);

        // Compute proportional scaled dimensions
        $ratio = min($maxWidth / max(1, $origWidth), $maxHeight / max(1, $origHeight), 1.0);
        $newWidth = (int) round($origWidth * $ratio);
        $newHeight = (int) round($origHeight * $ratio);

        $targetImage = imagecreatetruecolor($newWidth, $newHeight);

        // Handle alpha transparency for PNG / WebP / GIF
        if (in_array($mimeType, ['image/png', 'image/webp', 'image/gif'])) {
            imagealphablending($targetImage, false);
            imagesavealpha($targetImage, true);
            $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
            imagefilledrectangle($targetImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        imagecopyresampled($targetImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

        ob_start();
        $targetExt = 'jpg';
        $targetMime = 'image/jpeg';

        if ($mimeType === 'image/webp' && function_exists('imagewebp')) {
            imagewebp($targetImage, null, $quality);
            $targetExt = 'webp';
            $targetMime = 'image/webp';
        } elseif ($mimeType === 'image/png' && function_exists('imagepng')) {
            // PNG compression level (0-9)
            $pngQuality = (int) round((100 - $quality) / 10);
            imagepng($targetImage, null, min(9, max(1, $pngQuality)));
            $targetExt = 'png';
            $targetMime = 'image/png';
        } else {
            imagejpeg($targetImage, null, $quality);
            $targetExt = 'jpg';
            $targetMime = 'image/jpeg';
        }

        $content = ob_get_clean();

        imagedestroy($sourceImage);
        imagedestroy($targetImage);

        return [
            'content' => $content,
            'extension' => $targetExt,
            'mime' => $targetMime,
            'width' => $newWidth,
            'height' => $newHeight,
        ];
    }

    /**
     * Validate if an UploadedFile matches supported image formats.
     */
    public function isValidImage(UploadedFile $file): bool
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $mime = strtolower($file->getMimeType());

        return in_array($ext, self::SUPPORTED_EXTENSIONS, true) ||
               in_array($mime, self::SUPPORTED_MIMES, true) ||
               str_starts_with($mime, 'image/');
    }
}

