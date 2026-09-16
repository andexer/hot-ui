<?php

declare(strict_types=1);

extract(props($__ctx, [
    'label' => 'Add to cart',
    'addedLabel' => 'Added',
    'size' => 'default',
    'icon' => 'shopping-cart',
]));

$busyText = $label !== false ? $label : 'Add to cart';

$busyJs = js($busyText);
$addedJs = js($addedLabel);

$slotContent = trim((string) $slot);

$xData = "{
        state: 'idle',
        announce: '',
        _t: [],
        add() {
            if (this.state !== 'idle') return;
            this.state = 'adding';
            this.announce = {$busyJs} + '…';
            this._t.push(setTimeout(() => this.done(), 900));
        },
        done() {
            this.state = 'added';
            this.announce = {$addedJs};
            this._t.push(setTimeout(() => this.reset(), 1600));
        },
        reset() {
            this.state = 'idle';
            this.announce = '';
        },
        destroy() { this._t.forEach(clearTimeout); },
    }";
?>
<span class="contents" data-slot="add-to-cart">
<?= $this->uiButton([
    'type' => 'button',
    'size' => $size,
    'x-data' => $xData,

    'x-bind:disabled' => "state !== 'idle'",
    'x-bind:aria-busy' => "state === 'adding'",

    'x-bind:style' => "state === 'added' ? '--primary: oklch(0.596 0.145 163.225); --primary-foreground: #ffffff;' : null",

    'x-bind:class' => "state === 'idle' ? 'cursor-pointer' : ''",
    '@click' => 'add()',

    'class' => trim(((string) ($attributes->get('class') ?? '')).' disabled:opacity-90'),
    ...$attributes->except('class')->all(),
], function () use ($slot, $slotContent, $label, $addedLabel, $icon): void { ?>
    <!-- Idle -->
    <span x-show="state === 'idle'" class="contents">
        <i data-lucide="<?= e($icon) ?>" aria-hidden="true"></i>
<?php if ($slotContent !== '' || $label !== false): ?>
        <span><?php if ($slotContent !== '') { echo $slot; } else { echo e($label); } ?></span>
<?php endif; ?>
    </span>

    <!-- Adding -->
    <span x-show="state === 'adding'" x-cloak class="contents">
        <i data-lucide="loader-circle" class="animate-spin" aria-hidden="true"></i>
<?php if ($slotContent !== '' || $label !== false): ?>
        <span><?= e($label) ?>…</span>
<?php endif; ?>
    </span>

    <!-- Added -->
    <span x-show="state === 'added'" x-cloak class="contents">
        <i data-lucide="check" aria-hidden="true"></i>
<?php if ($slotContent !== '' || $label !== false): ?>
        <span><?= e($addedLabel) ?></span>
<?php endif; ?>
    </span>

    <!-- Polite live region announces every transition to screen readers. -->
    <span class="sr-only" aria-live="polite" x-text="announce"></span>
<?php }) ?>
</span>
