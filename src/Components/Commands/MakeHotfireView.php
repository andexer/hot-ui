<?php

declare(strict_types=1);

namespace Components\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Components\Hotfire\ComponentGenerator;
use Components\Hotfire\Exception\ComponentWriteException;
use Components\Hotfire\Exception\HotfireException;
use Components\Support\Filesystem;

/**
 * php spark make:hotfire-view <name> [options]
 *
 * Generates ONLY the Hotfire template for a component name, without touching
 * any class file. Handy when the matching class already exists and you just
 * want the view scaffold (or a copy under another name).
 *
 *   php spark make:hotfire-view post.create --props="title,content"
 */
final class MakeHotfireView extends BaseCommand
{
    use CliOptions;

    /**
     * CI4.7 forwards the logger and the command locator; standalone use (tests,
     * package tooling) can build the command without them.
     */
    public function __construct(?\Psr\Log\LoggerInterface $logger = null, ?\CodeIgniter\CLI\Commands $commands = null)
    {
        if ($logger !== null && $commands !== null) {
            parent::__construct($logger, $commands);
        }
    }

    /**
     * @var string
     */
    protected $group = 'Hot-UI';

    /**
     * @var string
     */
    protected $name = 'make:hotfire-view';

    /**
     * @var string
     */
    protected $description = 'Generates the Hotfire component template only.';

    /**
     * @var string
     */
    protected $usage = 'make:hotfire-view <name> [options]';

    /**
     * @var array<string, string>
     */
    protected $arguments = [
        'name' => 'Component name, dotted or slashed (post.create, post/create).',
    ];

    /**
     * @var array<string, string>
     */
    protected $options = [
        '--props'      => 'Comma-separated public state properties, e.g. title,content.',
        '--mfc'        => 'Shortcut for --props="title,content" plus the form template.',
        '--views'      => 'Views folder. Default: APPPATH.\'Views\'.',
        '--emoji'      => 'Visual marker on the component folder. Default: "🔥".',
        '--force'      => 'Overwrite the existing template.',
    ];

    /**
     * @param array<int|string, string|null> $params
     */
    public function run(array $params): int
    {
        $name = (string) ($params[0] ?? ($this->option($params, 'name') ?? ''));
        if ($name === '') {
            CLI::error('Hotfire: missing component name. Usage: '.$this->usage);
            CLI::newLine();

            return EXIT_ERROR;
        }

        try {
            $generator = $this->generator($params);

            $props = $this->has($params, 'mfc') ? ['title', 'content'] : $generator->props($this->option($params, 'props') ?? '');

            $path = $generator->viewPath($name);
            if (! Filesystem::ensureDirectory(dirname($path))) {
                throw ComponentWriteException::uncreatableDirectory(dirname($path));
            }

            if (! $this->has($params, 'force') && is_file($path)) {
                CLI::write('Skipped (exists): ', 'yellow');
                CLI::write('  '.clean_path($path));
                CLI::newLine();

                return EXIT_SUCCESS;
            }

            if (file_put_contents($path, $generator->viewContent($name, $props), LOCK_EX) === false) {
                throw ComponentWriteException::unwritableFile($path);
            }

            CLI::write('Created: ', 'green');
            CLI::write('  '.clean_path($path));
            CLI::newLine();
        } catch (HotfireException $exception) {
            CLI::error('Hotfire: '.$exception->getMessage());
            CLI::newLine();

            return EXIT_ERROR;
        }

        return EXIT_SUCCESS;
    }

    private function generator(array $params): ComponentGenerator
    {
        $customStubsDir = defined('APPPATH')
            ? rtrim(APPPATH, '/\\') . '/Components/stubs/hot-ui'
            : null;

        return new ComponentGenerator(
            $this->viewsRoot($params),
            'App\\Components',
            __DIR__.'/../Hotfire/templates',
            $this->option($params, 'emoji') ?? '🔥',
            ($customStubsDir !== null && is_dir($customStubsDir)) ? $customStubsDir : null,
        );
    }
}