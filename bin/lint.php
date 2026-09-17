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
 *   4. Every parameter declares a type, including $this-
 *      captured closures and variadics                      (rule 1.1 param).
 *   5. No throw of a generic SPL exception: the package
 *      throws domain exceptions instead                  (rule 5.1).
 *   6. Package source never even constructs one, and
 *      every exception class under src/ joins a family
 *      by implementing a marker interface                (rule 5.1).
 *
 *   composer lint                  (or: php bin/lint.php)
 *   php bin/lint.php src/Components/Hotfire
 *
 * Scans src/, tests/ and bin/ by default. The published view templates under
 * views/ are markup with embedded PHP, not classes, so they stay out of scope.
 *
 * The last two checks run on package source only (a path under src/): the suite
 * constructs a generic SPL exception on purpose to prove how the package reacts
 * to one it does not know, which is exactly what the rule forbids in shipped
 * code.
 *
 * Exits 0 when the package is clean and 1 on the first violation reported.
 */

/** Default scan roots, relative to the package root. */
const LINT_TARGETS = ['src', 'tests', 'bin'];

/** Magic methods PHP does not allow (or ignores) a return type on. */
const LINT_TYPELESS_MAGIC = ['__construct', '__destruct'];

/**
 * An exception class joins a family by implementing a marker interface, and
 * every marker in this package is named after it: HotUiException,
 * HotfireException, SupportException, CommandException.
 */
const LINT_MARKER_SUFFIX = 'Exception';

/**
 * SPL exception classes that exist to be subclassed. Throwing one of these
 * directly hides the failure mode from callers, who then have to match on the
 * message to tell "unknown group" from "directory not writable" (rule 5.1).
 * Only the bare name, or the same name behind a leading backslash, is matched:
 * a namespaced `Components\Exception\Foo` is a domain exception by definition.
 */
const LINT_GENERIC_EXCEPTIONS = [
    'Exception',
    'RuntimeException',
    'LogicException',
    'InvalidArgumentException',
    'DomainException',
    'LengthException',
    'OutOfRangeException',
    'RangeException',
    'UnexpectedValueException',
    'BadFunctionCallException',
    'BadMethodCallException',
    'OverflowException',
    'UnderflowException',
    'OutOfBoundsException',
];

/** Tokens that may precede a parameter type without being part of it. */
const LINT_PARAM_MODIFIERS = [
    T_PUBLIC,
    T_PROTECTED,
    T_PRIVATE,
    T_READONLY,
    T_VAR,
    T_FINAL,
    T_STATIC,
];

/**
 * Modifiers that may precede a parameter type, including the asymmetric
 * visibility tokens of PHP 8.4+ (private(set) …), which tokenize as one token
 * there but as "private" + "(set)" on 8.2/8.3. The tokenizer decides, so the
 * list is built at runtime instead of referencing constants that may not exist.
 *
 * @return list<int>
 */
function lintParamModifiers(): array
{
    $modifiers = LINT_PARAM_MODIFIERS;
    foreach (['T_PUBLIC_SET', 'T_PROTECTED_SET', 'T_PRIVATE_SET'] as $constant) {
        if (defined($constant)) {
            $modifiers[] = (int) constant($constant);
        }
    }

    return $modifiers;
}

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
function lintSource(string $source, bool $packageSource): array
{
    $tokens = lintTokens($source);
    $generic = lintGenericExceptions($tokens, $packageSource);

    return [
        ...lintSuppressions($tokens),
        ...lintDeclarations($tokens),
        ...lintReturnTypes($tokens),
        ...lintParameterTypes($tokens),
        ...$generic,
        ...($packageSource ? lintExceptionFamilies($tokens) : []),
    ];
}

/**
 * True for the package's own source tree, where rules 5 and 6 bite. The check
 * is on the path, not on the namespace, so it keeps working when the linter is
 * pointed at an absolute path from another directory.
 */
function lintIsPackageSource(string $file): bool
{
    $normalized = strtr($file, '\\', '/');

    return str_starts_with($normalized, 'src/') || str_contains($normalized, '/src/');
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
 * Rule 1.1 (parameter types): every parameter declares a type. Untyped
 * signatures are the one place where PHP 8 silently accepts anything, so the
 * rule is checked on functions, methods, closures and arrow functions alike.
 *
 * @param list<array{id: int|string, text: string, line: int}> $tokens
 *
 * @return list<string>
 */
function lintParameterTypes(array $tokens): array
{
    $violations = [];
    foreach ($tokens as $index => $token) {
        if ($token['id'] !== T_FUNCTION && $token['id'] !== T_FN) {
            continue;
        }

        $open = lintFindToken($tokens, $index, '(');
        $close = $open === null ? null : lintClosingParen($tokens, $open);
        if ($open === null || $close === null) {
            continue;
        }

        foreach (lintParameterSegments($tokens, $open, $close) as $segment) {
            $variable = lintParameterVariable($tokens, $segment);
            if ($variable === null || lintSegmentHasType($tokens, $segment, $variable)) {
                continue;
            }

            $violations[] = sprintf(
                '%d: parameter %s declares no type (rule 1.1)',
                $tokens[$variable]['line'],
                $tokens[$variable]['text'],
            );
        }
    }

    return $violations;
}

/**
 * Token indices of every parameter between the parentheses at $open/$close,
 * split on the commas that sit outside strings, arrays, attributes and default
 * values. Whitespace and comments are dropped so a segment is pure syntax.
 *
 * @param list<array{id: int|string, text: string, line: int}> $tokens
 *
 * @return list<list<int>>
 */
function lintParameterSegments(array $tokens, int $open, int $close): array
{
    $segments = [];
    $segment = [];
    $depth = 0;
    $attribute = 0;

    for ($i = $open + 1; $i < $close; ++$i) {
        $id = $tokens[$i]['id'];
        $text = $tokens[$i]['text'];

        if (in_array($id, [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
            continue;
        }

        // #[Attr] keeps its commas: attributes never separate parameters.
        if ($id === T_ATTRIBUTE) {
            ++$attribute;
            $segment[] = $i;
            continue;
        }
        if ($attribute > 0) {
            if ($text === '[') {
                ++$attribute;
            } elseif ($text === ']') {
                --$attribute;
            }
            $segment[] = $i;
            continue;
        }

        if ($text === '(' || $text === '[' || $text === '{') {
            ++$depth;
        } elseif ($text === ')' || $text === ']' || $text === '}') {
            --$depth;
        } elseif ($text === ',' && $depth === 0) {
            if ($segment !== []) {
                $segments[] = $segment;
                $segment = [];
            }
            continue;
        }

        $segment[] = $i;
    }

    if ($segment !== []) {
        $segments[] = $segment;
    }

    return $segments;
}

/**
 * Index of the parameter variable inside a segment, or null when the segment
 * holds only punctuation (a trailing comma, a stray token).
 *
 * @param list<array{id: int|string, text: string, line: int}> $tokens
 * @param list<int>                                            $segment
 */
function lintParameterVariable(array $tokens, array $segment): ?int
{
    foreach ($segment as $index) {
        if ($tokens[$index]['id'] === T_VARIABLE) {
            return $index;
        }
    }

    return null;
}

/**
 * True when something in front of the parameter variable is a type. Promotion
 * modifiers (public/private(set)/readonly), references, variadics and to-be-
 * determined union parentheses are stripped first, so `(A&B)|null $x` counts
 * as typed while `#[Attr] &$x` does not.
 *
 * @param list<array{id: int|string, text: string, line: int}> $tokens
 * @param list<int>                                            $segment
 */
function lintSegmentHasType(array $tokens, array $segment, int $variable): bool
{
    $count = count($segment);
    $position = 0;

    while ($position < $count) {
        $index = $segment[$position];
        if ($index >= $variable) {
            return false;
        }

        $id = $tokens[$index]['id'];
        $text = $tokens[$index]['text'];

        // #[Attr] carries no type information: skip the whole attribute.
        if ($id === T_ATTRIBUTE) {
            $depth = 1;
            ++$position;
            while ($position < $count && $depth > 0) {
                $inner = $tokens[$segment[$position]]['text'];
                if ($inner === '[') {
                    ++$depth;
                } elseif ($inner === ']') {
                    --$depth;
                }
                ++$position;
            }
            continue;
        }

        if (in_array($id, lintParamModifiers(), true)) {
            // Asymmetric visibility on PHP 8.2/8.3: `private(set) string $x`.
            $position += lintIsSetVisibility($tokens, $segment, $position) ? 4 : 1;
            continue;
        }

        if ($text === '&' || $text === '...' || $text === '(' || $text === ')') {
            ++$position;
            continue;
        }

        return true;
    }

    return false;
}

/**
 * True when the modifier at $position is followed by `(set)`, the PHP 8.4
 * asymmetric-visibility syntax that belongs to the modifier, not to the type.
 *
 * @param list<array{id: int|string, text: string, line: int}> $tokens
 * @param list<int>                                            $segment
 */
function lintIsSetVisibility(array $tokens, array $segment, int $position): bool
{
    return ($tokens[$segment[$position + 1] ?? 0]['text'] ?? '') === '('
        && ($tokens[$segment[$position + 2] ?? 0]['text'] ?? '') === 'set'
        && ($tokens[$segment[$position + 3] ?? 0]['text'] ?? '') === ')';
}

/**
 * Rule 5.1: a generic SPL exception is banned. Callers must be able to catch
 * the failure mode, not string-match its message.
 *
 * The rule is checked on every instantiation, not only on the ones written
 * straight after `throw`: `$failure = new \RuntimeException('…')` thrown a line
 * later (or returned, or handed to a callback) is the same generic failure with
 * a detour. Inside src/ that is a violation wherever it appears; in the suite it
 * is allowed unless it is the direct operand of a throw, because a test may
 * need to prove how the package reacts to an exception it does not know.
 *
 * @param list<array{id: int|string, text: string, line: int}> $tokens
 *
 * @return list<string>
 */
function lintGenericExceptions(array $tokens, bool $packageSource): array
{
    $violations = [];
    foreach ($tokens as $index => $token) {
        if ($token['id'] !== T_NEW) {
            continue;
        }

        $class = lintNextMeaningful($tokens, $index + 1);
        if ($class === null) {
            continue;
        }

        $name = lintGenericExceptionName($tokens[$class]['text']);
        if ($name === null) {
            continue;
        }

        $thrown = lintPreviousMeaningful($tokens, $index - 1);
        if ($thrown !== null && $tokens[$thrown]['id'] === T_THROW) {
            $violations[] = sprintf(
                '%d: throwing \\%s is banned; throw a domain exception (rule 5.1)',
                $token['line'],
                $name,
            );
            continue;
        }

        if ($packageSource) {
            $violations[] = sprintf(
                '%d: constructing \\%s is banned; use a domain exception (rule 5.1)',
                $token['line'],
                $name,
            );
        }
    }

    return $violations;
}

/**
 * Rule 5.1, the other half: an exception class of the package joins a family by
 * implementing a marker interface, so callers keep one type to catch. A class
 * extending \RuntimeException with no marker is a generic exception wearing a
 * domain name — the failure mode the rule exists to prevent.
 *
 * @param list<array{id: int|string, text: string, line: int}> $tokens
 *
 * @return list<string>
 */
function lintExceptionFamilies(array $tokens): array
{
    $violations = [];
    foreach ($tokens as $index => $token) {
        if ($token['id'] !== T_CLASS) {
            continue;
        }

        // Anonymous classes (new class extends …) have no name to fix and, being
        // one-off values, no family to join.
        $before = lintPreviousMeaningful($tokens, $index - 1);
        if ($before !== null && $tokens[$before]['id'] === T_NEW) {
            continue;
        }

        $name = lintNextMeaningful($tokens, $index + 1);
        if ($name === null || $tokens[$name]['id'] !== T_STRING) {
            continue;
        }

        $header = lintClassHeader($tokens, $index);
        if ($header['base'] === null || lintGenericExceptionName($header['base']) === null) {
            continue;
        }
        if ($header['markers'] !== []) {
            continue;
        }

        $violations[] = sprintf(
            '%d: class %s extends \\%s without implementing a family marker; make it part of an exception family (rule 5.1)',
            $token['line'],
            $tokens[$name]['text'],
            ltrim($header['base'], '\\'),
        );
    }

    return $violations;
}

/**
 * Reads a class header between its name and the opening brace: the extends
 * target, plus every interface it implements (an empty list when it implements
 * none, or none that is named like a marker).
 *
 * @param list<array{id: int|string, text: string, line: int}> $tokens
 *
 * @return array{base: ?string, markers: list<string>}
 */
function lintClassHeader(array $tokens, int $index): array
{
    $base = null;
    $markers = [];
    $implementing = false;

    for ($i = $index + 1; isset($tokens[$i]); ++$i) {
        $id = $tokens[$i]['id'];
        $text = $tokens[$i]['text'];
        if ($text === '{' || $text === ';') {
            break;
        }

        if ($id === T_EXTENDS) {
            $target = lintNextMeaningful($tokens, $i + 1);
            $base = $target === null ? null : $tokens[$target]['text'];
            continue;
        }

        if ($id === T_IMPLEMENTS) {
            $implementing = true;
            continue;
        }

        if (! $implementing || ! in_array($id, [T_STRING, T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED], true)) {
            continue;
        }

        if (str_ends_with($text, LINT_MARKER_SUFFIX)) {
            $markers[] = $text;
        }
    }

    return ['base' => $base, 'markers' => $markers];
}

/**
 * Index of the previous token that is neither whitespace nor a comment.
 *
 * @param list<array{id: int|string, text: string, line: int}> $tokens
 */
function lintPreviousMeaningful(array $tokens, int $index): ?int
{
    for ($i = $index; $i >= 0; --$i) {
        if (! in_array($tokens[$i]['id'], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
            return $i;
        }
    }

    return null;
}

/**
 * The banned SPL class a `new` target names, or null when it is a domain
 * exception. Only a bare name or a single leading backslash is considered:
 * `Components\Exception\Foo` and `Foo\RuntimeException` are userland classes.
 */
function lintGenericExceptionName(string $text): ?string
{
    $bare = str_starts_with($text, '\\') ? substr($text, 1) : $text;
    if (str_contains($bare, '\\')) {
        return null;
    }

    return in_array($bare, LINT_GENERIC_EXCEPTIONS, true) ? $bare : null;
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

    foreach (lintSource($source, lintIsPackageSource($file)) as $message) {
        printf("%s:%s\n", $file, $message);
        ++$violations;
    }
}

if ($violations > 0) {
    printf("\nLint: %d violation%s.\n", $violations, $violations === 1 ? '' : 's');
    exit(1);
}

printf(
    "Lint: %d file%s clean — no @ suppression, strict_types everywhere, every signature typed, no generic exceptions.\n",
    count($files),
    count($files) === 1 ? '' : 's',
);
exit(0);
