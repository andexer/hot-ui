<?php

declare(strict_types=1);

namespace Components\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Components\Ci4\Ci4;
use Components\Commands\Exception\InvalidPublishTargetException;
use Components\Hotfire\Config;
use Throwable;

/**
 * Publishes Hot-UI runtime assets (css/js), component views, and/or build
 * configurations into a CodeIgniter 4 application:
 *
 *   php spark hot-ui:publish            # assets + views
 *   php spark hot-ui:publish assets     # only css/js into public/
 *   php spark hot-ui:publish views      # only components/, layouts/, partials/
 *   php spark hot-ui:publish build      # Tailwind, PostCSS, Vite configs
 *   php spark hot-ui:publish all        # assets + views + build configs
 *
 * Requires hot-ui/hot-ui installed; discovers itself via Composer
 * extra.codeigniter4.commands.
 */
final class PublishCommand extends BaseCommand
{
    protected $group = 'Hot-UI';

    protected $name = 'hot-ui:publish';

    protected $description = 'Publishes Hot-UI assets (css/js) and/or views (components/, layouts/, partials/) into your app.';

    protected $usage = 'hot-ui:publish [only]';

    protected $arguments = [
        'only' => '"assets", "views", "build" or "all" (default: all)',
    ];

    /**
     * @param array<int|string, string|null> $params
     *
     * @return int Exit code
     */
    public function run(array $params): int
    {
        try {
            $which = $this->resolveTarget($params);

            if ($which === 'assets' || $which === 'both' || $which === 'all') {
                $this->publishAssets();
            }
            if ($which === 'views' || $which === 'both' || $which === 'all') {
                $this->publishViews();
            }
            if ($which === 'build' || $which === 'all') {
                $this->publishBuildConfigs();
            }
        } catch (Throwable $e) {
            CLI::error(sprintf('Hot-UI: %s', $e->getMessage()));

            return EXIT_ERROR;
        }

        CLI::write('Hot-UI: done.', 'green');

        return EXIT_SUCCESS;
    }

    /**
     * Resolves the requested target from CLI arguments: a bare word
     * ("views", "assets"), --only=views or --all.
     */
    private function resolveTarget(array $params): string
    {
        $which = 'both';
        foreach ($params as $index => $arg) {
            if ($arg === null || $arg === '') {
                continue;
            }
            $arg = (string) $arg;
            if ($arg === '--all' || $arg === '-a') {
                $which = 'both';
            } elseif (preg_match('/^--only=(.*)$/', $arg, $m) && $m[1] !== '') {
                $which = strtolower($m[1]);
            } elseif (($arg === '--only' || $arg === '-o') && isset($params[$index + 1])) {
                $which = strtolower((string) $params[$index + 1]);
            } elseif ($arg !== '' && ! str_starts_with($arg, '-')) {
                $which = strtolower($arg);
            }
        }

        if (! in_array($which, ['assets', 'views', 'both', 'build', 'all'], true)) {
            throw new InvalidPublishTargetException($which);
        }

        return $which;
    }

    private function publishAssets(): void
    {
        $copied = Ci4::publish();
        CLI::write(sprintf(
            'Hot-UI: assets published to %s (css=%d, js=%d).',
            rtrim((string) FCPATH, '/\\'),
            (int) ($copied['css'] ?? 0),
            (int) ($copied['js'] ?? 0),
        ), 'green');
    }

    private function publishViews(): void
    {
        $copied = Ci4::publishViews();
        CLI::write(sprintf(
            'Hot-UI: views copied to %s (components=%d, layouts=%d, partials=%d).',
            rtrim(APPPATH, '/\\').'/Views',
            (int) ($copied['components'] ?? 0),
            (int) ($copied['layouts'] ?? 0),
            (int) ($copied['partials'] ?? 0),
        ), 'green');
        CLI::write('Use your local copy: Ci4::boot(APPPATH.\'Views\');', 'yellow');
        CLI::write('Hotfire endpoint (optional): route POST '.Config::shared()->endpoint().' to Components\\Ci4\\Http\\HotfireController::update.', 'yellow');
    }

    private function publishBuildConfigs(): void
    {
        $packagePath = dirname(__DIR__, 3).'/build';
        $rootPath = ROOTPATH;
        
        $configs = [
            'tailwind.config.js' => 'tailwind.config.js',
            'postcss.config.js' => 'postcss.config.js',
            'vite.config.js' => 'vite.config.js',
        ];
        
        $copied = 0;
        foreach ($configs as $source => $dest) {
            $sourcePath = $packagePath.'/'.$source;
            $destPath = $rootPath.'/'.$dest;
            
            if (is_file($sourcePath)) {
                if (! is_file($destPath)) {
                    copy($sourcePath, $destPath);
                    $copied++;
                    CLI::write(sprintf('  Published: %s', $dest), 'green');
                } else {
                    CLI::write(sprintf('  Skipped (exists): %s', $dest), 'yellow');
                }
            }
        }
        
        CLI::write(sprintf('Hot-UI: build configs published to %s (%d files).', $rootPath, $copied), 'green');
        CLI::write('Run: npm install -D tailwindcss postcss autoprefixer vite', 'yellow');
        CLI::write('Run: npx tailwindcss -i ./app/Views/hotui/css/app.css -o ./public/assets/hot-ui.css', 'yellow');
        CLI::write('Or use Vite: npm run dev', 'yellow');
    }
}