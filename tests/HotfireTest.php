<?php

declare(strict_types=1);

namespace Components\Tests;

use Components\Hotfire\Config;
use Components\Hotfire\Engine;
use Components\Hotfire\HtmlTransform;
use Components\Hotfire\Snapshot;
use Components\Ui;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class HotfireTest extends TestCase
{
    private string $tmp;

    protected function setUp(): void
    {
        $this->tmp = sys_get_temp_dir().'/hot-ui-hotfire-'.uniqid();
        $views = $this->tmp.'/views/components';
        mkdir($views, 0777, true);
        
        // Create namespace directory (Components\Tests)
        $namespaceDir = $views.'/Components/Tests';
        mkdir($namespaceDir, 0777, true);
        
        // Also create a copy at root level for the tests to find
        file_put_contents(
            $views.'/counter.php',
            <<<'PHP'
            <button hot:click="increment">Count (<?= $component->count ?>)</button>
            <input hot:model="count">
            <div hot:poll="5000"></div>
            PHP,
        );
        file_put_contents(
            $views.'/typed-component.php',
            '<span><?= $component->total ?></span>',
        );
        
        file_put_contents(
            $namespaceDir.'/counter.php',
            <<<'PHP'
            <button hot:click="increment">Count (<?= $component->count ?>)</button>
            <input hot:model="count">
            <div hot:poll="5000"></div>
            PHP,
        );
        file_put_contents(
            $namespaceDir.'/typed-component.php',
            '<span><?= $component->total ?></span>',
        );
    }

    protected function tearDown(): void
    {
        Config::setShared(null);

        if (is_dir($this->tmp)) {
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
    }

    private function ui(): Ui
    {
        return new Ui($this->tmp.'/views');
    }

    private function signedCounter(): array
    {
        return Snapshot::encode([
            'class' => Counter::class,
            'state' => (new Counter())->state(),
        ], 'test-key');
    }

    public function testSnapshotRoundTrip(): void
    {
        $signed = Snapshot::encode(['count' => 3], 'test-key');

        self::assertSame(['count' => 3], Snapshot::decode($signed, 'test-key'));
    }

    public function testSnapshotTamperRejected(): void
    {
        $tampered = $this->signedCounter();
        $tampered['checksum'] = str_repeat('0', 64);

        $this->expectException(\InvalidArgumentException::class);
        Snapshot::decode($tampered, 'test-key');
    }

    public function testSnapshotSwappedKeyRejected(): void
    {
        $signed = $this->signedCounter();

        $this->expectException(\InvalidArgumentException::class);
        Snapshot::decode($signed, 'other-key');
    }

    public function testEncodeRequiresKey(): void
    {
        $config = new Config(snapshotKeyEnv: 'HOTFIRE_TEST_UNSET_KEY');

        $this->expectException(\RuntimeException::class);
        Snapshot::encode(['count' => 3], null, $config);
    }

    public function testStateExposesOnlyPublicProperties(): void
    {
        $component = new Counter();
        $component->count = 5;

        self::assertSame(['count' => 5], $component->state());
    }

    public function testHydrateAppliesState(): void
    {
        $component = new Counter();
        $component->hydrate(['count' => 7]);

        self::assertSame(7, $component->count);
    }

    public function testRenderReturnsFragmentAndSignedSnapshot(): void
    {
        $component = new Counter();
        $component->count = 3;
        $result = Engine::render($component, 'http://app/hot-ui/update', 'test-key', $this->ui());

        self::assertStringContainsString('data-hot-component', $result['html']);
        self::assertStringContainsString('data-hot-snapshot="'.$result['snapshot']['payload'].'"', $result['html']);
        self::assertStringContainsString('data-hot-checksum="'.$result['snapshot']['checksum'].'"', $result['html']);
        self::assertStringContainsString('data-hot-action="http://app/hot-ui/update"', $result['html']);
        self::assertStringContainsString('Count (3)', $result['html']);
        self::assertStringContainsString('data-hot-click="increment"', $result['html']);
        self::assertStringContainsString('data-hot-model="count"', $result['html']);
        self::assertStringContainsString('data-hot-poll="5000"', $result['html']);
    }

    public function testRenderUsesConfiguredEndpointByDefault(): void
    {
        $config = new Config(endpoint: 'custom/endpoint');
        $result = Engine::render(new Counter(), null, 'test-key', $this->ui(), $config);

        self::assertStringContainsString('data-hot-action="custom/endpoint"', $result['html']);
    }

    public function testRenderCanOmitEndpoint(): void
    {
        $result = Engine::render(new Counter(), '', 'test-key', $this->ui());

        self::assertStringNotContainsString('data-hot-action', $result['html']);
    }

    public function testCallRunsPublicMethodAndReRenders(): void
    {
        $result = Engine::call(
            $this->signedCounter(),
            ['name' => 'call', 'method' => 'increment'],
            'http://app/hot-ui/update',
            'test-key',
            $this->ui(),
        );
        $next = Snapshot::decode($result['snapshot'], 'test-key');

        self::assertSame(1, $next['state']['count']);
        self::assertStringContainsString('Count (1)', $result['html']);
    }

    public function testCallModelUpdateCastsAndReRenders(): void
    {
        $result = Engine::call(
            $this->signedCounter(),
            ['name' => 'model', 'property' => 'count', 'value' => '8'],
            'http://app/hot-ui/update',
            'test-key',
            $this->ui(),
        );
        $next = Snapshot::decode($result['snapshot'], 'test-key');

        self::assertSame(8, $next['state']['count']);
        self::assertStringContainsString('Count (8)', $result['html']);
    }

    public function testCallPollRefreshesWithoutAction(): void
    {
        $result = Engine::call(
            $this->signedCounter(),
            ['name' => 'poll'],
            'http://app/hot-ui/update',
            'test-key',
            $this->ui(),
        );

        self::assertStringContainsString('Count (0)', $result['html']);
    }

    public function testCallRejectsUnknownMethod(): void
    {
        $this->expectException(\RuntimeException::class);
        Engine::call($this->signedCounter(), ['name' => 'call', 'method' => 'bogus'], 'x', 'test-key', $this->ui());
    }

    public function testConfigReservedMethodsCannotBeCalled(): void
    {
        $config = new Config(reserved: ['increment']);

        $this->expectException(\RuntimeException::class);
        Engine::call($this->signedCounter(), ['name' => 'call', 'method' => 'increment'], 'x', 'test-key', $this->ui(), $config);
    }

    public function testCallRejectsTamperedSnapshot(): void
    {
        $snapshot = $this->signedCounter();
        $snapshot['checksum'] = 'f'.substr((string) $snapshot['checksum'], 1);

        $this->expectException(\InvalidArgumentException::class);
        Engine::call($snapshot, ['name' => 'model', 'property' => 'count', 'value' => '1'], 'x', 'test-key', $this->ui());
    }

    public function testCallRejectsUndeclaredModelProperty(): void
    {
        foreach (['bogus', 'view', '', 'slots'] as $property) {
            try {
                Engine::call(
                    $this->signedCounter(),
                    ['name' => 'model', 'property' => $property, 'value' => '1'],
                    'x',
                    'test-key',
                    $this->ui(),
                );
                self::fail(sprintf('Model property [%s] should have been rejected.', $property));
            } catch (\RuntimeException) {
                self::assertTrue(true);
            }
        }
    }

    public function testCallRefusesToInstantiateNonComponentClasses(): void
    {
        $snapshot = Snapshot::encode(['class' => \stdClass::class, 'state' => []], 'test-key');

        $this->expectException(\RuntimeException::class);
        Engine::call($snapshot, ['name' => 'poll'], 'x', 'test-key', $this->ui());
    }

    public function testHydrateSkipsUndeclaredAndNonPublicProperties(): void
    {
        $component = new Counter();
        $component->hydrate(['count' => 7, 'sneaky' => 'injected']);

        self::assertSame(['count' => 7], $component->state());
    }

    public function testOversizedSnapshotPayloadRejected(): void
    {
        $signed = Snapshot::encode(['blob' => str_repeat('a', 70000)], 'test-key');

        $this->expectException(\InvalidArgumentException::class);
        Snapshot::decode($signed, 'test-key');
    }

    public function testComponentViewPathUsesConfiguredPrefix(): void
    {
        self::assertSame('components/counter', (new Counter())->viewPath());

        Config::setShared(new Config(viewPrefix: 'sections'));

        self::assertSame('sections/counter', (new Counter())->viewPath());
    }

    public function testHtmlTransformRewritesKnownDirectivesOnly(): void
    {
        $html = '<button hot:click="go" data-x="keep" hot:carga="no">A</button>';
        $result = HtmlTransform::apply($html);

        self::assertStringContainsString('data-hot-click="go"', $result);
        self::assertStringContainsString('data-x="keep"', $result);
        self::assertStringContainsString('hot:carga="no"', $result);
        self::assertStringNotContainsString('hot:click', $result);
    }

    public function testHtmlTransformConvertsHotComponentTagSyntax(): void
    {
        $html = '<hot:counter init="5" />';
        $result = HtmlTransform::apply($html);

        self::assertStringContainsString('data-hot-component="counter"', $result);
        self::assertStringContainsString('data-hot-props=', $result);
        self::assertStringContainsString('init', $result);
        self::assertStringNotContainsString('<hot:counter', $result);
    }

    public function testHtmlTransformConvertsHotComponentWithColonPropSyntax(): void
    {
        $html = '<hot:counter :init="5" />';
        $result = HtmlTransform::apply($html);

        self::assertStringContainsString('data-hot-component="counter"', $result);
        self::assertStringContainsString('data-hot-props=', $result);
        self::assertStringContainsString('init', $result);
    }

    public function testHtmlTransformRemovesClosingHotComponentTags(): void
    {
        $html = '<hot:counter init="5"></hot:counter>';
        $result = HtmlTransform::apply($html);

        self::assertStringNotContainsString('</hot:counter>', $result);
    }

    public function testHtmlTransformUsesConfiguredDirectives(): void
    {
        $html = '<button hot:go="run">A</button>';

        self::assertSame($html, HtmlTransform::apply($html), 'Unknown directives are preserved by default');

        $config = new Config(directives: ['go' => 'go']);
        self::assertStringContainsString('data-hot-go="run"', HtmlTransform::apply($html, $config));
    }

    public function testUninitializedTypedPropertyDoesNotCrashState(): void
    {
        $component = new TypedComponent();
        $state = $component->state();

        self::assertArrayHasKey('uninitialized', $state);
        self::assertNull($state['uninitialized']);
        self::assertSame(10, $state['total']);

        $component->hydrate(['uninitialized' => null, 'total' => 20]);
        self::assertSame(20, $component->total);
    }

    public function testSnapshotDecodeRejectsMalformedBase64(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Snapshot::decode(['payload' => '!!!not-valid-base64!!!', 'checksum' => 'fake'], 'test-key');
    }

    public function testSnapshotDecodeRejectsInvalidJson(): void
    {
        $payload = base64_encode('{invalid-json:');
        $key = 'test-key';
        $checksum = hash_hmac('sha256', $payload, $key);

        $this->expectException(\InvalidArgumentException::class);
        Snapshot::decode(['payload' => $payload, 'checksum' => $checksum], $key);
    }

    public function testCallHandlesAssociativeParamsPositionally(): void
    {
        $signed = Snapshot::encode([
            'class' => TypedComponent::class,
            'state' => ['uninitialized' => 'ok', 'nullable' => null, 'total' => 10],
        ], 'test-key');

        $result = Engine::call(
            $signed,
            ['name' => 'call', 'method' => 'add', 'params' => ['arbitrary_key' => 5]],
            'http://app/hot-ui/update',
            'test-key',
            $this->ui(),
        );
        $next = Snapshot::decode($result['snapshot'], 'test-key');

        self::assertSame(15, $next['state']['total']);
    }

    public function testCallWithInvalidArgumentsThrowsInvalidActionException(): void
    {
        $signed = Snapshot::encode([
            'class' => TypedComponent::class,
            'state' => ['uninitialized' => 'ok', 'nullable' => null, 'total' => 10],
        ], 'test-key');

        $this->expectException(\RuntimeException::class);
        Engine::call(
            $signed,
            ['name' => 'call', 'method' => 'add', 'params' => []],
            'http://app/hot-ui/update',
            'test-key',
            $this->ui(),
        );
    }
}