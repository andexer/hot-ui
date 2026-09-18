<?php

declare(strict_types=1);

namespace Components\Hotfire\Uploads;

use Components\Support\Filesystem;

/**
 * Upload handler for Hotfire components.
 * 
 * Supports:
 * - Temporary file storage
 * - Upload progress tracking
 * - File previews
 * - Validation
 * - Cleanup
 * - CI4/local storage and S3-compatible adapters
 */
final class UploadHandler
{
    /**
     * Temporary upload directory.
     * 
     * @var string
     */
    private string $tempDir;

    /**
     * Allowed file types.
     * 
     * @var array<int, string>
     */
    private array $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'text/plain'];

    /**
     * Maximum file size in bytes (default 10MB).
     * 
     * @var int
     */
    private int $maxSize = 10485760;

    /**
     * Upload progress tracking.
     * 
     * @var array<string, int>
     */
    private array $progress = [];

    /**
     * Storage configuration.
     * 
     * @var array<string, mixed>
     */
    private array $storageConfig = [
        'type' => 'local',
    ];

    public function __construct(?string $tempDir = null)
    {
        $this->tempDir = $tempDir ?? sys_get_temp_dir().'/hotui-uploads';
        
        Filesystem::ensureDirectory($this->tempDir);
    }

    /**
     * Set allowed file types.
     * 
     * @param array<int, string> $types MIME types
     * @return void
     */
    public function setAllowedTypes(array $types): void
    {
        $this->allowedTypes = $types;
    }

    /**
     * Set maximum file size.
     * 
     * @param int $bytes Maximum size in bytes
     * @return void
     */
    public function setMaxSize(int $bytes): void
    {
        $this->maxSize = $bytes;
    }

    /**
     * Configure storage.
     *
     * Local storage expects `['type' => 'local']`.
     * S3-compatible storage expects `['type' => 's3', 'put' => callable]`,
     * where the callable receives `(string $tempPath, string $destination, array $config)`.
     * This keeps the package zero-dependency while letting the host inject its
     * AWS SDK, MinIO client or CodeIgniter storage service.
     * 
     * @param array<string, mixed> $config Storage configuration
     * @return void
     */
    public function setStorage(array $config): void
    {
        $this->storageConfig = $config;
    }

    /**
     * Handle file upload.
     * 
     * @param array $file File data from $_FILES
     * @return array{success: bool, path: string|null, error: string|null, progress: int}
     */
    public function upload(array $file): array
    {
        if (! isset($file['tmp_name']) || ! is_string($file['tmp_name']) || ! is_file($file['tmp_name']) || ! is_readable($file['tmp_name'])) {
            return [
                'success' => false,
                'path' => null,
                'error' => 'No file uploaded',
                'progress' => 0,
            ];
        }

        // Validate file type
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        if (! in_array($mimeType, $this->allowedTypes, true)) {
            return [
                'success' => false,
                'path' => null,
                'error' => 'File type not allowed',
                'progress' => 0,
            ];
        }

        // Validate file size
        if ($file['size'] > $this->maxSize) {
            return [
                'success' => false,
                'path' => null,
                'error' => 'File too large',
                'progress' => 0,
            ];
        }

        // Generate unique filename
        $extension = preg_replace('/[^A-Za-z0-9]/', '', pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        $filename = uniqid('upload_', true).'.'.$extension;
        $targetPath = $this->tempDir.'/'.$filename;

        $moved = is_uploaded_file($file['tmp_name'])
            ? move_uploaded_file($file['tmp_name'], $targetPath)
            : rename($file['tmp_name'], $targetPath);

        if (! $moved) {
            return [
                'success' => false,
                'path' => null,
                'error' => 'Failed to move file',
                'progress' => 0,
            ];
        }

        // Update progress
        $uploadId = md5($file['tmp_name']);
        $this->progress[$uploadId] = 100;

        return [
            'success' => true,
            'path' => $targetPath,
            'error' => null,
            'progress' => 100,
            'uploadId' => $uploadId,
            'filename' => $filename,
            'originalName' => basename((string) ($file['name'] ?? $filename)),
            'mimeType' => $mimeType,
            'size' => $file['size'],
        ];
    }

    /**
     * Get upload progress.
     * 
     * @param string $uploadId Upload ID
     * @return int Progress percentage (0-100)
     */
    public function getProgress(string $uploadId): int
    {
        return $this->progress[$uploadId] ?? 0;
    }

    /**
     * Generate a preview for an image file.
     * 
     * @param string $filePath File path
     * @return string|null Base64 encoded preview or null
     */
    public function generatePreview(string $filePath): ?string
    {
        if (! file_exists($filePath)) {
            return null;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($filePath);

        if (! str_starts_with($mimeType, 'image/')) {
            return null;
        }

        $imageData = file_get_contents($filePath);
        if ($imageData === false) {
            return null;
        }

        return 'data:'.$mimeType.';base64,'.base64_encode($imageData);
    }

    /**
     * Move file to permanent storage.
     * 
     * @param string $tempPath Temporary file path
     * @param string $destination Destination path
     * @return bool Whether the move was successful
     */
    public function moveToStorage(string $tempPath, string $destination): bool
    {
        if ($this->storageConfig['type'] === 's3') {
            return $this->moveToS3($tempPath, $destination);
        }

        return $this->moveToLocal($tempPath, $destination);
    }

    /**
     * Move file to local storage.
     * 
     * @param string $tempPath Temporary file path
     * @param string $destination Destination path
     * @return bool Whether the move was successful
     */
    private function moveToLocal(string $tempPath, string $destination): bool
    {
        if (! is_file($tempPath)) {
            return false;
        }

        $dir = dirname($destination);
        Filesystem::ensureDirectory($dir);

        return rename($tempPath, $destination);
    }

    /**
     * Move file to S3 storage.
     * 
     * @param string $tempPath Temporary file path
     * @param string $destination Destination path
     * @return bool Whether the upload was successful
     */
    private function moveToS3(string $tempPath, string $destination): bool
    {
        if (! is_file($tempPath)) {
            return false;
        }

        $put = $this->storageConfig['put'] ?? null;
        if (is_callable($put)) {
            return (bool) $put($tempPath, $destination, $this->storageConfig);
        }

        return false;
    }

    /**
     * Clean up temporary files.
     * 
     * @param int $maxAge Maximum age in seconds (default 1 hour)
     * @return int Number of files cleaned up
     */
    public function cleanup(int $maxAge = 3600): int
    {
        $cleaned = 0;
        $now = time();

        foreach (scandir($this->tempDir) ?: [] as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $this->tempDir.'/'.$file;
            
            if (! is_file($filePath)) {
                continue;
            }

            if (filemtime($filePath) < ($now - $maxAge)) {
                unlink($filePath);
                $cleaned++;
            }
        }

        return $cleaned;
    }

    /**
     * Clean up all temporary files.
     * 
     * @return int Number of files cleaned up
     */
    public function cleanupAll(): int
    {
        $cleaned = 0;

        foreach (scandir($this->tempDir) ?: [] as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $this->tempDir.'/'.$file;
            
            if (is_file($filePath) && unlink($filePath)) {
                $cleaned++;
            }
        }

        return $cleaned;
    }

    /**
     * Validate file based on rules.
     * 
     * @param array $file File data
     * @param array<string, string> $rules Validation rules
     * @return array{valid: bool, errors: array<string, string>}
     */
    public function validate(array $file, array $rules = []): array
    {
        $errors = [];

        foreach ($rules as $rule => $value) {
            switch ($rule) {
                case 'mimes':
                    if (! isset($file['tmp_name']) || ! is_file((string) $file['tmp_name'])) {
                        $errors[$rule] = 'File is missing';
                        break;
                    }
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo->file((string) $file['tmp_name']);
                    $allowed = explode(',', $value);
                    if (! in_array($mimeType, $allowed, true)) {
                        $errors[$rule] = "File type must be one of: {$value}";
                    }
                    break;
                    
                case 'max':
                    if ($file['size'] > (int) $value) {
                        $errors[$rule] = "File must be smaller than {$value} bytes";
                    }
                    break;
                    
                case 'min':
                    if ($file['size'] < (int) $value) {
                        $errors[$rule] = "File must be at least {$value} bytes";
                    }
                    break;
            }
        }

        return [
            'valid' => $errors === [],
            'errors' => $errors,
        ];
    }
}
