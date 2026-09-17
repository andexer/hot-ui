<?php

declare(strict_types=1);

namespace Components\Tests;

use Components\Hotfire\ComponentGenerator;
use Components\Hotfire\ComponentPaths;
use Components\Hotfire\Config;
use Components\Hotfire\Engine;
use Components\Hotfire\Exception\ComponentWriteException;
use Components\Hotfire\Exception\HotfireException;
use Components\Hotfire\Exception\InvalidActionException;
use Components\Hotfire\Exception\InvalidComponentMarkerException;
use Components\Hotfire\Exception\InvalidComponentNameException;
use Components\Hotfire\Exception\InvalidComponentPropertyException;
use Components\Hotfire\Exception\InvalidNamespaceException;
use Components\Hotfire\Exception\InvalidSnapshotException;
use Components\Hotfire\Exception\MissingSnapshotKeyException;
use Components\Hotfire\Exception\MissingViewsRootException;
use Components\Hotfire\Exception\StubTemplateNotFoundException;
use Components\Hotfire\Exception\UnknownComponentException;
use Components\Hotfire\Snapshot;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

final class HotfireExceptionTest extends TestCase
{
    private string $tmp;

    protected function setUp(): void
    {
        $this->tmp = sys_get_temp_dir().'/hot-ui-exceptions-'.uniqid();
        mkdir($this->tmp, 0777, true);
    }

    protected function tearDown(): void
    {
        Config::setShared(null);

        if (! is_dir($this->tmp)) {
            return;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->tmp, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );
        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($this->tmp);
    }

    public function testEveryHotfireExceptionIsAFamilyMemberWithItsSplBase(): void
    {
        $cases = [
            [new InvalidComponentNameException('post.create', 'reason'), InvalidArgumentException::class],
            [new InvalidComponentMarkerException('🔥/'), InvalidArgumentException::class],
            [new InvalidComponentPropertyException('bad-name'), InvalidArgumentException::class],
            [new InvalidNamespaceException('Bad Namespace'), InvalidArgumentException::class],
            [MissingViewsRootException::forDiscovery(), InvalidArgumentException::class],
            [MissingViewsRootException::forConsole(), InvalidArgumentException::class],
            [InvalidSnapshotException::malformed(), InvalidArgumentException::class],
            [InvalidSnapshotException::tooLarge(70000), InvalidArgumentException::class],
            [InvalidSnapshotException::checksumMismatch(), InvalidArgumentException::class],
            [InvalidSnapshotException::notValid(), InvalidArgumentException::class],
            [new MissingSnapshotKeyException(), RuntimeException::class],
            [new UnknownComponentException(\stdClass::class), RuntimeException::class],
            [InvalidActionException::reservedMethod('App\\Counter', 'mount'), RuntimeException::class],
            [InvalidActionException::notAStateProperty('App\\Counter', ''), RuntimeException::class],
            [InvalidActionException::notAPublicMethod('App\\Counter', 'hidden'), RuntimeException::class],
            [new StubTemplateNotFoundException('component_class.stub'), RuntimeException::class],
            [ComponentWriteException::uncreatableDirectory('/nope'), RuntimeException::class],
            [ComponentWriteException::unwritableFile('/nope/file.php'), RuntimeException::class],
        ];

        foreach ($cases as [$exception, $splBase]) {
            self::assertInstanceOf(HotfireException::class, $exception, $exception::class);
            self::assertInstanceOf($splBase, $exception, $exception::class);
            self::assertNotSame('', $exception->getMessage(), $exception::class);
        }
    }

    public function testInvalidNameExposesTheNameAndTheReason(): void
    {
        try {
            (new ComponentPaths($this->tmp))->segments('a/../b');
            self::fail('Expected a rejected component name.');
        } catch (InvalidComponentNameException $exception) {
            self::assertSame('a/../b', $exception->getComponentName());
            self::assertStringContainsString('segments', $exception->getReason());
        }
    }

    public function testUnsafeMarkerExposesTheMarker(): void
    {
        try {
            new ComponentPaths($this->tmp, '🔥/');
            self::fail('Expected a rejected component marker.');
        } catch (InvalidComponentMarkerException $exception) {
            self::assertSame('🔥/', $exception->getMarker());
            self::assertStringContainsString('directory-safe', $exception->getMessage());
        }
    }

    public function testInvalidPropertyExposesThePropertyName(): void
    {
        $generator = new ComponentGenerator($this->tmp);

        try {
            $generator->props('title, bad-name');
            self::fail('Expected a rejected property name.');
        } catch (InvalidComponentPropertyException $exception) {
            self::assertSame('bad-name', $exception->getPropertyName());
        }
    }

    public function testGeneratorRejectsAnInvalidNamespace(): void
    {
        try {
            new ComponentGenerator($this->tmp, 'App\\Bad Namespace');
            self::fail('Expected a rejected namespace.');
        } catch (InvalidNamespaceException $exception) {
            self::assertSame('App\\Bad Namespace', $exception->getNamespace());
        }
    }

    public function testMissingStubExposesTheStubName(): void
    {
        $generator = new ComponentGenerator($this->tmp, 'App\\Components', $this->tmp.'/no-stubs');

        try {
            $generator->classContent('post.create', []);
            self::fail('Expected a missing stub error.');
        } catch (StubTemplateNotFoundException $exception) {
            self::assertSame('component_class.stub', $exception->getStub());
        }
    }

    public function testMissingViewsRootCoversDiscoveryAndConsole(): void
    {
        try {
            (new ComponentPaths())->discover();
            self::fail('Expected a missing views root error.');
        } catch (MissingViewsRootException $exception) {
            self::assertStringContainsString('views root', $exception->getReason());
        }

        $console = MissingViewsRootException::forConsole();
        self::assertStringContainsString('--views', $console->getMessage());
    }

    public function testTamperedSnapshotIsRejectedWithItsReason(): void
    {
        $signed = $this->snapshot();
        $signed['checksum'] = 'f'.substr($signed['checksum'], 1);

        try {
            Snapshot::decode($signed, 'test-key');
            self::fail('Expected a tampered snapshot to be rejected.');
        } catch (InvalidSnapshotException $exception) {
            self::assertSame('checksum mismatch', $exception->getReason());
        }
    }

    public function testOversizedAndMalformedSnapshotsKeepTheirOwnReason(): void
    {
        try {
            Snapshot::decode(['payload' => str_repeat('a', 65537), 'checksum' => 'x'], 'test-key');
            self::fail('Expected an oversized snapshot to be rejected.');
        } catch (InvalidSnapshotException $exception) {
            self::assertStringContainsString('size limit', $exception->getReason());
        }

        try {
            Snapshot::decode([], 'test-key');
            self::fail('Expected a malformed snapshot to be rejected.');
        } catch (InvalidSnapshotException $exception) {
            self::assertSame('malformed payload', $exception->getReason());
        }
    }

    public function testUnsignedRenderingIsItsOwnError(): void
    {
        $this->expectException(MissingSnapshotKeyException::class);

        Snapshot::encode(['class' => Counter::class, 'state' => []]);
    }

    public function testUnknownComponentIsNamedInTheError(): void
    {
        $signed = Snapshot::encode(['class' => \stdClass::class, 'state' => []], 'test-key');

        try {
            Engine::call($signed, ['name' => 'poll'], '/hot-ui/update', 'test-key');
            self::fail('Expected an unknown component rejection.');
        } catch (UnknownComponentException $exception) {
            self::assertSame(\stdClass::class, $exception->getComponentClass());
        }
    }

    public function testReservedMethodAndBadStatePropertyAreRejected(): void
    {
        $signed = $this->snapshot();

        try {
            Engine::call($signed, ['name' => 'call', 'method' => 'mount'], '/hot-ui/update', 'test-key');
            self::fail('Expected a reserved framework method to be rejected.');
        } catch (InvalidActionException $exception) {
            self::assertSame('mount', $exception->getAction());
            self::assertStringContainsString('framework method', $exception->getMessage());
        }

        try {
            Engine::call($signed, ['name' => 'model', 'property' => 'ghost', 'value' => 'x'], '/hot-ui/update', 'test-key');
            self::fail('Expected an unknown state property to be rejected.');
        } catch (InvalidActionException $exception) {
            self::assertSame('ghost', $exception->getAction());
            self::assertStringContainsString('state property', $exception->getMessage());
        }
    }

    public function testActionAndWriteFactoriesDescribeTheirContext(): void
    {
        $action = InvalidActionException::notAPublicMethod(Counter::class, 'hidden');
        self::assertSame('hidden', $action->getAction());
        self::assertSame(Counter::class, $action->getComponentClass());

        $write = ComponentWriteException::unwritableFile('/tmp/post/create.php');
        self::assertSame('/tmp/post/create.php', $write->getPath());
        self::assertStringContainsString('Cannot write', $write->getMessage());
    }

    /** @return array{payload: string, checksum: string} */
    private function snapshot(): array
    {
        return Snapshot::encode(['class' => Counter::class, 'state' => (new Counter())->state()], 'test-key');
    }
}
