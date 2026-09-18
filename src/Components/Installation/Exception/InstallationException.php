<?php

declare(strict_types=1);

namespace Components\Installation\Exception;

use Components\Exception\HotUiException;

/**
 * Marker for every error the Installation layer raises (house rule 5.1): the
 * step registry, the wizard and the individual steps.
 *
 * It narrows Components\Exception\HotUiException, so a host can catch the whole
 * package or just the layer that installs and configures Hot-UI:
 *
 *   try {
 *       $wizard->run();
 *   } catch (InstallationException $exception) {
 *       // step registration, ordering or execution failure
 *   }
 *
 * Each concrete class still extends the SPL exception matching its nature:
 *
 *   RuntimeException — the registry cannot comply
 *     CircularDependencyException
 */
interface InstallationException extends HotUiException
{
}