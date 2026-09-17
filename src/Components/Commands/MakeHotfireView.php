<?php

declare(strict_types=1);

namespace Components\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Components\Hotfire\ComponentGenerator;

/**
 * php spark make:hotfire-view <name> [options]
 *
 * Generates ONLY the Hotfire template for a component name, without touching
 * any class file. Handy when the matching class already exists and you just
 * want the view scaffold (or a copy under another name).
 *
 *   php spark make:hotfire-view post.create --props="title,content"
 */
class MakeHotfireView extends BaseCommand
{
    use CliOptions;

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
    public function run(array $params)
    {
        $name = (string) ($params[0] ?? ($prop = $this->option($params, 'name')) ?? '');
        if ($name === '') {
            CLI::error('Hotfire: missing component name. Usage: '.$this->usage);
            CLI::newLine();

            return EXIT_ERROR;
        }

        try {
            $generator = $this->generator($params);

            $props = $this->has($params, 'mfc') ? ['title', 'content'] : $generator->props($this->option($params, 'props') ?? '');

            $path = $generator->viewPath($name);
            $directory = dirname($path);
            if (! is_dir($directory) && ! @mkdir($directory, 0o775, true) && ! is_dir($directory)) {
                throw new \InvalidArgumentException('Cannot create directory '.$directory);
            }

            if (! $this->has($params, 'force') && is_file($path)) {
                CLI::write('Skipped (exists): ', 'yellow');
                CLI::write('  '.clean_path($path));
                CLI::newLine();

                return EXIT_SUCCESS;
            }

            if (file_put_contents($path, $generator->viewContent($name, $props), LOCK_EX) === false) {
                throw new \InvalidArgumentException('Cannot write '.$path);
            }

            CLI::write('Created: ', 'green');
            CLI::write('  '.clean_path($path));
            CLI::newLine();
        } catch (\InvalidArgumentException $exception) {
            CLI::error('Hotfire: '.$exception->getMessage());
            CLI::newLine();

            return EXIT_ERROR;
        }

        return EXIT_SUCCESS;
    }

    private function generator(array $params): ComponentGenerator
    {
        $views = $this->option($params, 'views');
        if ($views === null) {
            if (! defined('APPPATH')) {
                throw new \InvalidArgumentException('Cannot resolve APPPATH; run from a CodeIgniter 4 application.');
            }
            $views = rtrim((string) APPPATH, '/\\').'/Views';
        }

        return new ComponentGenerator($views, 'App\\Components', __DIR__.'/../Hotfire/templates', $this->option($params, 'emoji') ?? '🔥');
    }
}