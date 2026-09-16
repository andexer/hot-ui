<?php

declare(strict_types=1);

/**
 * Showcase de componentes — página principal de la demo.
 *
 * Estilo streaming: open/close con el nombre de archivo; close() devuelve el
 * HTML y se imprime o asigna.
 */

// ── Card de acceso (ejemplo canónico) ────────────────────────────────────
ui()->open('card', ['class' => 'max-w-sm']);

    ui()->open('card-header');
        ui()->open('card-title');
            echo 'Bienvenido de nuevo';
        echo ui()->close();
        ui()->open('card-description');
            echo 'Inicia sesión para continuar.';
        echo ui()->close();
    echo ui()->close();

    ui()->open('card-content', ['class' => 'space-y-3']);
        echo ui()->input(['type' => 'email', 'placeholder' => 'm@example.com']);
        echo ui()->button(['class' => 'w-full'], 'Entrar');
    echo ui()->close();

$loginCard = ui()->close();

// ── Card con estilo alternativo (argumentos + slots-closure) ─────────────
// Misma pieza que arriba pero escrita al otro estilo: ambos producen HTML
// idéntico y pueden mezclarse libremente.
$helperCard = ui()->card(['class' => 'max-w-sm mt-4'], function () { ?>
    <?= ui()->cardHeader([], function () { ?>
        <?= ui()->cardTitle([], 'Ruta del helper') ?>
        <?= ui()->badge(['tone' => 'success'], 'activo') ?>
    <?php }) ?>
    <?= ui()->cardContent([], function () { ?>
        <?= ui()->alert(['tone' => 'info'], function () {
            echo ui()->alertTitle([], 'Atención');
            echo ui()->alertDescription([], 'Renderizado a través del helper ui().');
            echo ui()->alertAction([], '<button type="button" class="text-xs">Cerrar</button>');
        }) ?>
        <?= ui()->separator(['class' => 'my-4']) ?>
        <?= ui()->input([
            'type' => 'password',
            'name' => 'secret',
            'required' => true,
        ], leading: fn () => '<span>🔒</span>') ?>
    <?php }) ?>
<?php });

echo '<div class="demo-page flex flex-wrap gap-6">', $loginCard, $helperCard, '</div>';
