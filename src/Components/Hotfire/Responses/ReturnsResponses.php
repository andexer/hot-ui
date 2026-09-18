<?php

declare(strict_types=1);

namespace Components\Hotfire\Responses;

/**
 * Trait for components that return special responses from actions.
 * 
 * Provides helper methods for:
 * - Redirect responses
 * - Flash messages
 * - Download responses
 * - No content responses
 */
trait ReturnsResponses
{
    /**
     * Return a redirect response.
     * 
     * @param string $url Target URL
     * @param int $status HTTP status code
     * @return RedirectResponse
     */
    public function redirect(string $url, int $status = 302): RedirectResponse
    {
        return new RedirectResponse($url, $status);
    }

    /**
     * Return a flash message.
     * 
     * @param string $message Message text
     * @param string $type Message type (success, error, warning, info)
     * @return FlashMessage
     */
    public function flash(string $message, string $type = 'info'): FlashMessage
    {
        return new FlashMessage($message, $type);
    }

    /**
     * Return a download response.
     * 
     * @param string $content File content
     * @param string $filename Download filename
     * @param string $mimeType MIME type
     * @return DownloadResponse
     */
    public function download(string $content, string $filename, string $mimeType = 'application/octet-stream'): DownloadResponse
    {
        return new DownloadResponse($content, $filename, $mimeType);
    }

    /**
     * Return a no content response.
     * 
     * @param int $status HTTP status code
     * @return NoContentResponse
     */
    public function noContent(int $status = 204): NoContentResponse
    {
        return new NoContentResponse($status);
    }
}
