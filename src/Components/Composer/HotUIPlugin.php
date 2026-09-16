<?php

declare(strict_types=1);

namespace Components\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Components\HotUI;

/**
 * Composer plugin that auto-publishes the bundled css/ + js/ runtime after
 * every install/update, so the host app needs no composer.json scripts block.
 *
 *   composer require hot-ui/hot-ui
 *   composer config allow-plugins.hot-ui/hot-ui true   # una vez por proyecto
 *
 * The plugin detects the web root itself (FCPATH inside CI4, or public/,
 * public_html/, web/, www/, html/ under the project root). If none is found
 * it only warns instead of failing the composer run.
 */
final class HotUIPlugin implements PluginInterface, EventSubscriberInterface
{
    private IOInterface $io;

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->io = $io;
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => 'publishAssets',
            ScriptEvents::POST_UPDATE_CMD => 'publishAssets',
        ];
    }

    public function publishAssets(Event $event): void
    {
        try {
            $copied = HotUI::autoPublish();
            $this->io->write(sprintf(
                '<info>Hot-UI:</info> publicado css/js en el web root (css=%d, js=%d).',
                $copied['css'] ?? 0,
                $copied['js'] ?? 0,
            ));
        } catch (\Throwable $exception) {
            $this->io->writeError(sprintf(
                '<warning>Hot-UI:</warning> %s (publica a mano con HotUI::publish($publicDir)).',
                $exception->getMessage(),
            ));
        }
    }
}