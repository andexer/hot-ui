<?php

declare(strict_types=1);

/**
 * Smoke test: renders every registered component with default props and
 * reports failures. Complex components can declare minimal payloads here.
 *
 *   composer smoke            (or: php bin/smoke.php)
 *   php bin/smoke.php uiCard  (filter by substring)
 */

use Components\Ui;

require dirname(__DIR__).'/vendor/autoload.php';

/**
 * Minimal sensible payloads for components that require data to render.
 *
 * @var array<string, array<string, mixed>> $PAYLOADS
 */
$PAYLOADS = [
    // filled progressively during migration verification
];

$filter = $argv[1] ?? null;

$ui = Ui::new(dirname(__DIR__).'/views');

$names = $ui->componentNames();
sort($names);

$passed = 0;
$failed = [];

foreach ($names as $name) {
    if ($filter !== null && ! str_contains($name, $filter)) {
        continue;
    }

    try {
        $args = [$PAYLOADS[$name] ?? []];
        $html = $ui->renderComponent($name, $args);
        if (! is_string($html)) {
            throw new RuntimeException('Renderer returned non-string');
        }
        ++$passed;
    } catch (Throwable $e) {
        $failed[$name] = $e->getMessage();
    }
}

$total = $passed + count($failed);
printf("\nSmoke: %d/%d rendered (%d failed)\n", $passed, $total, count($failed));

foreach ($failed as $name => $message) {
    printf("  FAIL %s: %s\n", $name, strtok($message, "\n"));
}

exit($failed === [] ? 0 : 1);
