<?php

declare(strict_types=1);

/**
 * Renders every example page (login, dashboard, blog) through the real
 * engine (compiled tag-syntax view + layout) and validates the output.
 * Exit code 0 = all good.
 *
 *   composer examples        (or: php bin/examples.php)
 */

use Components\Ui;

require dirname(__DIR__).'/vendor/autoload.php';

$ui = Ui::new();

$examples = [
    // [view, layout, esperados: nevadas del título/cuerpo]
    'examples/login' => ['layouts/guest', ['Acceder', 'Bienvenido de nuevo']],
    'examples/dashboard' => ['layouts/app', ['Dashboard', 'Panel general']],
    'examples/blog' => ['layouts/app', ['Blog', 'El blog de Hot-UI']],
];

/** @psalm-param array{string, string} $needles */
$wrap = static function (Ui $ui, string $layout, string $body, string $title, array $needles, array $nav): string {
    return $ui->render($layout, [
        'title' => $title,
        'meta' => $ui->render('partials/meta', ['title' => $title, 'description' => 'Ejemplo Hot-UI.']),
        'nav' => $nav,
        'content' => $body,
    ]);
};

$failed = 0;
foreach ($examples as $template => [$layout, $needles]) {
    [$titleNeedle, $bodyNeedle] = $needles;

    try {
        $html = $wrap(
            $ui,
            $layout,
            $ui->view($template),
            $titleNeedle,
            $needles,
            ['Inicio' => '/', 'Login' => '/examples/login'],
        );
    } catch (Throwable $e) {
        printf("FAIL %s: %s\n", $template, $e->getMessage());
        ++$failed;

        continue;
    }

    $checks = [
        "título [$titleNeedle]" => str_contains($html, $titleNeedle),
        "contenido [$bodyNeedle]" => str_contains($html, $bodyNeedle),
        'doctype' => str_starts_with(ltrim($html), '<!DOCTYPE html>'),
        'css incluido' => str_contains($html, '/css/hot-ui.css'),
        'js incluido' => str_contains($html, '/js/app.js'),
        'sin fugas de código' => preg_match('/<\?php|extract\(\$__ctx/', $html) !== 1,
    ];

    $errors = array_filter($checks, static fn (bool $ok): bool => ! $ok);
    if ($errors !== []) {
        printf("FAIL %s: %s\n", $template, implode(', ', array_keys($errors)));
        ++$failed;

        continue;
    }

    printf("OK   %-22s %6d bytes\n", $template, strlen($html));
}

exit($failed === 0 ? 0 : 1);