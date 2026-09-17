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
 */
final class HotfireController extends Controller
{
    public function update(): ResponseInterface
    {
        $body = $this->request->getJSON(true);
        if (
            ! is_array($body)
            || ! isset($body['snapshot'], $body['action'])
            || ! is_array($body['snapshot'])
            || ! is_array($body['action'])
        ) {
            return $this->json([])->setStatusCode(422);
        }

        try {
            $payload = Engine::call($body['snapshot'], $body['action'], $this->endpoint());
        } catch (\Throwable $exception) {
            return $this->json(['error' => $exception->getMessage()])->setStatusCode(422);
        }

        return $this->json($payload);
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