<?php

declare(strict_types=1);

extract(props($__ctx, [
    'name' => null,
    'height' => 200,
    'penColor' => null,
    'id' => null,
]));

$resolvedPen = $penColor ? "'".addslashes($penColor)."'" : 'null';

$hasModel = ($modelPath = $attributes->get('data-hot-model')) !== null;
?>
<div
    data-slot="signature-pad"
x-data="hotSignaturePad({
        pen: <?= $resolvedPen ?>,
        height: <?= js((int) $height) ?>,
    })"
    <?= $attributes->twMerge('text-foreground flex w-full flex-col gap-2') ?>
>
    <div class="border-input bg-background relative overflow-hidden rounded-md border">
        <div
            x-show="!hasInk"
            x-transition.opacity
            class="pointer-events-none absolute inset-x-0 bottom-0 flex flex-col items-center"
            aria-hidden="true"
        >
            <span class="text-muted-foreground mb-2 text-xs">Sign here</span>
            <span class="bg-border mb-7 h-px w-3/4"></span>
        </div>

        <canvas
            x-ref="canvas"
            aria-label="Signature pad — draw your signature"
            role="img"
            class="block w-full touch-none"
            :style="`height: ${height}px`"
            @pointerdown="start($event)"
            @pointermove="move($event)"
            @pointerup="end($event)"
            @pointercancel="end($event)"
            @pointerleave="end($event)"
        ></canvas>
    </div>

    <div class="flex items-center gap-2">
        <?= $this->uiButton(['type' => 'button', 'variant' => 'outline', 'size' => 'sm', 'x-on:click' => 'undo()', 'x-bind:disabled' => 'strokes.length === 0'], function (): void { ?>
            <i data-lucide="undo-2" aria-hidden="true"></i>
            Undo
        <?php }) ?>
        <?= $this->uiButton(['type' => 'button', 'variant' => 'ghost', 'size' => 'sm', 'x-on:click' => 'clear()', 'x-bind:disabled' => '!hasInk'], function (): void { ?>
            <i data-lucide="eraser" aria-hidden="true"></i>
            Clear
        <?php }) ?>
    </div>

    <?php if ($name || $hasModel): ?>
        <input type="hidden" x-ref="field" <?php if ($name): ?>name="<?= e($name) ?>"<?php endif; ?> <?php if ($hasModel): ?>data-hot-model="<?= e($modelPath) ?>"<?php endif; ?><?php if ($id): ?> id="<?= e($id) ?>"<?php endif; ?>>
    <?php endif; ?>
</div>
