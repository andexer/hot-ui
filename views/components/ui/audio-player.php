<?php

declare(strict_types=1);

extract(props($__ctx, [
    'src' => null,
    'title' => null,
    'artist' => null,
    'autoplay' => false,
]));

$srcUrl = safe_url(is_string($src) ? $src : null);
?>
<div
    data-slot="audio-player"
    x-data="{
        playing: false,
        muted: <?= js((bool) $autoplay) ?>,
        currentTime: 0,
        duration: 0,
        volume: 1,
        get progress() { return this.duration > 0 ? (this.currentTime / this.duration) * 100 : 0 },
        get volumePercent() { return (this.muted ? 0 : this.volume) * 100 },
        fmt(s) {
            if (!isFinite(s) || s < 0) s = 0;
            const m = Math.floor(s / 60);
            const sec = Math.floor(s % 60);
            return m + ':' + String(sec).padStart(2, '0');
        },
        toggle() {
            const a = this.$refs.audio;
            if (a.paused) { a.play(); } else { a.pause(); }
        },
        seek(v) {
            const a = this.$refs.audio;
            if (this.duration > 0) { a.currentTime = (v / 100) * this.duration; }
        },
        setVolume(v) {
            const a = this.$refs.audio;
            this.volume = v / 100;
            this.muted = this.volume === 0;
            a.muted = this.muted;
            a.volume = this.volume;
        },
        toggleMute() {
            const a = this.$refs.audio;
            this.muted = !this.muted;
            a.muted = this.muted;
        },
    }"
    <?= $attributes->twMerge('bg-card text-card-foreground flex w-full max-w-md flex-col gap-3 rounded-xl border p-4 shadow-sm') ?>
>
    <audio
        x-ref="audio"
<?php if ($src && $srcUrl !== null): ?>
        src="<?= e($srcUrl) ?>"
<?php endif; ?>
        preload="metadata"
<?php if ($autoplay): ?>
        autoplay muted
<?php endif; ?>
        class="sr-only"
        tabindex="-1"
        aria-hidden="true"
        @loadedmetadata="duration = $refs.audio.duration; volume = $refs.audio.volume"
        @timeupdate="currentTime = $refs.audio.currentTime"
        @play="playing = true"
        @pause="playing = false"
        @ended="playing = false; currentTime = $refs.audio.duration"
        @volumechange="volume = $refs.audio.volume; muted = $refs.audio.muted"
    ></audio>

<?php if ($title || $artist): ?>
    <div data-slot="audio-player-meta" class="flex flex-col gap-0.5">
<?php if ($title): ?>
        <span class="truncate text-sm font-medium"><?= e($title) ?></span>
<?php endif; ?>
<?php if ($artist): ?>
        <span class="text-muted-foreground truncate text-xs"><?= e($artist) ?></span>
<?php endif; ?>
    </div>
<?php endif; ?>

    <div data-slot="audio-player-controls" class="flex items-center gap-3">
        <button
            type="button"
            @click="toggle()"
            :aria-label="playing ? 'Pause' : 'Play'"
            :aria-pressed="playing"
            class="bg-primary text-primary-foreground inline-flex size-9 shrink-0 items-center justify-center rounded-full shadow-xs transition-all outline-none hover:bg-primary/90 focus-visible:ring-ring/50 focus-visible:ring-[3px]"
        >
            <i data-lucide="pause" x-show="playing" class="size-4 fill-current" aria-hidden="true"></i>
            <i data-lucide="play" x-show="!playing" x-cloak class="size-4 fill-current" aria-hidden="true"></i>
        </button>

        <span data-slot="audio-player-time" class="text-muted-foreground w-10 text-end text-xs tabular-nums" x-text="fmt(currentTime)">0:00</span>

        <input
            type="range"
            min="0"
            max="100"
            step="0.1"
            aria-label="Seek"
            :value="progress"
            @input="seek($event.target.value)"
            class="bg-muted h-1.5 grow min-w-0 cursor-pointer appearance-none rounded-full outline-none focus-visible:ring-ring/50 focus-visible:ring-[3px] [&::-webkit-slider-thumb]:size-3.5 [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:border [&::-webkit-slider-thumb]:border-primary [&::-webkit-slider-thumb]:bg-background [&::-webkit-slider-thumb]:shadow-sm [&::-moz-range-thumb]:size-3.5 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:border [&::-moz-range-thumb]:border-primary [&::-moz-range-thumb]:bg-background"
            :style="`background-image: linear-gradient(to right, var(--color-primary) ${progress}%, transparent ${progress}%)`"
        >

        <span data-slot="audio-player-duration" class="text-muted-foreground w-10 text-xs tabular-nums" x-text="fmt(duration)">0:00</span>

        <button
            type="button"
            @click="toggleMute()"
            :aria-label="muted ? 'Unmute' : 'Mute'"
            :aria-pressed="muted"
            class="text-muted-foreground hover:text-foreground inline-flex size-8 shrink-0 items-center justify-center rounded-md transition-colors outline-none focus-visible:ring-ring/50 focus-visible:ring-[3px]"
        >
            <i data-lucide="volume-x" x-show="muted" x-cloak class="size-4" aria-hidden="true"></i>
            <i data-lucide="volume-2" x-show="!muted" class="size-4" aria-hidden="true"></i>
        </button>

        <input
            type="range"
            min="0"
            max="100"
            step="1"
            aria-label="Volume"
            :value="volumePercent"
            @input="setVolume($event.target.value)"
            class="bg-muted hidden h-1.5 w-16 shrink-0 cursor-pointer appearance-none rounded-full outline-none focus-visible:ring-ring/50 focus-visible:ring-[3px] sm:block [&::-webkit-slider-thumb]:size-3 [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:border [&::-webkit-slider-thumb]:border-primary [&::-webkit-slider-thumb]:bg-background [&::-webkit-slider-thumb]:shadow-sm [&::-moz-range-thumb]:size-3 [&::-moz-range-thumb]:rounded-full [&::-moz-range-thumb]:border [&::-moz-range-thumb]:border-primary [&::-moz-range-thumb]:bg-background"
            :style="`background-image: linear-gradient(to right, var(--color-primary) ${volumePercent}%, transparent ${volumePercent}%)`"
        >
    </div>
</div>
