<?php

declare(strict_types=1);

/**
 * Enforces the machine-checkable rules of the house PHP best-practices skill
 * (`.agents/skills/php-best-practices`):
 *
 *   1. No error suppression: the @ operator is banned      (rule 5.3).
 *   2. Every PHP file declares strict_types=1              (rule 1.1).
 *   3. Every function, method and closure declares a return
 *      type, except constructors and destructors, where
 *      PHP forbids one                                      (rule 1.2).
 *
 *   composer lint                  (or: php bin/lint.php)
 *   php bin/lint.php src/Components/Hotfire
 *
 * Scans src/, tests/ and bin/ by default. The published view templates under
 * views/ are markup with embedded PHP, not classes, so they stay out of scope.
 * Exits 0 when the package is clean and 1 on the first violation reported.
 */

/** Default scan roots, relative to the package root. */
const LINT_TARGETS = ['src', 'tests', 'bin'];

/** Magic methods PHP does not allow (or ignores) a return type on. */
const LINT_TYPELESS_MAGIC = ['__construct', '__destruct'];

/**
 * Every .php file below the given paths, sorted for stable output.
 *
 * @param list<string> $targets Files or directories.
 *
 * @return list<string>
 */
function lintFiles(array $targets): array
{
    $files = [];
    foreach ($targets as $target) {
        if (is_file($target)) {
            $files[] = strtr($target, '\\', '/');
            continue;
        }

        if (! is_dir($target)) {
            fwrite(STDERR, sprintf("lint: skipping missing path [%s]\n", $target));
            continue;
        }

        $entries = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($target, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY,
            RecursiveIteratorIterator::CATCH_GET_CHILD,
        );
        foreach ($entries as $entry) {
            if ($entry->isFile() && $entry->getExtension() === 'php') {
                $files[] = strtr($entry->getPathname(), '\\', '/');
            }
        }
    }

    sort($files);

    return $files;
}

/**
 * token_get_all() carries no line number on single-character tokens, so pair
 * every token with the line it starts on while walking the source once.
 *
 * @return list<array{id: int|string, text: string, line: int}>
 */
function lintTokens(string $source): array
{
    $line = 1;
    $tokens = [];
    foreach (token_get_all($source) as $token) {
        if (is_array($token)) {
            [$id, $text, $start] = $token;
            $tokens[] = ['id' => $id, 'text' => $text, 'line' => $start];
            $line = $start + substr_count($text, "\n");
            continue;
        }

        $tokens[] = ['id' => $token, 'text' => $token, 'line' => $line];
        $line += substr_count($token, "\n");
    }

    return $tokens;
}

/**
 * Violations found in one file, as "<line>: <message>" strings.
 *
 * @return list<string>
 */
function lintSource(string $source): array
{
    $tokens = lintTokens($source);

    return [
        ...lintSuppressions($tokens),
        ...lintDeclarations($tokens),
        ...lintReturnTypes($tokens),
    ];
}

/**
 * Rule 5.3: the @ operator is banned. Attributes (#[...]) and docblock tags
 * (@param, …) tokenize as their own tokens, so they never trip this check.
 *
 * @param list<array{id: int|string, text: string, line: int}> $tokens
 *
 * @return list<string>
 */
function lintSuppressions(array $tokens): array
{
    $violations = [];
    foreach ($tokens as $token) {
        if ($token['text'] === '@') {
            $violations[] = sprintf('%d: the @ error-suppression operator is banned (rule 5.3)', $token['line']);
        }
    }

    return $violations;
}

/**
 * Rule 1.1: strict_types must be the first statement of the file.
 *
 * @param list<array{id: int|string, text: string, line: int}> $tokens
 *
 * @return list<string>
 */
function lintDeclarations(array $tokens): array
{
    foreach ($tokens as $index => $token) {
        if (in_array($token['id'], [T_OPEN_TAG, T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
            continue;
        }

        if ($token['id'] !== T_DECLARE) {
            break;
        }

        $statement = '';
        for ($i = $index; isset($tokens[$i]) && $tokens[$i]['text'] !== ';'; ++$i) {
            $statement .= $tokens[$i]['text'];
        }

        if (preg_match('/strict_types\s*=\s*1/', $statement) === 1) {
            return [];
        }

        break;
    }

    return ['1: missing declare(strict_types=1) (rule 1.1)'];
}

/**
 * Rule 1.2: every function, method and closure declares a return type.
 *
 * @param list<array{id: int|string, text: string, line: int}> $tokens
 *
 * @return list<string>
 */
function lintReturnTypes(array $tokens): array
{
    $violations = [];
    foreach ($tokens as $index => $token) {
        if ($token['id'] !== T_FUNCTION && $token['id'] !== T_FN) {
            continue;
        }

        $name = lintFunctionName($tokens, $index);
        if ($name !== null && in_array($name, LINT_TYPELESS_MAGIC, true)) {
            continue;
        }

        if (lintHasReturnType($tokens, $index)) {
            continue;
        }

        $violations[] = sprintf(
            '%d: %s declares no return type (rule 1.2)',
            $token['line'],
            $name === null ? 'the closure' : $name.'()',
        );
    }

    return $violations;
}

/**
 * Name of the T_FUNCTION at $index, or null for closures / arrow functions.
 *
 * @param list<array{id: int|string, text: string, line: int}> $tokens
 */
function lintFunctionName(array $tokens, int $index): ?string
{
    for ($i = $index + 1; isset($tokens[$i]); ++$i) {
        $token = $tokens[$i];
        if (in_array($token['id'], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true) || $token['text'] === '&') {
            continue;
        }

        return $token['id'] === T_STRING ? $token['text'] : null;
    }

    return null;
}

/**
 * True when the signature at $index is followed by a return type: after the
 * parameter list (and any `use (...)` clause of a closure) comes ":" rather
 * than "{", ";" or the "=>" starting an arrow function body.
 *
 * @param list<array{id: int|string, text: string, line: int}> $tokens
 */
function lintHasReturnType(array $tokens, int $index): bool
{
    $open = lintFindToken($tokens, $index, '(');
    $close = $open === null ? null : lintClosingParen($tokens, $open);
    if ($close === null) {
        return false;
    }

    $next = lintNextMeaningful($tokens, $close + 1);
    if ($next === null) {
        return false;
    }

    if ($tokens[$next]['text'] === 'use') {
        $useOpen = lintFindToken($tokens, $next, '(');
        $useClose = $useOpen === null ? null : lintClosingParen($tokens, $useOpen);
        if ($useClose === null) {
            return false;
        }
        $next = lintNextMeaningful($tokens, $useClose + 1);
    }

    return $next !== null && $tokens[$next]['text'] === ':';
}

/**
 * Index of the next token with the given text at or after $index.
 *
 * @param list<array{id: int|string, text: string, line: int}> $tokens
 */
function lintFindToken(array $tokens, int $index, string $text): ?int
{
    for ($i = $index; isset($tokens[$i]); ++$i) {
        if ($tokens[$i]['text'] === $text) {
            return $i;
        }
    }

    return null;
}

/**
 * Index of the ")" closing the "(" at $open, honouring nesting.
 *
 * @param list<array{id: int|string, text: string, line: int}> $tokens
 */
function lintClosingParen(array $tokens, int $open): ?int
{
    $depth = 0;
    for ($i = $open; isset($tokens[$i]); ++$i) {
        if ($tokens[$i]['text'] === '(') {
            ++$depth;
        } elseif ($tokens[$i]['text'] === ')') {
            --$depth;
            if ($depth === 0) {
                return $i;
            }
        }
    }

    return null;
}

/**
 * Index of the next token that is neither whitespace nor a comment.
 *
 * @param list<array{id: int|string, text: string, line: int}> $tokens
 */
function lintNextMeaningful(array $tokens, int $index): ?int
{
    for ($i = $index; isset($tokens[$i]); ++$i) {
        if (! in_array($tokens[$i]['id'], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
            return $i;
        }
    }

    return null;
}

$targets = array_slice($argv, 1) ?: LINT_TARGETS;
$files = lintFiles($targets);
$violations = 0;

foreach ($files as $file) {
    $source = file_get_contents($file);
    if ($source === false) {
        fwrite(STDERR, sprintf("lint: cannot read [%s]\n", $file));
        ++$violations;
        continue;
    }

    foreach (lintSource($source) as $message) {
        printf("%s:%s\n", $file, $message);
        ++$violations;
    }
}

if ($violations > 0) {
    printf("\nLint: %d violation%s.\n", $violations, $violations === 1 ? '' : 's');
    exit(1);
}

printf(
    "Lint: %d file%s clean — no @ suppression, strict_types everywhere, every signature typed.\n",
    count($files),
    count($files) === 1 ? '' : 's',
);
exit(0);
