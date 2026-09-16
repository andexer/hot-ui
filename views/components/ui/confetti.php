<?php

declare(strict_types=1);

extract(props($__ctx, [
    'count' => 80,
    'spread' => 70,
    'colors' => null,
    'direction' => null,
    'spreadArc' => 90,
    'fullscreen' => false,
]));

$palette = is_array($colors) && count($colors)
    ? array_values($colors)
    : ['#6366f1', '#ec4899', '#f59e0b', '#22d3ee', '#34d399', '#a855f7', '#fb7185', '#fbbf24'];
?>
<?php if (! isset($GLOBALS['__hot_confetti_styles_emitted'])): ?>
<?php $GLOBALS['__hot_confetti_styles_emitted'] = true; ?>
    <style>
        @keyframes hot-confetti-fall {
            0% {
                opacity: 1;
                transform: translate3d(0, 0, 0) rotate(0deg);
            }
            100% {
                opacity: 0;
                transform: translate3d(var(--hot-confetti-x), var(--hot-confetti-y), 0) rotate(var(--hot-confetti-rot));
            }
        }
        [data-slot="confetti"] .hot-confetti-piece {
            position: absolute;
            top: 0;
            left: 0;
            width: var(--hot-confetti-size, 8px);
            height: calc(var(--hot-confetti-size, 8px) * 0.4);
            border-radius: 1px;
            will-change: transform, opacity;
            animation: hot-confetti-fall var(--hot-confetti-dur, 1100ms) cubic-bezier(0.16, 1, 0.3, 1) var(--hot-confetti-delay, 0ms) forwards;
        }
        @media (prefers-reduced-motion: reduce) {
            [data-slot="confetti"] .hot-confetti-piece {
                animation: none;
                display: none;
            }
        }
    </style>
<?php endif; ?>
<span
    data-slot="confetti"
    x-data="{
        palette: <?= js($palette) ?>,
        count: <?= js((int) $count) ?>,
        spread: <?= js((float) $spread) ?>,
        direction: <?= js($direction === null ? null : (float) $direction) ?>,
        spreadArc: <?= js((float) $spreadArc) ?>,
        fullscreen: <?= js((bool) $fullscreen) ?>,
        reduced: false,
        init() {
            this.reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        },
        fire() {
            if (this.reduced) return;
            const overlay = this.$refs.overlay;
            if (! overlay) return;
            const rect = this.$el.getBoundingClientRect();
            const hasDir = this.direction !== null;
            const arc = (this.spreadArc * Math.PI) / 180;
            
            const base = hasDir ? (this.direction * Math.PI / 180) : 0;
            for (let i = 0; i < this.count; i++) {
                const piece = document.createElement('span');
                piece.className = 'hot-confetti-piece';

                let originX, originY, vx, vy, fallY;
                if (this.fullscreen) {
                    
                    originX = Math.random() * window.innerWidth;
                    originY = -20;
                    vx = (Math.random() - 0.5) * this.spread * 1.5;
                    fallY = window.innerHeight + 40 + Math.random() * 120;
                } else {
                    originX = rect.left + rect.width / 2;
                    originY = rect.top + rect.height / 2;
                    const velocity = this.spread + Math.random() * this.spread;
                    if (hasDir) {
                        
                        const t = this.count > 1 ? i / (this.count - 1) : 0.5;
                        const angle = base - arc / 2 + arc * t + (Math.random() - 0.5) * 0.2;
                        vx = Math.cos(angle) * velocity;
                        vy = -Math.sin(angle) * velocity;
                    } else {
                        
                        const angle = (Math.PI * 2 * i) / this.count + (Math.random() - 0.5) * 0.6;
                        vx = Math.cos(angle) * velocity;
                        vy = Math.sin(angle) * velocity - (this.spread * 0.8 + Math.random() * this.spread);
                    }
                    fallY = vy + 320 + Math.random() * 160;
                }

                const dur = this.fullscreen ? (1600 + Math.random() * 1400) : (900 + Math.random() * 900);
                const delay = (this.fullscreen ? 400 : 120) * Math.random();
                const size = 6 + Math.random() * 8;
                const rot = (Math.random() * 720 - 360) + 'deg';
                const color = this.palette[i % this.palette.length];
                piece.style.left = originX + 'px';
                piece.style.top = originY + 'px';
                piece.style.background = color;
                piece.style.setProperty('--hot-confetti-x', vx.toFixed(2) + 'px');
                piece.style.setProperty('--hot-confetti-y', fallY.toFixed(2) + 'px');
                piece.style.setProperty('--hot-confetti-rot', rot);
                piece.style.setProperty('--hot-confetti-dur', dur + 'ms');
                piece.style.setProperty('--hot-confetti-delay', delay + 'ms');
                piece.style.setProperty('--hot-confetti-size', size.toFixed(1) + 'px');
                if (Math.random() > 0.6) piece.style.borderRadius = '50%';
                overlay.appendChild(piece);
                setTimeout(() => piece.remove(), dur + delay + 80);
            }
        },
    }"
    <?= $attributes->twMerge('relative inline-flex') ?>
>
    <?php if ($slot->isNotEmpty()): ?>
        <span @click="fire()" class="contents"><?= $slot ?></span>
    <?php else: ?>
        <?= $this->uiButton(['type' => 'button', '@click' => 'fire()'], 'Celebrate &#127881;') ?>
    <?php endif; ?>

    <span
        x-ref="overlay"
        aria-hidden="true"
        class="pointer-events-none fixed inset-0 z-[9999] overflow-hidden"
    ></span>
</span>
