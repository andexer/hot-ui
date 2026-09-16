<?php

declare(strict_types=1);

/**
 * Demo router — run from the project root:
 *
 *   php -S 127.0.0.1:8080 demo/router.php
 *
 * Routes:
 *   /                        Component showcase
 *   /sintaxis                Tag syntax (<ui:…>) showcase (app layout)
 *   /examples/login          Login example (guest layout)
 *   /examples/dashboard      Admin dashboard example (app layout)
 *   /examples/blog           Blog front page example (app layout)
 *
 * Static assets under css/ and js/ stream straight from disk.
 */

require dirname(__DIR__).'/vendor/autoload.php';

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

$static = realpath(__DIR__.'/..'.str_replace(['..', "\0"], '', $uri));
if ($uri !== '/' && $static !== false && str_starts_with($static, dirname(__DIR__)) && is_file($static)) {
    return false;
}

$ui = Components\Ui::new();

header('Content-Type: text/html; charset=utf-8');

/**
 * Wraps a tag-syntax body ($ui->view) into a master layout.
 *
 * Pages written with <ui:…> stream their output, so composition (layout +
 * meta + nav) belongs to the router/controller, not to the view file.
 */
$wrap = static function (string $layout, string $body, string $title, string $description, array $nav) use ($ui): string {
    return $ui->render($layout, [
        'title' => $title,
        'meta' => $ui->render('partials/meta', [
            'title' => $title,
            'description' => $description,
        ]),
        'analytics' => $ui->render('partials/analytics', ['analyticsId' => null]),
        'nav' => $nav,
        'content' => $body,
    ]);
};

$examplesNav = [
    'Componentes' => '/',
    'Sintaxis' => '/sintaxis',
    'Dashboard' => '/examples/dashboard',
    'Blog' => '/examples/blog',
    'Login' => '/examples/login',
];

switch ($uri) {
    case '/sintaxis':
        echo $wrap(
            'layouts/app',
            $ui->view('examples/sintaxis', ['deshabilitado' => true]),
            'Hot-UI — sintaxis de tags',
            'Escribe componentes como <ui:button> en vez de echo + buffers.',
            $examplesNav,
        );

        return;

    case '/examples/login':
        echo $wrap(
            'layouts/guest',
            $ui->view('examples/login'),
            'Acceder — Hot-UI',
            'Ejemplo de pantalla de acceso construida con Hot-UI.',
            [],
        );

        return;

    case '/examples/dashboard':
        echo $wrap(
            'layouts/app',
            $ui->view('examples/dashboard'),
            'Dashboard — Hot-UI',
            'Ejemplo de panel de administración con Hot-UI.',
            ['Dashboard' => '#', 'Clientes' => '#clientes', 'Facturación' => '#facturacion'],
        );

        return;

    case '/examples/blog':
        echo $wrap(
            'layouts/app',
            $ui->view('examples/blog'),
            'Blog — Hot-UI',
            'Ejemplo de portada de blog construida con Hot-UI.',
            ['Inicio' => '/', 'Artículos' => '#articulos', 'Acerca de' => '#acerca'],
        );

        return;

    case '/':
        $body = $ui->render('demo');
        $body .= $ui->render('partials/examples-nav');

        echo $wrap(
            'layouts/app',
            $body,
            'Hot-UI — componentes PHP + Alpine',
            'Librería de componentes UI estilo shadcn en PHP puro (cero dependencias).',
            $examplesNav,
        );

        return;
}

$page = $ui->render('layouts/error', [
    'code' => 404,
    'message' => 'La página solicitada no existe.',
    'backUrl' => '/',
]);

http_response_code(404);
echo $page;