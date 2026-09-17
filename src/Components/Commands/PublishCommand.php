<?php

declare(strict_types=1);

namespace Components\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Components\Ci4\Ci4;
use Throwable;

/**
 * Publishes Hot-UI runtime assets (css/js) and/or component views into a
 * CodeIgniter 4 application:
 *
 *   php spark hot-ui:publish            # assets + views
 *   php spark hot-ui:publish assets     # solo css/js a public/
 *   php spark hot-ui:publish views      # solo components/, layouts/, partials/
 *
 * Requires hot-ui/hot-ui (^0.9) installed; discovers itself via Composer
 * extra.codeigniter4.commands.
 */
final class PublishCommand extends BaseCommand
{
    protected $group = 'Hot-UI';

    protected $name = 'hot-ui:publish';

    protected $description = 'Publica assets (css/js) y/o vistas (components/ui/*) de Hot-UI en tu app.';

    protected $usage = 'hot-ui:publish [only]';

    protected $arguments = [
        'only' => '"assets", "views" o "both" (por defecto: both)',
    ];

    public function run(array $params): void
    {
        try {
            $which = $this->resolveTarget($params);

            if ($which === 'assets' || $which === 'both') {
                $this->publishAssets();
            }
            if ($which === 'views' || $which === 'both') {
                $this->publishViews();
            }
        } catch (Throwable $e) {
            CLI::error(sprintf('Hot-UI: %s', $e->getMessage()));
            exit(EXIT_ERROR);
        }

        CLI::write('Hot-UI: listo.', 'green');
    }

    /**
     * Resolves the requested target from CLI arguments: a bare word
     * ("views", "assets"), --only=views or --all.
     */
    private function resolveTarget(array $params): string
    {
        $which = 'both';
        foreach ($params as $index => $arg) {
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

        if (! in_array($which, ['assets', 'views', 'both'], true)) {
            throw new \InvalidArgumentException(sprintf('Destino inválido [%s]; usa assets, views o both.', $which));
        }

        return $which;
    }

    private function publishAssets(): void
    {
        $copied = Ci4::publish();
        CLI::write(sprintf(
            'Hot-UI: css/js publicados en %s (css=%d, js=%d).',
            rtrim((string) FCPATH, '/\\'),
            (int) ($copied['css'] ?? 0),
            (int) ($copied['js'] ?? 0),
        ), 'green');
    }

    private function publishViews(): void
    {
        $copied = Ci4::publishViews();
        CLI::write(sprintf(
            'Hot-UI: vistas copiadas a %s (components=%d, layouts=%d, partials=%d).',
            rtrim(APPPATH, '/\\').'/Views/hotui',
            (int) ($copied['components'] ?? 0),
            (int) ($copied['layouts'] ?? 0),
            (int) ($copied['partials'] ?? 0),
        ), 'green');
        CLI::write('Usa tu copia local: Ci4::boot(APPPATH.\'Views/hotui\');', 'yellow');
    }
}