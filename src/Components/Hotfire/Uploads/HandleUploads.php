<?php

declare(strict_types=1);

namespace Components\Hotfire\Uploads;

/**
 * Trait for components that handle file uploads.
 * 
 * Provides methods for:
 * - Handling file uploads
 * - Tracking upload progress
 * - Generating previews
 * - Validating files
 * - Cleaning up temporary files
 */
trait HandleUploads
{
    /**
     * Upload handler instance.
     * 
     * @var UploadHandler|null
     */
    private ?UploadHandler $uploadHandler = null;

    /**
     * Upload progress tracking for the component.
     * 
     * @var array<string, int>
     */
    public array $uploadProgress = [];

    /**
     * Get or create the upload handler.
     * 
     * @return UploadHandler
     */
    protected function uploadHandler(): UploadHandler
    {
        if ($this->uploadHandler === null) {
            $this->uploadHandler = new UploadHandler();
        }
        
        return $this->uploadHandler;
    }

    /**
     * Handle a file upload.
     * 
     * @param array $file File data from $_FILES
     * @return array{success: bool, path: string|null, error: string|null, progress: int}
     */
    public function handleUpload(array $file): array
    {
        $result = $this->uploadHandler()->upload($file);
        
        if (isset($result['uploadId'])) {
            $this->uploadProgress[$result['uploadId']] = $result['progress'];
        }
        
        return $result;
    }

    /**
     * Get upload progress for a specific upload.
     * 
     * @param string $uploadId Upload ID
     * @return int Progress percentage (0-100)
     */
    public function getUploadProgress(string $uploadId): int
    {
        return $this->uploadProgress[$uploadId] ?? $this->uploadHandler()->getProgress($uploadId);
    }

    /**
     * Generate a preview for an uploaded file.
     * 
     * @param string $filePath File path
     * @return string|null Base64 encoded preview or null
     */
    public function generatePreview(string $filePath): ?string
    {
        return $this->uploadHandler()->generatePreview($filePath);
    }

    /**
     * Move uploaded file to permanent storage.
     * 
     * @param string $tempPath Temporary file path
     * @param string $destination Destination path
     * @return bool Whether the move was successful
     */
    public function moveToStorage(string $tempPath, string $destination): bool
    {
        return $this->uploadHandler()->moveToStorage($tempPath, $destination);
    }

    /**
     * Validate an uploaded file.
     * 
     * @param array $file File data
     * @param array<string, string> $rules Validation rules
     * @return array{valid: bool, errors: array<string, string>}
     */
    public function validateUpload(array $file, array $rules = []): array
    {
        return $this->uploadHandler()->validate($file, $rules);
    }

    /**
     * Clean up temporary upload files.
     * 
     * @param int $maxAge Maximum age in seconds
     * @return int Number of files cleaned up
     */
    public function cleanupUploads(int $maxAge = 3600): int
    {
        return $this->uploadHandler()->cleanup($maxAge);
    }

    /**
     * Clean up all temporary upload files.
     * 
     * @return int Number of files cleaned up
     */
    public function cleanupAllUploads(): int
    {
        return $this->uploadHandler()->cleanupAll();
    }
}
