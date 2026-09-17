<?php

declare(strict_types=1);

namespace Components\Tests;

use Components\Support\Exception\DirectoryCreateException;
use Components\Ui;
use PHPUnit\Framework\TestCase;

final class TemplateRendererTest extends TestCase
{
    private string $tmp;
    private Ui $ui;

    protected function setUp(): void
    {
        $this->tmp = sys_get_temp_dir().'/hot-ui-renderer-'.uniqid();
        $root = $this->tmp.'/views';
        if (! is_dir($root) && ! mkdir($root, 0o775, true) && ! is_dir($root)) {
            throw new DirectoryCreateException($root);
        }

        foreach (['ui', 'blocks'] as $ns) {
            $dir = $root.'/components/'.$ns;
            if (! is_dir($dir) && ! mkdir($dir, 0o775, true) && ! is_dir($dir)) {
                throw new DirectoryCreateException($dir);
            }
        }
        $sub = $root.'/sub';
        if (! is_dir($sub) && ! mkdir($sub, 0o775, true) && ! is_dir($sub)) {
            throw new DirectoryCreateException($sub);
        }

        file_put_contents($root.'/components/ui/greet.php', <<<'PHP'
<?php
declare(strict_types=1);
extract(props($__ctx, []));
?><span data-slot="greet" <?= $attributes->twMerge('inline') ?>><?= $slot ?></span>
PHP);
        file_put_contents($root.'/components/blocks/card-wrap.php', '<?= $this->uiGreet(["class" => "w-full"], $slot) ?>');
        file_put_contents($root.'/page.php', 'Página:<?= $this->insert("_frag"); ?><?= $this->fetch("_frag"); ?>');
        file_put_contents($root.'/sub/two.php', 'dos');
        file_put_contents($root.'/_frag.php', '{<?= $this->uiGreet([], "f") ?>}');
        file_put_contents($root.'/../escape.php', 'escape');

        $this->ui = new Ui($root);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tmp)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->tmp, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST,
            );
            foreach ($iterator as $file) {
                $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
            }
            rmdir($this->tmp);
        }
    }

    public function testPlainTemplatesResolveRelativeToRoot(): void
    {
        self::assertSame('dos', $this->ui->render('sub/two'));
        self::assertSame('dos', $this->ui->render('sub/two.php'));
    }

    public function testComponentNamespacesResolveAndBindThis(): void
    {
        $html = $this->ui->render('components/blocks/card-wrap', ['slot' => 'inner']);

        // Rendered through the plain template, blocks::card-wrap calls
        // $this->uiGreet() — forwarded by the renderer to the Ui instance.
        self::assertSame('<span data-slot="greet" class="w-full inline">inner</span>', $html);
    }

    public function testInsertEchoesAndFetchReturns(): void
    {
        $html = $this->ui->render('page');

        self::assertSame('Página:{<span data-slot="greet" class="inline">f</span>}{<span data-slot="greet" class="inline">f</span>}', $html);
    }

    public function testUnknownNamespaceThrows(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->ui->render('ghost::nope');
    }

    public function testPathTraversalIsRejected(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->ui->render('../escape');
    }
}