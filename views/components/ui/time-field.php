<?php

declare(strict_types=1);

extract(props($__ctx, [
    'name' => null,
    'value' => null,
    'variant' => 'input',
    'hourCycle' => 'auto',
    'seconds' => false,
    'minuteStep' => 1,
    'secondStep' => 1,
    'min' => null,
    'max' => null,
    'disabled' => false,
    'id' => null,
    'part' => null,
]));

$step = $seconds ? max(1, (int) $secondStep) : ((int) $minuteStep > 1 ? (int) $minuteStep * 60 : null);

$selCls = 'appearance-none border-input bg-background dark:bg-input/30 h-9 rounded-md border ps-2.5 pe-7 text-sm shadow-xs outline-none transition-[color,box-shadow] focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] disabled:opacity-50 disabled:pointer-events-none';

$ariaLabel = $attributes->get('aria-label');
$attributes = $attributes->except('aria-label');

?>
<div
    data-slot="time-field"
x-data="hotTimeField({
        model: $hot.model(<?= js($value) ?>),
        cycle: <?= js($hourCycle) ?>,
        seconds: <?= js((bool) $seconds) ?>,
        part: <?= js($part) ?>,
        minStep: <?= max(1, (int) $minuteStep) ?>,
        secStep: <?= max(1, (int) $secondStep) ?>,
    })"
    <?= $attributes->twMerge('inline-flex items-center gap-1.5') ?>
>
    <?php if ($name): ?>
        <input type="hidden" name="<?= e($name) ?>" :value="value ?? ''">
    <?php endif; ?>

    <?php if ($variant === 'select'): ?>
        <div class="relative" x-show="cyc !== '12'">
            <select aria-label="Hour"<?php if ($disabled): ?> disabled<?php endif; ?> class="<?= e($selCls) ?>" @change="setH($event.target.value)">
                <option value="" :selected="h === null" disabled hidden>--</option>
                <template x-for="o in hourOpts" :key="o">
                    <option :value="o" :selected="h === o" x-text="pad(o)"></option>
                </template>
            </select>
            <i data-lucide="chevron-down" class="pointer-events-none absolute end-2 top-1/2 size-3.5 -translate-y-1/2 opacity-50" aria-hidden="true"></i>
        </div>

        <div class="relative" x-show="cyc === '12'">
            <select aria-label="Hour"<?php if ($disabled): ?> disabled<?php endif; ?> class="<?= e($selCls) ?>" @change="setH12($event.target.value)">
                <option value="" :selected="h === null" disabled hidden>--</option>
                <template x-for="o in hourOpts" :key="o">
                    <option :value="o" :selected="hour12 === o" x-text="pad(o)"></option>
                </template>
            </select>
            <i data-lucide="chevron-down" class="pointer-events-none absolute end-2 top-1/2 size-3.5 -translate-y-1/2 opacity-50" aria-hidden="true"></i>
        </div>

        <span class="text-muted-foreground">:</span>

        <div class="relative">
            <select aria-label="Minute"<?php if ($disabled): ?> disabled<?php endif; ?> class="<?= e($selCls) ?>" @change="setM($event.target.value)">
                <option value="" :selected="m === null" disabled hidden>--</option>
                <template x-for="o in minOpts" :key="o">
                    <option :value="o" :selected="m === o" x-text="pad(o)"></option>
                </template>
            </select>
            <i data-lucide="chevron-down" class="pointer-events-none absolute end-2 top-1/2 size-3.5 -translate-y-1/2 opacity-50" aria-hidden="true"></i>
        </div>

        <?php if ($seconds): ?>
            <span class="text-muted-foreground">:</span>
            <div class="relative">
                <select aria-label="Second"<?php if ($disabled): ?> disabled<?php endif; ?> class="<?= e($selCls) ?>" @change="setS($event.target.value)">
                    <option value="" :selected="h === null" disabled hidden>--</option>
                    <template x-for="o in secOpts" :key="o">
                        <option :value="o" :selected="s === o" x-text="pad(o)"></option>
                    </template>
                </select>
                <i data-lucide="chevron-down" class="pointer-events-none absolute end-2 top-1/2 size-3.5 -translate-y-1/2 opacity-50" aria-hidden="true"></i>
            </div>
        <?php endif; ?>

        <div class="relative" x-show="cyc === '12'">
            <select aria-label="AM or PM"<?php if ($disabled): ?> disabled<?php endif; ?> class="<?= e($selCls) ?>" @change="setPeriod($event.target.value)">
                <option value="AM" :selected="period === 'AM'">AM</option>
                <option value="PM" :selected="period === 'PM'">PM</option>
            </select>
            <i data-lucide="chevron-down" class="pointer-events-none absolute end-2 top-1/2 size-3.5 -translate-y-1/2 opacity-50" aria-hidden="true"></i>
        </div>
    <?php else: ?>
        <?= $this->uiInput([
            'type' => 'time',
            'id' => $id,
            'step' => $step,
            'min' => $min,
            'max' => $max,
            'disabled' => $disabled,
            'aria-label' => $ariaLabel,
            'x-bind:value' => 'value',
            'x-on:input' => 'fromInput($event.target.value)',
            'class' => 'bg-background w-auto [&::-webkit-calendar-picker-indicator]:hidden',
        ]) ?>
    <?php endif; ?>
</div>
