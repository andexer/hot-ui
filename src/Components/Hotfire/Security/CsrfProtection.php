<?php

declare(strict_types=1);

namespace Components\Hotfire\Security;

/**
 * CSRF protection helper for Hotfire components.
 * 
 * Automatically reads CodeIgniter 4 CSRF token and includes it in requests.
 * The Hotfire endpoint will not be excluded from CSRF protection.
 */
final class CsrfProtection
{
    /**
     * Gets the CSRF token from CodeIgniter 4 session.
     * 
     * @return string|null CSRF token or null if not available
     */
    public static function getToken(): ?string
    {
        // Try to get token from CI4 session
        if (function_exists('session')) {
            $session = session();
            if ($session !== null) {
                return $session->get('csrf_token');
            }
        }

        // Try to get from $_SESSION directly
        if (isset($_SESSION['csrf_token'])) {
            return $_SESSION['csrf_token'];
        }

        // Try to get from CI4 config (CI4 v4.4+)
        if (defined('Config\\App') && class_exists('Config\\App')) {
            $app = new \Config\App();
            if (isset($app->CSRFProtection) && $app->CSRFProtection) {
                return $_SESSION[$app->CSRFTokenName] ?? null;
            }
        }

        return null;
    }

    /**
     * Gets the CSRF token name.
     * 
     * @return string CSRF token name
     */
    public static function getTokenName(): string
    {
        // Try to get from CI4 config
        if (defined('Config\\App') && class_exists('Config\\App')) {
            $app = new \Config\App();
            return $app->CSRFTokenName ?? 'csrf_token';
        }

        return 'csrf_token';
    }

    /**
     * Gets the CSRF header name for AJAX requests.
     * 
     * @return string CSRF header name
     */
    public static function getHeaderName(): string
    {
        // Try to get from CI4 config
        if (defined('Config\\App') && class_exists('Config\\App')) {
            $app = new \Config\App();
            return $app->CSRFHeaderName ?? 'X-CSRF-TOKEN';
        }

        return 'X-CSRF-TOKEN';
    }

    /**
     * Generates a CSRF meta tag for HTML forms.
     * 
     * @return string HTML meta tag
     */
    public static function metaTag(): string
    {
        $token = self::getToken();
        $name = self::getTokenName();

        if ($token === null) {
            return '';
        }

        return sprintf(
            '<meta name="csrf-token" content="%s">',
            htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
        );
    }

    /**
     * Generates a CSRF hidden input for forms.
     * 
     * @return string HTML hidden input
     */
    public static function hiddenField(): string
    {
        $token = self::getToken();
        $name = self::getTokenName();

        if ($token === null) {
            return '';
        }

        return sprintf(
            '<input type="hidden" name="%s" value="%s">',
            htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
        );
    }

    /**
     * Validates a CSRF token from the request.
     * 
     * @param string|null $token Token to validate
     * @return bool Whether the token is valid
     */
    public static function validate(?string $token): bool
    {
        $expected = self::getToken();

        if ($expected === null || $token === null) {
            return false;
        }

        return hash_equals($expected, $token);
    }

    /**
     * Gets CSRF data for including in API requests.
     * 
     * @return array{header: string, token: string, name: string} CSRF data
     */
    public static function forRequest(): array
    {
        return [
            'header' => self::getHeaderName(),
            'token' => self::getToken() ?? '',
            'name' => self::getTokenName(),
        ];
    }
}
