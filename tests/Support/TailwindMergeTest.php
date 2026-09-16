<?php

declare(strict_types=1);

namespace Components\Tests\Support;

use Components\Support\TailwindMerge;
use PHPUnit\Framework\TestCase;

final class TailwindMergeTest extends TestCase
{
    public function testLaterClassWinsWithinSameGroup(): void
    {
        self::assertSame('p-8', TailwindMerge::merge('p-4 p-8'));
        self::assertSame('text-sm', TailwindMerge::merge('text-lg text-sm'));
    }

    public function testIndependentAxesDoNotConflict(): void
    {
        self::assertSame('px-4 py-3', TailwindMerge::merge('px-4 py-3'));
        self::assertSame('gap-x-2 gap-y-4', TailwindMerge::merge('gap-x-2 gap-y-4'));
        self::assertSame('space-x-1 space-y-2', TailwindMerge::merge('space-x-1 space-y-2'));

        $out = TailwindMerge::merge('mt-2 mx-auto');
        self::assertStringContainsString('mt-2', $out);
        self::assertStringContainsString('mx-auto', $out);
    }

    public function testPaddingAxisOverridesAllOnlyOnItsAxis(): void
    {
        // p-6 sets both axes; px-4 overrides x only — both survive.
        $out = TailwindMerge::merge('p-6 px-4');
        self::assertStringContainsString('p-6', $out);
        self::assertStringContainsString('px-4', $out);

        // Same axis: later wins.
        self::assertSame('px-2', TailwindMerge::merge('px-4 px-2'));
    }

    public function testVariantsAreIsolatedNamespaces(): void
    {
        $out = TailwindMerge::merge('p-2 hover:p-4 md:p-6 dark:hover:p-8');

        foreach (['p-2', 'hover:p-4', 'md:p-6', 'dark:hover:p-8'] as $token) {
            self::assertStringContainsString($token, $out);
        }

        self::assertSame('hover:px-2', TailwindMerge::merge('hover:px-4 hover:px-2'));
    }

    public function testBracketAwareVariantSplitting(): void
    {
        $out = TailwindMerge::merge("has-[>svg]:px-3 [&_svg:not([class*='size-'])]:size-4 size-9");

        self::assertStringContainsString('has-[>svg]:px-3', $out);
        self::assertStringContainsString("[&_svg:not([class*='size-'])]:size-4", $out);
        self::assertStringContainsString('size-9', $out);

        // Arbitrary variant with a colon inside brackets stays one chain.
        self::assertStringContainsString('[&[data-open]]:p-2', TailwindMerge::merge('[&[data-open]]:p-1 [&[data-open]]:p-2'));
    }

    public function testNegativeAndImportantMarkers(): void
    {
        self::assertSame('mt-4', TailwindMerge::merge('-mt-2 mt-4'));
        self::assertSame('!p-2', TailwindMerge::merge('!p-8 !p-2'));
    }

    public function testFontSizeVersusColorAreDifferentGroups(): void
    {
        $out = TailwindMerge::merge('text-sm text-red-500 text-center');
        self::assertStringContainsString('text-sm', $out);
        self::assertStringContainsString('text-red-500', $out);
        self::assertStringContainsString('text-center', $out);

        self::assertSame('text-base', TailwindMerge::merge('text-xs text-base'));
        self::assertSame('text-destructive/90', TailwindMerge::merge('text-muted-foreground text-destructive/90'));
    }

    public function testBorderWidthVersusColor(): void
    {
        $out = TailwindMerge::merge('border border-input border-b last:border-b-0');
        self::assertStringContainsString('border ', ' '.$out.' ');
        self::assertStringContainsString('border-input', $out);
        self::assertStringContainsString('border-b', $out);

        self::assertSame('border-2', TailwindMerge::merge('border border-2'));
    }

    public function testUnknownUtilitiesNeverConflictButDeduplicate(): void
    {
        self::assertSame('foo-bar baz', TailwindMerge::merge('foo-bar foo-bar baz'));
    }

    public function testArbitraryPropertyUtilitiesAreUnique(): void
    {
        self::assertSame('[mask-type:luminance]', TailwindMerge::merge('[mask-type:luminance] [mask-type:luminance]'));
    }

    public function testOrderPreservedByFirstOccurrence(): void
    {
        self::assertSame('a p-8 b', TailwindMerge::merge('a p-4 b p-8'));
    }
}
