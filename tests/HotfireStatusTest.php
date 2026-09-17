<?php

declare(strict_types=1);

namespace Components\Tests;

use Components\Ci4\Http\HotfireStatus;
use Components\Exception\ComponentNotFoundException;
use Components\Support\Exception\DirectoryCreateException;
use Components\Support\Exception\TemplateNotFoundException;
use Components\Support\Exception\UnbalancedTagException;
use Components\Hotfire\Exception\InvalidActionException;
use Components\Hotfire\Exception\InvalidComponentNameException;
use Components\Hotfire\Exception\InvalidSnapshotException;
use Components\Hotfire\Exception\MissingSnapshotKeyException;
use Components\Hotfire\Exception\UnknownComponentException;
use Components\Hotfire\Snapshot;
use Components\Hotfire\SnapshotFailure;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The endpoint tells the driver the truth about a failure with a status, so the
 * mapping is pinned here: this class is framework-free on purpose, which makes
 * the whole contract checkable without a CodeIgniter application.
 */
final class HotfireStatusTest extends TestCase
{
    /**
     * @return array<string, array{0: \Throwable, 1: int}>
     */
    public static function failures(): array
    {
        return [
            'malformed snapshot' => [InvalidSnapshotException::malformed(), 422],
            'tampered snapshot' => [InvalidSnapshotException::checksumMismatch(), 422],
            'unknown snapshot version' => [InvalidSnapshotException::notValid(), 422],
            'oversized snapshot' => [InvalidSnapshotException::tooLarge(70000), 413],
            'component not registered' => [new UnknownComponentException('App\\Components\\Gone'), 404],
            'component not found' => [new ComponentNotFoundException('ui.gone'), 404],
            'reserved action' => [InvalidActionException::reservedMethod('App\\Components\\Counter', 'mount'), 422],
            'non-public action' => [InvalidActionException::notAPublicMethod('App\\Components\\Counter', 'booted'), 422],
            'property outside state' => [InvalidActionException::notAStateProperty('App\\Components\\Counter', 'secret'), 422],
            'garbled component name' => [new InvalidComponentNameException('../etc/passwd', 'it traverses'), 422],
            'no signing key' => [new MissingSnapshotKeyException(), 500],
            'template will not compile' => [UnbalancedTagException::unclosed('ui.card'), 500],
            'template missing' => [TemplateNotFoundException::forTemplate('ui.card', '/views/ui/card.php'), 500],
            'cache not writable' => [new DirectoryCreateException('/var/cache/hotui'), 500],
            'unexpected bug' => [new \TypeError('renderer returned null'), 500],
            'unexpected runtime error' => [new \RuntimeException('boom'), 500],
        ];
    }

    #[DataProvider('failures')]
    public function testEachFailureGetsTheStatusThatDescribesIt(\Throwable $exception, int $expected): void
    {
        self::assertSame($expected, HotfireStatus::for($exception), $exception::class.' → '.$expected);
    }

    public function testSnapshotFailuresAreDistinguishedByKindNotByMessage(): void
    {
        $tooLarge = InvalidSnapshotException::tooLarge(65537);
        $tampered = InvalidSnapshotException::checksumMismatch();

        self::assertSame(SnapshotFailure::TooLarge, $tooLarge->getFailure());
        self::assertSame(SnapshotFailure::ChecksumMismatch, $tampered->getFailure());
        self::assertSame(413, HotfireStatus::for($tooLarge));
        self::assertSame(422, HotfireStatus::for($tampered));
    }

    public function testReasonStaysHumanReadableForLogs(): void
    {
        self::assertSame('70000 bytes exceed the size limit', InvalidSnapshotException::tooLarge(70000)->getReason());
        self::assertSame('checksum mismatch', InvalidSnapshotException::checksumMismatch()->getReason());
        self::assertSame('malformed payload', InvalidSnapshotException::malformed()->getReason());
        self::assertSame('payload not valid', InvalidSnapshotException::notValid()->getReason());
    }

    public function testOversizedSnapshotFromTheWireMapsTo413(): void
    {
        // End to end through the real decoder, which is what the endpoint calls.
        try {
            Snapshot::decode(['payload' => str_repeat('a', 70000), 'checksum' => 'nope'], 'suite-key');
            self::fail('an oversized snapshot was accepted');
        } catch (InvalidSnapshotException $exception) {
            self::assertSame(SnapshotFailure::TooLarge, $exception->getFailure());
            self::assertSame(413, HotfireStatus::for($exception));
        }
    }

    public function testTamperedSnapshotFromTheWireMapsTo422(): void
    {
        try {
            Snapshot::decode(['payload' => base64_encode('{"v":1,"s":[]}'), 'checksum' => 'forged'], 'suite-key');
            self::fail('a tampered snapshot was accepted');
        } catch (InvalidSnapshotException $exception) {
            self::assertSame(SnapshotFailure::ChecksumMismatch, $exception->getFailure());
            self::assertSame(422, HotfireStatus::for($exception));
        }
    }

    public function testTheMapperOnlyEverAnswersWithAFailureStatus(): void
    {
        foreach (self::failures() as [$exception]) {
            self::assertContains(HotfireStatus::for($exception), [404, 413, 422, 500], $exception::class);
        }

        // Anything the package does not know about is a server fault, never a
        // status that blames the client's request.
        self::assertSame(500, HotfireStatus::for(new \Error('allowed memory exhausted')));
        self::assertSame(500, HotfireStatus::for(new \LogicException('impossible state')));
    }
}
