<?php

declare(strict_types=1);

namespace Components\Commands\Exception;

use Components\Exception\HotUiException;

/**
 * Marker for every error the spark commands raise (house rule 5.1).
 *
 * It narrows Components\Exception\HotUiException, so a command can catch the
 * failures of its own layer and answer on the CLI, while hosts keep catching
 * the package-wide marker:
 *
 *   try {
 *       $which = $this->resolveTarget($params);
 *   } catch (CommandException $exception) {
 *       CLI::error($exception->getMessage());
 *
 *       return EXIT_ERROR;
 *   }
 *
 * Scaffolding failures are not part of this family: `make:hotfire` raises the
 * Hotfire exceptions, because what failed is the component being generated, not
 * the command that asked for it.
 *
 *   InvalidArgumentException — the caller asked for something unusable
 *     InvalidPublishTargetException
 */
interface CommandException extends HotUiException
{
}
