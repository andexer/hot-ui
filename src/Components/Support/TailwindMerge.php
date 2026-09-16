<?php

declare(strict_types=1);

namespace Components\Support;

/**
 * Tailwind class conflict resolver.
 *
 * Splits every token into [variant-chain, utility], classifies the utility
 * into a conflict group, then lets later tokens override earlier ones within
 * the same group + variant chain. Unknown utilities never conflict. Handles
 * variants (hover:, md:, dark:, aria-*:, data-*:, arbitrary [&...]:), the
 * important marker (!), negatives (-) and bracket-aware parsing, with static
 * memoisation per input string.
 */
final class TailwindMerge
{
    /** @var array<string, string> */
    private static array $memo = [];

    /**
     * Ordered classification rules; first match wins, so specific groups must
     * precede generic catch-alls (e.g. font-size before text-color).
     *
     * @var list<array{0: non-empty-string, 1: string}>
     */
    private const RULES = [
        ['/^text-(?:xs|sm|base|lg|xl|[2-9]xl|\[[^\]]+\])$/', 'font-size'],
        ['/^text-(?:left|center|right|justify|start|end)$/', 'text-align'],
        ['/^text-(?:underline|overline|line-through|no-underline)$/', 'text-decoration-line'],
        ['/^underline-offset-/', 'underline-offset'],
        ['/^decoration-(?:solid|dashed|dotted|double|none|from-font)$/', 'text-decoration-style'],
        ['/^leading-/', 'leading'],
        ['/^tracking-/', 'tracking'],
        ['/^whitespace-/', 'whitespace'],
        ['/^break-(?:normal|words|all|keep-all|inside-page)/', 'word-break'],
        ['/^(?:truncate|text-ellipsis|text-clip)$/', 'text-overflow'],

        ['/^font-(?:thin|extralight|light|normal|medium|semibold|bold|extrabold|black|\[[^\]]+\])$/', 'font-weight'],
        ['/^font-(?:sans|serif|mono|\[[^\]]+\])$/', 'font-family'],
        ['/^(?:italic|not-italic)$/', 'font-style'],
        ['/^(?:uppercase|lowercase|capitalize|normal-case)$/', 'text-transform'],

        ['/^bg-(?:top|bottom|center|left|right)(?:-(?:top|bottom|left|right))?$/', 'bg-position'],
        ['/^bg-(?:cover|contain|\[[^\]]+\])$/', 'bg-size'],
        ['/^bg-(?:no-repeat|repeat(?:-x|-y|-round|-space)?)$/', 'bg-repeat'],
        ['/^bg-(?:none|gradient-to-\w+|linear-to-\w+|\[url|\[image)/', 'bg-image'],
        ['/^bg-/', 'bg-color'],

        ['/^rounded(?:(?:-t|-r|-b|-l|-tl|-tr|-br|-bl|-s|-e|-ss|-se|-es|-ee)(?:-none|-sm|-md|-lg|-xl|-2xl|-3xl|-4xl|-full)?|-none|-sm|-md|-lg|-xl|-2xl|-3xl|-4xl|-full)?$/', 'radius'],
        ['/^border-(?:solid|dashed|dotted|double|hidden|none)$/', 'border-style'],
        ['/^divide-(?:x|y)(?:-\d+(?:\.\d+)?)?$/', 'divide-w'],
        ['/^divide-(?:solid|dashed|dotted|double|none)$/', 'divide-style'],
        ['/^divide-/', 'divide-color'],
        ['/^border-(?:t|r|b|l|s|e|x|y)(?:-\d+(?:\.\d+)?$|$)/', 'border-side-w'],
        ['/^border-(?:t|r|b|l|s|e|x|y)-\[[^\]]+\]$/', 'border-side-w-arb'],
        ['/^border(?:-\d+(?:\.\d+)?$|\[[^\]]+\]?$|$)/', 'border-w'],
        ['/^outline-(?:none|hidden|dashed|dotted|double|solid)$/', 'outline-style'],
        ['/^outline-(?:offset-)?(?:\d+(?:\.\d+)?|\[[^\]]+\])$/', 'outline-w'],
        ['/^ring-offset(?:-\d+(?:\.\d+)?)?$/', 'ring-offset-w'],
        ['/^ring(?:(?:-\d+(?:\.\d+)?)|(?:-\[[^\]]+\]))?$/', 'ring-w'],
        ['/^shadow(?:-(?:2xs|xs|sm|md|lg|xl|2xl|none|inner)|\[[^\]]*\])?$/', 'shadow'],
        ['/^inset-shadow(?:-\w+|\[[^\]]*\])?$/', 'inset-shadow'],
        ['/^drop-shadow(?:-\w+|\[[^\]]*\])?$/', 'drop-shadow'],

        ['/^p(?:x|\[[^\]]+\]$)/', 'padding-x'],
        ['/^py-/', 'padding-y'],
        ['/^ps-/', 'padding-start'],
        ['/^pe-/', 'padding-end'],
        ['/^pt-/', 'padding-top'],
        ['/^pr-/', 'padding-right'],
        ['/^pb-/', 'padding-bottom'],
        ['/^pl-/', 'padding-left'],
        ['/^p(?:\[|-)/', 'padding'],
        ['/^m(?:x|\[[^\]]+\]$)/', 'margin-x'],
        ['/^my-/', 'margin-y'],
        ['/^ms-/', 'margin-start'],
        ['/^me-/', 'margin-end'],
        ['/^mt-/', 'margin-top'],
        ['/^mr-/', 'margin-right'],
        ['/^mb-/', 'margin-bottom'],
        ['/^ml-/', 'margin-left'],
        ['/^m(?:\[|-)/', 'margin'],

        ['/^gap-x-/', 'gap-x'],
        ['/^gap-y-/', 'gap-y'],
        ['/^gap-/', 'gap'],
        ['/^space-x-/', 'space-x'],
        ['/^space-y-/', 'space-y'],

        ['/^w-/', 'width'],
        ['/^min-w-/', 'min-width'],
        ['/^max-w-/', 'max-width'],
        ['/^h-/', 'height'],
        ['/^min-h-/', 'min-height'],
        ['/^max-h-/', 'max-height'],
        ['/^size-/', 'size'],
        ['/^basis-/', 'flex-basis'],
        ['/^grow(?:-\d+(?:\.\d+)?)?$/', 'flex-grow'],
        ['/^shrink(?:-\d+(?:\.\d+)?)?$/', 'flex-shrink'],
        ['/^flex-(?:row|row-reverse|col|col-reverse)$/', 'flex-direction'],
        ['/^flex-(?:wrap|wrap-reverse|nowrap)$/', 'flex-wrap'],
        ['/^flex-(?:1|auto|initial|none|\[\S+\])?$/', 'flex'],
        ['/^order-/', 'order'],

        ['/^grid-cols-/', 'grid-cols'],
        ['/^grid-rows-/', 'grid-rows'],
        ['/^grid-flow-/', 'grid-flow'],
        ['/^col-span-/', 'col-span'],
        ['/^col-start-/', 'col-start'],
        ['/^col-end-/', 'col-end'],
        ['/^row-span-/', 'row-span'],
        ['/^row-start-/', 'row-start'],
        ['/^auto-cols-/', 'auto-cols'],
        ['/^auto-rows-/', 'auto-rows'],

        ['/^justify-items-/', 'justify-items'],
        ['/^justify-self-/', 'justify-self'],
        ['/^justify-(?:normal|start|end|center|between|around|evenly|stretch|safe|unsafe)/', 'justify-content'],
        ['/^items-/', 'align-items'],
        ['/^content-/', 'align-content'],
        ['/^self-/', 'align-self'],
        ['/^place-items-/', 'place-items'],
        ['/^place-content-/', 'place-content'],
        ['/^place-self-/', 'place-self'],

        ['/^aspect-/', 'aspect-ratio'],
        ['/^object-(?:contain|cover|fill|none|scale-down)$/', 'object-fit'],
        ['/^object-/', 'object-position'],
        ['/^overflow-x-/', 'overflow-x'],
        ['/^overflow-y-/', 'overflow-y'],
        ['/^overflow-/', 'overflow'],
        ['/^overscroll-/', 'overscroll'],
        ['/^(?:static|fixed|absolute|relative|sticky)$/', 'position'],
        ['/^(?:block|inline-block|inline|flex|inline-flex|table|inline-table|table-row|table-cell|grid|inline-grid|contents|flow-root|list-item|hidden)$/', 'display'],
        ['/^(?:float|clear)-(?:left|right|none|start|end|both)$/', 'float'],
        ['/^inset-x-/', 'inset-x'],
        ['/^inset-y-/', 'inset-y'],
        ['/^inset-/', 'inset'],
        ['/^start-/', 'start'],
        ['/^end-/', 'end'],
        ['/^top-/', 'top'],
        ['/^right-/', 'right'],
        ['/^bottom-/', 'bottom'],
        ['/^left-/', 'left'],
        ['/^z-/', 'z-index'],

        ['/^translate-x-/', 'translate-x'],
        ['/^translate-y-/', 'translate-y'],
        ['/^translate-z-/', 'translate-z'],
        ['/^rotate-/', 'rotate'],
        ['/^scale-x-/', 'scale-x'],
        ['/^scale-y-/', 'scale-y'],
        ['/^scale-/', 'scale'],
        ['/^skew-x-/', 'skew-x'],
        ['/^skew-y-/', 'skew-y'],
        ['/^origin-/', 'transform-origin'],
        ['/^(?:transform|transform-(?:gpu|none))$/', 'transform'],

        ['/^transition-(?:all|colors|opacity|shadow|transform|property|duration|ease|delay)?$|^transition$/', 'transition-property'],
        ['/^duration-/', 'duration'],
        ['/^delay-/', 'delay'],
        ['/^ease-/', 'timing-function'],

        ['/^opacity-/', 'opacity'],
        ['/^blur(?:-\w+|\[[^\]]+\])?$/', 'blur'],
        ['/^brightness-/', 'brightness'],
        ['/^contrast-/', 'contrast'],
        ['/^grayscale(?:-\d*)?$/', 'grayscale'],
        ['/^invert(?:-\d*)?$/', 'invert'],
        ['/^sepia(?:-\d*)?$/', 'sepia'],
        ['/^saturate-/', 'saturation'],
        ['/^hue-rotate-/', 'hue-rotate'],
        ['/^(?:filter|filter-none)$/', 'filter'],
        ['/^backdrop-blur/', 'backdrop-blur'],
        ['/^(?:backdrop-filter|backdrop-filter-none)$/', 'backdrop-filter'],

        ['/^cursor-/', 'cursor'],
        ['/^select-/', 'user-select'],
        ['/^pointer-events-/', 'pointer-events'],
        ['/^resize(?:-x|-y|-none)?$/', 'resize'],
        ['/^(?:sr-only|not-sr-only)$/', 'screen-reader'],
        ['/^appearance-/', 'appearance'],
        ['/^accent-/', 'accent-color'],
        ['/^caret-/', 'caret-color'],
        ['/^scroll-m(?:x|y|s|e|t|r|b|l)-/', 'scroll-margin-axis'],
        ['/^scroll-p(?:x|y|s|e|t|r|b|l)-/', 'scroll-padding-axis'],
        ['/^scroll-m/', 'scroll-margin'],
        ['/^scroll-p/', 'scroll-padding'],
        ['/^snap-/', 'scroll-snap'],
        ['/^touch-/', 'touch-action'],
        ['/^will-change-/', 'will-change'],
        ['/^animate-/', 'animation'],
        ['/^columns-/', 'columns'],
        ['/^list-/', 'list'],
        ['/^placeholder-/', 'placeholder-color'],
        ['/^fill-/', 'fill-color'],
        ['/^stroke-(?:\d|\[[^\]]+\]|current)/', 'stroke-width'],
        ['/^stroke-/', 'stroke-color'],
        ['/^outline-/', 'outline-color'],
        ['/^ring-/', 'ring-color'],
        ['/^border-(?:t|r|b|l|s|e|x|y)-/', 'border-side-color'],
        ['/^border-/', 'border-color'],
        ['/^ring-offset-/', 'ring-offset-color'],
        ['/^text-/', 'text-color'],
    ];

    /**
     * Merges class strings, later conflicts winning.
     */
    public static function merge(string ...$inputs): string
    {
        $tokens = preg_split('/\s+/', implode(' ', $inputs), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if ($tokens === []) {
            return '';
        }

        $key = implode(' ', $tokens);

        return self::$memo[$key] ??= self::resolve($tokens);
    }

    /** Clears the static memoisation cache. */
    public static function flushMemo(): void
    {
        self::$memo = [];
    }

    /**
     * @param list<string> $tokens
     */
    private static function resolve(array $tokens): string
    {
        /** @var array<int|string, string> $values Last-wins token per key. */
        $values = [];
        /** @var list<int|string> $order First-seen key order. */
        $order = [];

        foreach ($tokens as $token) {
            [$variants, $utility] = self::splitVariant($token);

            $group = str_starts_with($utility, '[')
                ? null
                : self::classify($utility);

            $key = $group === null ? "\0{$variants}\0{$utility}" : "{$variants}|{$group}";
            if (! array_key_exists($key, $values)) {
                $order[] = $key;
            }
            $values[$key] = $token;
        }

        return implode(' ', array_map(static fn (string|int $k): string => $values[$k], $order));
    }

    /**
     * Splits a token into variant chain and final utility, ignoring colons
     * nested inside brackets or parentheses.
     *
     * @return array{0: string, 1: string}
     */
    private static function splitVariant(string $token): array
    {
        $depth = 0;
        $split = -1;
        $length = strlen($token);

        for ($i = 0; $i < $length; ++$i) {
            match ($token[$i]) {
                '[', '(' => ++$depth,
                ']', ')' => --$depth,
                ':' => $depth === 0 ? $split = $i : null,
                default => null,
            };
        }

        return $split === -1
            ? ['', $token]
            : [substr($token, 0, $split), substr($token, $split + 1)];
    }

    /**
     * Maps a utility to its conflict group id, or null when it never conflicts.
     */
    private static function classify(string $utility): ?string
    {
        if ($utility !== '' && ($utility[0] === '!' || $utility[0] === '-')) {
            $utility = substr($utility, 1);
        }

        foreach (self::RULES as [$pattern, $group]) {
            if (preg_match($pattern, $utility) === 1) {
                return $group;
            }
        }

        return null;
    }
}
