<?php

declare(strict_types=1);

namespace Components\Tests;

use Components\Hotfire\Config;
use Components\Hotfire\Engine;
use Components\Hotfire\Events\EventDispatcher;
use Components\Hotfire\Exception\InvalidActionException;
use Components\Hotfire\FormBinder;
use Components\Hotfire\HtmlTransform;
use Components\Hotfire\NestedComponentHelper;
use Components\Hotfire\Pagination\PaginationHelper;
use Components\Hotfire\Responses\RedirectResponse;
use Components\Hotfire\Security\CsrfProtection;
use Components\Hotfire\Snapshot;
use Components\Hotfire\Uploads\UploadHandler;
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
        $hotfireViews = $views.'/hotfire';
        mkdir($views, 0777, true);
        mkdir($hotfireViews, 0777, true);
        
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
            $hotfireViews.'/counter.php',
            <<<'PHP'
            <button hot:click="increment">Count (<?= $component->count ?>)</button>
            <input hot:model="count">
            <div hot:poll="5000"></div>
            PHP,
        );
        file_put_contents(
            $hotfireViews.'/typed-component.php',
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

    public function testCallModelUpdateSupportsNestedStatePaths(): void
    {
        $component = new TypedComponent();
        $signed = Snapshot::encode([
            'class' => TypedComponent::class,
            'state' => $component->state(),
        ], 'test-key');

        $result = Engine::call(
            $signed,
            ['name' => 'model', 'property' => 'user.name', 'value' => 'Ada'],
            'http://app/hot-ui/update',
            'test-key',
            $this->ui(),
        );
        $next = Snapshot::decode($result['snapshot'], 'test-key');

        self::assertSame('Ada', $next['state']['user']['name']);
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

    public function testHtmlTransformPreservesHotfireModifiersAsMetadata(): void
    {
        $html = '<input hot:model.live.debounce.300ms="search">';
        $result = HtmlTransform::apply($html);

        self::assertStringContainsString('data-hot-model="search"', $result);
        self::assertStringContainsString('data-hot-model-modifiers="live debounce 300ms"', $result);
    }

    public function testHtmlTransformRewritesInteractionAndStateDirectives(): void
    {
        $html = <<<'HTML'
        <form hot:submit.prevent="save">
            <button hot:click="destroy" hot:confirm="Are you sure?" hot:target="destroy">Delete</button>
            <span hot:loading="destroy">Deleting</span>
            <span hot:dirty="title">Unsaved</span>
            <input hot:change="lookup" hot:key.enter.prevent="search">
        </form>
        HTML;
        $result = HtmlTransform::apply($html);

        self::assertStringContainsString('data-hot-submit="save" data-hot-submit-modifiers="prevent"', $result);
        self::assertStringContainsString('data-hot-confirm="Are you sure?"', $result);
        self::assertStringContainsString('data-hot-target="destroy"', $result);
        self::assertStringContainsString('data-hot-loading="destroy"', $result);
        self::assertStringContainsString('data-hot-dirty="title"', $result);
        self::assertStringContainsString('data-hot-change="lookup"', $result);
        self::assertStringContainsString('data-hot-key="search" data-hot-key-modifiers="enter prevent"', $result);
    }

    public function testHtmlTransformConvertsHotComponentTagSyntax(): void
    {
        $html = '<hot:counter init="5" />';
        $result = HtmlTransform::apply($html);

        self::assertStringContainsString('data-hot-component="counter"', $result);
        self::assertStringContainsString('data-hot-props=', $result);
        self::assertStringContainsString('init', $result);
        self::assertStringContainsString('</div>', $result);
        self::assertStringNotContainsString('<hot:counter', $result);
    }

    public function testHtmlTransformConvertsHotComponentWithColonPropSyntax(): void
    {
        $html = '<hot:counter :init="5" />';
        $result = HtmlTransform::apply($html);

        self::assertStringContainsString('data-hot-component="counter"', $result);
        self::assertStringContainsString('data-hot-props=', $result);
        self::assertStringContainsString('init', $result);
        self::assertStringContainsString('&quot;init&quot;:&quot;5&quot;', $result);
        self::assertStringNotContainsString('&quot;init&quot;:&quot;&quot;5&quot;&quot;', $result);
    }

    public function testHtmlTransformRemovesClosingHotComponentTags(): void
    {
        $html = '<hot:counter init="5"></hot:counter>';
        $result = HtmlTransform::apply($html);

        self::assertStringContainsString('</div>', $result);
        self::assertStringNotContainsString('</hot:counter>', $result);
    }

    public function testFormBinderGetsSimpleValue(): void
    {
        $component = new Counter();
        $component->count = 10;

        self::assertSame(10, FormBinder::value($component, 'count'));
    }

    public function testFormBinderCheckedForSingleValue(): void
    {
        $component = new Counter();
        $component->count = 5;

        self::assertTrue(FormBinder::checked($component, 'count', 5));
        self::assertFalse(FormBinder::checked($component, 'count', 10));
    }

    public function testFormBinderCheckedForArray(): void
    {
        $component = new TypedComponent();
        $component->tags = ['php', 'javascript', 'python'];

        self::assertTrue(FormBinder::checked($component, 'tags', 'javascript'));
        self::assertFalse(FormBinder::checked($component, 'tags', 'go'));
    }

    public function testFormBinderCheckboxChecked(): void
    {
        $component = new TypedComponent();
        $component->categories = ['technology', 'design'];

        self::assertTrue(FormBinder::checkboxChecked($component, 'categories', 'technology'));
        self::assertFalse(FormBinder::checkboxChecked($component, 'categories', 'business'));
    }

    public function testFormBinderHasError(): void
    {
        $component = new TypedComponent();
        $component->errors = ['title' => 'Title is required'];

        self::assertTrue(FormBinder::hasError($component, 'title'));
        self::assertSame('Title is required', FormBinder::error($component, 'title'));
        self::assertFalse(FormBinder::hasError($component, 'content'));
    }

    public function testValidationRulesRequired(): void
    {
        $component = new TypedComponent();
        $component->total = 0;
        $component->customMessages = ['total.required' => 'Total is mandatory'];

        $component->validate();

        self::assertTrue($component->hasErrors());
        self::assertArrayHasKey('total', $component->getErrors());
    }

    public function testValidationRulesEmail(): void
    {
        $component = new TypedComponent();
        $component->email = 'invalid-email';
        $component->customMessages = [
            'email' => 'Email',
            'email.email' => 'Please provide a valid email',
        ];

        $component->validate();

        self::assertTrue($component->hasErrors());
        self::assertArrayHasKey('email', $component->getErrors());
    }

    public function testValidationOnly(): void
    {
        $component = new TypedComponent();
        $component->email = 'test@example.com';
        $component->total = 5;

        $result = $component->validateOnly(['email']);

        self::assertTrue($result);
        self::assertFalse($component->hasErrors());
    }

    public function testAllowlistRestrictsActions(): void
    {
        $component = new AllowlistedComponent();

        $signed = Snapshot::encode(['class' => AllowlistedComponent::class, 'state' => $component->state()], 'test-key');

        $this->expectException(InvalidActionException::class);
        $this->expectExceptionMessage('not in the allowlist');

        Engine::call(
            $signed,
            ['name' => 'method', 'method' => 'subtract', 'params' => [1]],
            'http://app/hot-ui/update',
            'test-key',
            $this->ui(),
        );
    }

    public function testAllowlistAllowsPermittedActions(): void
    {
        $component = new AllowlistedComponent();
        $component->total = 5;

        $signed = Snapshot::encode(['class' => AllowlistedComponent::class, 'state' => $component->state()], 'test-key');

        $result = Engine::call(
            $signed,
            ['name' => 'method', 'method' => 'add', 'params' => [3]],
            'http://app/hot-ui/update',
            'test-key',
            $this->ui(),
        );

        self::assertStringContainsString('8', $result['html']);
    }

    public function testCsrfTokenIsIncludedInRenderedHtml(): void
    {
        // Mock session with CSRF token
        $_SESSION['csrf_token'] = 'test-csrf-token-123';

        $component = new Counter();
        $result = Engine::render($component, 'http://app/hot-ui/update', 'test-key', $this->ui());

        self::assertStringContainsString('data-hot-csrf', $result['html']);
        self::assertStringContainsString('test-csrf-token-123', $result['html']);

        // Clean up
        unset($_SESSION['csrf_token']);
    }

    public function testCsrfProtectionReturnsMetaTag(): void
    {
        $_SESSION['csrf_token'] = 'test-token';

        $meta = CsrfProtection::metaTag();

        self::assertStringContainsString('csrf-token', $meta);
        self::assertStringContainsString('test-token', $meta);

        unset($_SESSION['csrf_token']);
    }

    public function testCsrfProtectionReturnsHiddenField(): void
    {
        $_SESSION['csrf_token'] = 'test-token';

        $field = CsrfProtection::hiddenField();

        self::assertStringContainsString('type="hidden"', $field);
        self::assertStringContainsString('test-token', $field);

        unset($_SESSION['csrf_token']);
    }

    public function testComputedPropertiesAreIncludedInState(): void
    {
        $component = new TypedComponent();
        $component->total = 5;

        $state = $component->state();

        self::assertArrayHasKey('totalCount', $state);
        self::assertSame(5, $state['totalCount']);
    }

    public function testLockedPropertyCannotBeUpdated(): void
    {
        $component = new TypedComponent();
        $component->total = 5;
        $component->lockProperty('total');

        // Verify property is locked
        self::assertTrue($component->isLocked('total'));

        // Verify property is still in state (for display)
        $state = $component->state();
        self::assertArrayHasKey('total', $state);
        self::assertSame(5, $state['total']);
    }

    public function testIsLockedReturnsCorrectStatus(): void
    {
        $component = new TypedComponent();
        $component->lockProperty('total');

        self::assertTrue($component->isLocked('total'));
        self::assertFalse($component->isLocked('email'));
    }

    public function testEventDispatcherDispatchesGlobalEvent(): void
    {
        $called = false;
        EventDispatcher::listen('test-event', function () use (&$called): void {
            $called = true;
        });

        EventDispatcher::dispatch('test-event');

        self::assertTrue($called);
        
        EventDispatcher::forget('test-event');
    }

    public function testEventDispatcherDispatchesToComponent(): void
    {
        $called = false;
        EventDispatcher::listenTo('component-1', 'test-event', function () use (&$called): void {
            $called = true;
        });

        EventDispatcher::dispatchTo('component-1', 'test-event');

        self::assertTrue($called);
        
        EventDispatcher::forgetComponent('component-1');
    }

    public function testComponentCanDispatchEvent(): void
    {
        $component = new TypedComponent();
        $called = false;
        
        EventDispatcher::listen('component-event', function () use (&$called): void {
            $called = true;
        });

        $component->dispatch('component-event');

        self::assertTrue($called);
        
        EventDispatcher::forget('component-event');
    }

    public function testComponentCanListenToEvent(): void
    {
        $component = new TypedComponent();
        $called = false;
        
        $component->listen('test-event', function () use (&$called): void {
            $called = true;
        });

        EventDispatcher::dispatch('test-event');

        self::assertTrue($called);
        
        EventDispatcher::forget('test-event');
    }

    public function testComponentCanHandleBrowserEvent(): void
    {
        $component = new TypedComponent();
        
        // Test that the component has the handleBrowserEvent method
        self::assertTrue(method_exists($component, 'handleBrowserEvent'));
    }

    public function testComponentCanReturnRedirectResponse(): void
    {
        $component = new TypedComponent();
        
        $response = $component->redirectToHome();
        
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/home', $response->url);
        self::assertSame(302, $response->status);
    }

    public function testComponentCanReturnFlashMessage(): void
    {
        $component = new TypedComponent();
        
        $response = $component->flashSuccess();
        
        self::assertIsObject($response);
        self::assertSame('Operation successful', $response->message);
        self::assertSame('success', $response->type);
    }

    public function testComponentCanReturnDownloadResponse(): void
    {
        $component = new TypedComponent();
        
        $response = $component->downloadFile();
        
        self::assertIsObject($response);
        self::assertSame('file content', $response->content);
        self::assertSame('test.txt', $response->filename);
    }

    public function testComponentCanReturnNoContentResponse(): void
    {
        $component = new TypedComponent();
        
        $response = $component->returnNoContent();
        
        self::assertIsObject($response);
        self::assertSame(204, $response->status);
    }

    public function testEngineHandlesRedirectResponse(): void
    {
        $component = new TypedComponent();
        $component->total = 5;

        $signed = Snapshot::encode(['class' => TypedComponent::class, 'state' => $component->state()], 'test-key');

        $result = Engine::call(
            $signed,
            ['name' => 'method', 'method' => 'redirectToHome', 'params' => []],
            'http://app/hot-ui/update',
            'test-key',
            $this->ui(),
        );

        self::assertArrayHasKey('response', $result);
        self::assertSame('redirect', $result['response']['type']);
        self::assertSame('/home', $result['response']['url']);
    }

    public function testNestedComponentHelperGeneratesStableKey(): void
    {
        $key = NestedComponentHelper::generateKey('parent-1', 'child', 'unique-123');
        
        self::assertSame('parent-1.child-unique-123', $key);
    }

    public function testNestedComponentHelperParsesKey(): void
    {
        $parsed = NestedComponentHelper::parseKey('parent.child-unique-123');
        
        self::assertSame('parent', $parsed['parent']);
        self::assertSame('child', $parsed['child']);
        self::assertSame('unique-123', $parsed['uniqueId']);
    }

    public function testNestedComponentHelperDetectsNestedComponents(): void
    {
        self::assertTrue(NestedComponentHelper::isNested('parent.child'));
        self::assertFalse(NestedComponentHelper::isNested('root'));
    }

    public function testNestedComponentHelperGetsRootKey(): void
    {
        self::assertSame('root', NestedComponentHelper::getRootKey('root'));
        self::assertSame('root', NestedComponentHelper::getRootKey('root.child.grandchild'));
    }

    public function testComponentSupportsKeyProperty(): void
    {
        $component = new TypedComponent();
        $component->key = 'my-component-key';
        
        self::assertSame('my-component-key', $component->key);
    }

    public function testNavigationSupportExists(): void
    {
        $component = new TypedComponent();
        
        self::assertTrue(method_exists($component, 'navigate'));
        self::assertTrue(method_exists($component, 'back'));
        self::assertTrue(method_exists($component, 'forward'));
        self::assertTrue(method_exists($component, 'persistElement'));
        self::assertTrue(method_exists($component, 'isPersistent'));
    }

    public function testNavigationAddsToHistory(): void
    {
        $component = new TypedComponent();
        
        $result = $component->navigate('/test', 'Test Page', true);
        
        self::assertSame('/test', $result['url']);
        self::assertSame('Test Page', $result['title']);
        self::assertTrue($result['prefetch']);
        self::assertSame(0, $component->historyIndex);
    }

    public function testNavigationBack(): void
    {
        $component = new TypedComponent();
        $component->navigate('/page1', 'Page 1');
        $component->navigate('/page2', 'Page 2');
        
        $back = $component->back();
        
        self::assertSame('/page1', $back['url']);
        self::assertSame('Page 1', $back['title']);
    }

    public function testNavigationPersistElement(): void
    {
        $component = new TypedComponent();
        $component->persistElement('header');
        
        self::assertTrue($component->isPersistent('header'));
        self::assertFalse($component->isPersistent('footer'));
    }

    public function testUploadHandlerExists(): void
    {
        $component = new TypedComponent();
        
        self::assertTrue(method_exists($component, 'handleUpload'));
        self::assertTrue(method_exists($component, 'getUploadProgress'));
        self::assertTrue(method_exists($component, 'generatePreview'));
        self::assertTrue(method_exists($component, 'moveToStorage'));
        self::assertTrue(method_exists($component, 'validateUpload'));
        self::assertTrue(method_exists($component, 'cleanupUploads'));
    }

    public function testUploadHandlerValidatesFile(): void
    {
        $handler = new UploadHandler();
        
        // Test with invalid file
        $result = $handler->upload([]);
        
        self::assertFalse($result['success']);
        self::assertNotNull($result['error']);
    }

    public function testUploadHandlerCanGeneratePreview(): void
    {
        $handler = new UploadHandler();
        
        // Test with non-existent file
        $preview = $handler->generatePreview('/nonexistent/file.jpg');
        
        self::assertNull($preview);
    }

    public function testUploadHandlerCanValidateRules(): void
    {
        $handler = new UploadHandler();
        
        // Test with empty file data
        $result = $handler->validate([], []);
        
        self::assertTrue($result['valid']);
        self::assertEmpty($result['errors']);
    }

    public function testUploadHandlerAcceptsAlreadyStagedTemporaryFiles(): void
    {
        $tempDir = $this->tmp.'/uploads';
        $source = $this->tmp.'/sample.txt';
        file_put_contents($source, 'hello upload');

        $handler = new UploadHandler($tempDir);
        $result = $handler->upload([
            'tmp_name' => $source,
            'name' => '../sample.txt',
            'size' => filesize($source),
        ]);

        self::assertTrue($result['success']);
        self::assertIsString($result['path']);
        self::assertFileExists($result['path']);
        self::assertSame('sample.txt', $result['originalName']);
        self::assertStringStartsWith($tempDir, $result['path']);
    }

    public function testUploadHandlerSupportsS3CompatibleStorageCallback(): void
    {
        $source = $this->tmp.'/stored.txt';
        file_put_contents($source, 'storage payload');

        $handler = new UploadHandler($this->tmp.'/uploads');
        $called = false;
        $handler->setStorage([
            'type' => 's3',
            'put' => function (string $tempPath, string $destination, array $config) use (&$called): bool {
                $called = true;

                return is_file($tempPath)
                    && $destination === 'bucket/path/stored.txt'
                    && ($config['bucket'] ?? null) === 'bucket';
            },
            'bucket' => 'bucket',
        ]);

        self::assertTrue($handler->moveToStorage($source, 'bucket/path/stored.txt'));
        self::assertTrue($called);
    }

    public function testPaginationNeverFallsToPageZeroWhenThereAreNoRows(): void
    {
        $pagination = new PaginationHelper();
        $pagination->total = 0;

        $pagination->goToPage(5);

        self::assertSame(1, $pagination->page);

        $pagination->syncFromUrl(['page' => 0, 'perPage' => 0]);

        self::assertSame(1, $pagination->page);
        self::assertSame(1, $pagination->perPage);
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
