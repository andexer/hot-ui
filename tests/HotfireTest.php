<?php

declare(strict_types=1);

namespace Components\Tests;

use Components\Hotfire\Component;
use Components\Hotfire\Config;
use Components\Hotfire\Engine;
use Components\Hotfire\HtmlTransform;
use Components\Hotfire\Snapshot;
use Components\Ui;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class Counter extends Component
{
    public int $count = 0;

    public function increment(): void
    {
        $this->count++;
    }
}

final class HotfireTest extends TestCase
{
    private string $tmp;

    protected function setUp(): void
    {
        $this->tmp = sys_get_temp_dir().'/hot-ui-hotfire-'.uniqid();
        $views = $this->tmp.'/views/components/hotfire';
        mkdir($views, 0777, true);
        file_put_contents(
            $views.'/counter.php',
            <<<'PHP'
            <button hot:click="increment">Count (<?= $component->count ?>)</button>
            <input hot:model="count">
            <div hot:poll="5000"></div>
            PHP,
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

    public function testComponentViewPathUsesConfiguredPrefix(): void
    {
        self::assertSame('components/hotfire/counter', (new Counter())->viewPath());

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

    public function testHtmlTransformUsesConfiguredDirectives(): void
    {
        $html = '<button hot:go="run">A</button>';

        self::assertSame($html, HtmlTransform::apply($html), 'Unknown directives are preserved by default');

        $config = new Config(directives: ['go' => 'go']);
        self::assertStringContainsString('data-hot-go="run"', HtmlTransform::apply($html, $config));
    }
}