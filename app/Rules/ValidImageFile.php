<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class ValidImageFile implements ValidationRule
{
    private array $allowedExtensions;
    private array $allowedMimeTypes;
    private int $maxSizeInBytes;
    private string $failureReason = '';

    public function __construct(
        array $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'],
        array $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'],
        int $maxSizeMB = 5
    ) {
        $this->allowedExtensions = array_map('strtolower', $allowedExtensions);
        $this->allowedMimeTypes = $allowedMimeTypes;
        $this->maxSizeInBytes = $maxSizeMB * 1024 * 1024; 
    }

    /**
     * Create instance with default settings for profile images
     */
    public static function profile(int $maxSizeMB = 2): self
    {
        return new self(
            allowedExtensions: ['jpg', 'jpeg', 'png'],
            allowedMimeTypes: ['image/jpeg', 'image/png'],
            maxSizeMB: $maxSizeMB
        );
    }

    /**
     * Create instance with default settings for carpet photos
     */
    public static function carpet(int $maxSizeMB = 5): self
    {
        return new self(
            allowedExtensions: ['jpg', 'jpeg', 'png', 'webp'],
            allowedMimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
            maxSizeMB: $maxSizeMB
        );
    }

    /**
     * Create instance with settings for thumbnails
     */
    public static function thumbnail(int $maxSizeMB = 1): self
    {
        return new self(
            allowedExtensions: ['jpg', 'jpeg', 'png', 'webp'],
            allowedMimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
            maxSizeMB: $maxSizeMB
        );
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile) {
            $this->failureReason = 'not_uploaded_file';
            $fail(__('validation.image_file.not_uploaded_file'));
            return;
        }

        if (!$this->isValidExtension($value)) {
            $this->failureReason = 'invalid_extension';
            $fail(__('validation.image_file.invalid_extension', [
                'extensions' => implode(', ', array_map('strtoupper', $this->allowedExtensions))
            ]));
            return;
        }

        if (!$this->isValidMimeType($value)) {
            $this->failureReason = 'invalid_mime_type';
            $fail(__('validation.image_file.invalid_mime_type'));
            return;
        }

        if (!$this->isValidImageFile($value)) {
            $this->failureReason = 'not_valid_image';
            $fail(__('validation.image_file.not_valid_image'));
            return;
        }

        if (!$this->isValidSize($value)) {
            $this->failureReason = 'file_too_large';
            $fail(__('validation.image_file.file_too_large', [
                'max_size' => $this->formatBytes($this->maxSizeInBytes)
            ]));
            return;
        }
    }

    /**
     * Check if file extension is allowed
     */
    private function isValidExtension(UploadedFile $file): bool
    {
        $extension = strtolower($file->getClientOriginalExtension());
        return in_array($extension, $this->allowedExtensions);
    }

    /**
     * Check if MIME type is allowed using file content analysis
     */
    private function isValidMimeType(UploadedFile $file): bool
    {
        $filePath = $file->getRealPath();
        
        if (!$filePath || !file_exists($filePath)) {
            return false;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            return false;
        }

        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        return in_array($mimeType, $this->allowedMimeTypes);
    }

    /**
     * Verify the file is actually a valid image
     */
    private function isValidImageFile(UploadedFile $file): bool
    {
        $filePath = $file->getRealPath();
        
        if (!$filePath || !file_exists($filePath)) {
            return false;
        }

        $imageInfo = getimagesize($filePath);
        return $imageInfo !== false;
    }

    /**
     * Check if file size is within limits
     */
    private function isValidSize(UploadedFile $file): bool
    {
        return $file->getSize() <= $this->maxSizeInBytes;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        
        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }
        
        return round($bytes, 1) . ' ' . $units[$unitIndex];
    }

    /**
     * Get the reason for validation failure (useful for testing)
     */
    public function getFailureReason(): string
    {
        return $this->failureReason;
    }
}