<?php

declare(strict_types=1);

/**
 * Enables the versioned git hooks of this checkout by pointing the LOCAL
 * core.hooksPath at .githooks/, so the pre-commit gate (which lints the staged
 * PHP) runs on every commit.
 *
 *   composer hooks                      (or: php bin/install-hooks.php)
 *   php bin/install-hooks.php --status
 *   php bin/install-hooks.php --uninstall
 *
 * Only this clone's .git/config is written: hooks are a checkout setting, not a
 * repository one, so a fresh clone stays untouched until someone opts in, and a
 * host project that vendors the package never inherits them.
 */

const HOOKS_PATH = '.githooks';
const HOOK_FILE = '.githooks/pre-commit';

/**
 * Runs a command in the package root without a shell, so arguments are never
 * re-split or expanded.
 *
 * @param list<string> $command
 *
 * @return array{code: int, output: string}
 */
function run(array $command): array
{
    $pipes = [];
    $process = proc_open(
        $command,
        [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
        $pipes,
        dirname(__DIR__),
    );
    if (! is_resource($process)) {
        return ['code' => 1, 'output' => 'cannot start '.implode(' ', $command)];
    }

    $output = (string) stream_get_contents($pipes[1]).(string) stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    return ['code' => proc_close($process), 'output' => trim($output)];
}

/**
 * @param list<string> $command
 *
 * @return array{code: int, output: string}
 */
function git(array $command): array
{
    return run(['git', ...$command]);
}

function hooksPath(): ?string
{
    $result = git(['config', '--local', '--get', 'core.hooksPath']);

    return $result['code'] === 0 && $result['output'] !== '' ? $result['output'] : null;
}

function report(string $message): void
{
    fwrite(STDOUT, $message."\n");
}

function fail(string $message): never
{
    fwrite(STDERR, $message."\n");
    exit(1);
}

$arguments = array_slice($argv, 1);
$mode = 'install';
foreach ($arguments as $argument) {
    if (in_array($argument, ['--uninstall', '--remove'], true)) {
        $mode = 'uninstall';
    } elseif (in_array($argument, ['--status', '--check'], true)) {
        $mode = 'status';
    }
}

$repository = git(['rev-parse', '--show-toplevel']);
$current = hooksPath();

if ($mode === 'status') {
    report(sprintf('core.hooksPath: %s', $current ?? '(unset)'));
    if ($current === HOOKS_PATH) {
        report(sprintf(
            'pre-commit hook: %s (%s)',
            is_file(HOOK_FILE) ? 'present' : 'MISSING',
            is_executable(HOOK_FILE) ? 'executable' : 'not executable',
        ));
    }
    exit($current === HOOKS_PATH ? 0 : 1);
}

if ($mode === 'uninstall') {
    if ($current !== HOOKS_PATH) {
        report(sprintf(
            'core.hooksPath is %s, not %s: left untouched.',
            $current ?? '(unset)',
            HOOKS_PATH,
        ));
        exit(0);
    }

    $result = git(['config', '--local', '--unset', 'core.hooksPath']);
    if ($result['code'] !== 0) {
        fail('cannot unset core.hooksPath: '.$result['output']);
    }

    report('core.hooksPath unset: hooks are disabled for this clone.');
    exit(0);
}

if ($repository['code'] !== 0) {
    fail('not a git checkout: run this from a clone of the repository.');
}
if (! is_file(HOOK_FILE)) {
    fail(sprintf('%s is missing: the hooks directory is incomplete.', HOOK_FILE));
}
if (! is_executable(HOOK_FILE) && ! chmod(HOOK_FILE, 0o755) && ! is_executable(HOOK_FILE)) {
    fail(sprintf('cannot make %s executable; git would ignore it.', HOOK_FILE));
}

$result = git(['config', '--local', 'core.hooksPath', HOOKS_PATH]);
if ($result['code'] !== 0) {
    fail('cannot set core.hooksPath: '.$result['output']);
}

report(sprintf('core.hooksPath = %s (%s)', HOOKS_PATH, $repository['output']));
report('pre-commit runs `php bin/lint.php` on the staged PHP files.');
report('Bypass a commit with `git commit --no-verify`; undo with `--uninstall`.');
