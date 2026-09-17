<?php

declare(strict_types=1);

namespace Components\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Components\Hotfire\ComponentGenerator;

/**
 * php spark make:hotfire <name> [options]
 *
 * Scaffolds a Hotfire reactive component for the running CodeIgniter 4 app:
 * the component class (extends Components\Hotfire\Component) and its template
 * under the Hotfire view prefix, plus optional JS, scoped/global CSS and a
 * PHPUnit test — the Livewire make:livewire workflow, adapted for Hotfire.
 *
 *   php spark make:hotfire post.create
 *   php spark make:hotfire post.create --props="title,content"
 *   php spark make:hotfire post.create --mfc --js --css --test
 *
 * The command is auto-discovered by spark from this package (any class in
 * vendor/**\Commands\|Components\Commands extending BaseCommand).
 */
class MakeHotfire extends BaseCommand
{
    use CliOptions;

    /**
     * @var string
     */
    protected $group = 'Hot-UI';

    /**
     * @var string
     */
    protected $name = 'make:hotfire';

    /**
     * @var string
     */
    protected $description = 'Generates a Hotfire reactive component (class + template).';

    /**
     * @var string
     */
    protected $usage = 'make:hotfire <name> [options]';

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
        '--props'       => 'Comma-separated public state properties, e.g. title,content.',
        '--mfc'         => 'Shortcut for --props="title,content" plus the form template.',
        '--namespace'   => 'Root namespace for the component class. Default: "App\\Components".',
        '--views'       => 'Views folder. Default: APPPATH.\'Views\'.',
        '--emoji'       => 'Visual marker on the component folder. Default: "🔥".',
        '--js'          => 'Also generate the scoped JavaScript sidecar.',
        '--css'         => 'Also generate the scoped stylesheet sidecar.',
        '--global-css'  => 'Also generate the global stylesheet sidecar.',
        '--test'        => 'Also generate a PHPUnit test in the component folder.',
        '--force'       => 'Overwrite existing files.',
    ];

    /**
     * Actually execute the command.
     *
     * @param array<int|string, string|null> $params
     */
    public function run(array $params)
    {
        $name = (string) ($params[0] ?? ($this->option($params, 'name') ?? ''));
        if ($name === '') {
            CLI::error('Hotfire: missing component name. Usage: '.$this->usage);
            CLI::newLine();

            return EXIT_ERROR;
        }

        try {
            $generator = $this->generator($params);

            $props = $this->has($params, 'mfc') ? ['title', 'content'] : ($generator)->props($this->option($params, 'props') ?? '');
            $form = $this->has($params, 'mfc') || $props !== [];

            $targets = [
                $generator->classPath($name) => $generator->classContent($name, $props),
                $generator->viewPath($name)  => $generator->viewContent($name, $props),
            ];
            if ($this->has($params, 'js')) {
                $targets[$generator->sidecarPath($name, 'js')] = $generator->jsContent($name);
            }
            if ($this->has($params, 'css')) {
                $targets[$generator->sidecarPath($name, 'css')] = $generator->cssContent($name);
            }
            if ($this->has($params, 'global-css')) {
                $targets[$generator->sidecarPath($name, 'global.css')] = $generator->globalCssContent($name);
            }
            if ($this->has($params, 'test')) {
                $targets[$generator->testPath($name)] = $generator->testContent($name);
            }

            $written = $this->writeAll($targets, $this->has($params, 'force'));

            foreach ($written as $path => $created) {
                CLI::write($created ? 'Created: ' : 'Skipped (exists): ', $created ? 'green' : 'yellow');
                CLI::write('  '.clean_path($path));
            }
            if ($form) {
                CLI::newLine();
                CLI::write('Next: render it with Ci4::hotfire(new \\'.$generator->classNamespace($name).'\\'.$generator->className($name).'());', 'blue');
            }

            CLI::newLine();
        } catch (\InvalidArgumentException $exception) {
            CLI::error('Hotfire: '.$exception->getMessage());
            CLI::newLine();

            return EXIT_ERROR;
        }

        return EXIT_SUCCESS;
    }

    /**
     * Writes every generated file, respecting an existing artifact unless
     * --force is given.
     *
     * @param array<string, string> $targets
     *
     * @return array<string, bool> path => created
     */
    private function writeAll(array $targets, bool $force): array
    {
        $result = [];
        foreach ($targets as $path => $content) {
            $directory = dirname($path);
            if (! is_dir($directory) && ! @mkdir($directory, 0o775, true) && ! is_dir($directory)) {
                CLI::error('Hotfire: cannot create directory '.clean_path($directory));
                CLI::newLine();

                continue;
            }

            if (! $force && is_file($path)) {
                $result[$path] = false;
                continue;
            }

            if (file_put_contents($path, $content, LOCK_EX) === false) {
                CLI::error('Hotfire: cannot write '.clean_path($path));
                CLI::newLine();
                continue;
            }
            $result[$path] = true;
        }

        return $result;
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

        $namespace = $this->option($params, 'namespace') ?? 'App\\Components';
        if (! preg_match('#^[A-Za-z_][A-Za-z0-9_\\\\]*$#', trim($namespace, '\\'))) {
            throw new \InvalidArgumentException(sprintf('Invalid namespace [%s].', $namespace));
        }

        return new ComponentGenerator($views, trim($namespace, '\\'), __DIR__.'/../Hotfire/templates', $this->option($params, 'emoji') ?? '🔥');
    }
}