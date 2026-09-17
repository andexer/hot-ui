<?php

declare(strict_types=1);

namespace Components\Support\Exception;

use Components\Exception\HotUiException;

/**
 * Marker for every error the Support layer raises (house rule 5.1): the
 * template engine, the tag compiler, the attribute bag and the publishers.
 *
 * It narrows Components\Exception\HotUiException, so a host can catch the whole
 * package or just the layer that renders and publishes:
 *
 *   try {
 *       $ui->render('examples/dashboard');
 *   } catch (SupportException $exception) {
 *       // template, tag, attribute or publishing failure
 *   }
 *
 * Each concrete class still extends the SPL exception matching its nature, so
 * layered catches keep working:
 *
 *   InvalidArgumentException — the caller supplied something unusable
 *     UnknownGroupException, UnbalancedTagException,
 *     IllegalAttributeNameException
 *
 *   LogicException — the call sequence itself is wrong
 *     ImmutableAttributeBagException
 *
 *   RuntimeException — the filesystem or the templates cannot comply
 *     DirectoryCreateException, CompiledViewWriteException,
 *     CompiledTemplateNotFoundException, TemplateNotFoundException,
 *     UnknownTemplateNamespaceException
 */
interface SupportException extends HotUiException
{
}
