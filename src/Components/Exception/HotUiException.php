<?php

declare(strict_types=1);

namespace Components\Exception;

/**
 * Marker for every error the core package raises (house rule 5.1).
 *
 * Callers can catch the whole family with one type:
 *
 *   try {
 *       $ui->renderComponent('ui.button', $props);
 *   } catch (HotUiException $exception) {
 *       // any package error: assets, views, templates, rendering pipeline…
 *   }
 *
 * Each concrete class still extends the SPL exception matching its nature, and
 * each layer narrows this marker with one of its own, so a host can catch the
 * package or just the piece that failed:
 *
 *   Components\Hotfire\Exception\HotfireException — the reactive layer
 *   Components\Support\Exception\SupportException — template engine, tag
 *     compiler, attribute bag and publishers
 *   Components\Commands\Exception\CommandException — the spark commands
 *
 * These live here because they belong to no single layer:
 *
 *   InvalidArgumentException — the caller supplied something unusable, or
 *   omitted something the environment cannot default for them
 *     ReservedPropertyNameException, UnresolvedDirectoryException,
 *     UnsupportedPositionalArgumentException
 *
 *   LogicException — the call sequence itself is wrong
 *     UiPipelineException
 *
 *   RuntimeException — the registry or the filesystem cannot comply
 *     ComponentNotFoundException, MissingDirectoryException,
 *     MissingComponentNamespaceException
 */
interface HotUiException extends \Throwable
{
}
