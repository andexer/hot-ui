<?php

declare(strict_types=1);

namespace Components\Hotfire\Exception;

use Components\Exception\HotUiException;

/**
 * Marker for every error the Hotfire layer raises (house rule 5.1).
 *
 * It narrows Components\Exception\HotUiException, so the package-wide marker
 * also catches Hotfire failures.
 *
 * Callers can catch the whole family with one type:
 *
 *   try {
 *       Engine::call($snapshot, $action);
 *   } catch (HotfireException $exception) {
 *       // any Hotfire error: snapshot, action, component name, scaffolding…
 *   }
 *
 * Each concrete class still extends the SPL exception matching its nature, so
 * layered catches keep working:
 *
 *   InvalidArgumentException — the caller supplied something unusable
 *     InvalidComponentNameException, InvalidComponentMarkerException,
 *     InvalidComponentPropertyException, MissingViewsRootException,
 *     InvalidSnapshotException
 *
 *   RuntimeException — the environment/state cannot satisfy the request
 *     MissingSnapshotKeyException, UnknownComponentException,
 *     InvalidActionException, StubTemplateNotFoundException
 */
interface HotfireException extends HotUiException
{
}
