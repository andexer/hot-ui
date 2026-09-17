<?php

declare(strict_types=1);

namespace Components\Ci4\Http;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;
use Components\Hotfire\Config;
use Components\Hotfire\Engine;

/**
 * Round-trip endpoint for Hotfire components.
 *
 * The JS driver POSTs the signed snapshot plus an action as JSON; the server
 * verifies the snapshot, hydrates the component, runs the action and returns
 * fresh HTML plus a new snapshot that morphs in place.
 *
 * Route (app/Config/Routes.php), using the configured endpoint path:
 *
 *   $routes->post('hot-ui/update', 'Components\Ci4\Http\HotfireController::update');
 *
 * If the global CSRF filter protects POST routes, exclude this one (the driver
 * sends JSON, not a form CSRF token):
 *
 *   // app/Config/Filters.php
 *   public array $globals = ['except' => ['csrf', 'hot-ui/update']];
 *
 * Failures answer with the status that describes them (see HotfireStatus): 413
 * for a payload too large, 422 for a client payload the server can never
 * accept, 404 for a component that does not exist, 500 when the server itself
 * cannot comply. Server faults are logged even outside production, so a bug
 * never disappears as a bare 500.
 */
final class HotfireController extends Controller
{
    /** Maximum accepted request body in bytes (JSON snapshot + action). */
    private const MAX_BODY_BYTES = 131072;

    public function update(): ResponseInterface
    {
        if (strlen((string) $this->request->getBody()) > self::MAX_BODY_BYTES) {
            return $this->json(['error' => 'Hotfire: request body too large.'])->setStatusCode(413);
        }

        $body = $this->request->getJSON(true);
        if (
            ! is_array($body)
            || ! isset($body['snapshot'], $body['action'])
            || ! is_array($body['snapshot'])
            || ! is_array($body['action'])
        ) {
            // Same body shape as every other failure: {error}, never an empty
            // object the driver cannot tell apart from a successful payload.
            return $this->json(['error' => 'Hotfire: request must carry a snapshot and an action.'])
                ->setStatusCode(422);
        }

        try {
            $payload = Engine::call($body['snapshot'], $body['action'], $this->endpoint());
        } catch (\Throwable $exception) {
            return $this->failure($exception);
        }

        return $this->json($payload);
    }

    /**
     * Answers a failed round-trip with the status its cause deserves. A client
     * mistake keeps the exception text in development; a server fault is
     * logged even there, so the cause is never lost.
     */
    private function failure(\Throwable $exception): ResponseInterface
    {
        $status = HotfireStatus::for($exception);

        return $this->json(['error' => $this->publicMessage($exception, $status >= 500)])
            ->setStatusCode($status);
    }

    /**
     * Production never echoes raw exception text back to the client (it can
     * leak stack traces, paths and internals); the full error is logged and a
     * generic message returned instead.
     */
    private function publicMessage(\Throwable $exception, bool $serverFault): string
    {
        $production = defined('ENVIRONMENT') && ENVIRONMENT === 'production';
        if ($production || $serverFault) {
            if (function_exists('log_message')) {
                log_message(
                    'error',
                    '[Hotfire] {message} in {file}:{line} (exception {class})',
                    [
                        'message' => $exception->getMessage(),
                        'file' => $exception->getFile(),
                        'line' => $exception->getLine(),
                        'class' => $exception::class,
                    ],
                );
            }
        }

        return $production ? 'Hotfire: the component could not be updated.' : $exception->getMessage();
    }

    /** Resolves the configured endpoint to a usable URL. */
    private function endpoint(): string
    {
        $endpoint = Config::shared()->endpoint();

        if (preg_match('#^(https?:)?//#', $endpoint) === 1 || str_starts_with($endpoint, '/')) {
            return $endpoint;
        }

        return function_exists('site_url') ? (string) site_url($endpoint) : $endpoint;
    }

    private function json(array $data): ResponseInterface
    {
        return $this->response->setContentType('application/json')->setJSON($data);
    }
}