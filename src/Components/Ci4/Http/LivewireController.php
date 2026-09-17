<?php

declare(strict_types=1);

namespace Components\Ci4\Http;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;
use Components\Reactivity\Engine;

/**
 * Round-trip endpoint for reactive components.
 *
 * The JS driver POSTs the signed snapshot + an action as JSON; the server
 * verifies the snapshot, hydrates the component, runs the action and returns
 * fresh HTML + a new snapshot that morphs in place.
 *
 * Route (app/Config/Routes.php):
 *
 *   $routes->post('hot-ui/update', 'Components\Ci4\Http\LivewireController::update');
 *
 * If the global CSRF filter protects POST routes, allow this one
 * (the driver sends JSON, not a form CSRF token):
 *
 *   // app/Config/Filters.php
 *   $this->globals['except'] = ['csrf', 'hot-ui/update'];
 */
final class LivewireController extends Controller
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
            $payload = Engine::call($body['snapshot'], $body['action'], $this->actionUrl());
        } catch (\Throwable $exception) {
            return $this->json(['error' => $exception->getMessage()])->setStatusCode(422);
        }

        return $this->json($payload);
    }

    private function actionUrl(): string
    {
        return function_exists('site_url') ? (string) site_url('hot-ui/update') : 'hot-ui/update';
    }

    private function json(array $data): ResponseInterface
    {
        return $this->response->setContentType('application/json')->setJSON($data);
    }
}