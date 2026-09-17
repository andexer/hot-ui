<?php

declare(strict_types=1);

namespace Components\Support;

use Components\Support\Exception\UnbalancedTagException;

/**
 * Transpiles <ui:…> / <blocks:…> tag syntax into Hot-UI streaming calls.
 *
 * Turns markup like:
 *
 *   <ui:card class="w-full">
 *     <ui:slot name="header"><h2>Hola</h2></ui:slot>
 *     Cuerpo
 *   </ui:card>
 *
 * into plain PHP that drives Ui::open()/into()/close(), so views read like
 * Blade/Flux markup instead of echo + ob_start()/ob_get_clean(). Everything
 * that is not a namespaced component tag is preserved verbatim: native HTML,
 * PHP blocks, and the whitespace between tags.
 *
 * Attribute semantics (hybrid):
 *   - class="a"        static class, merged with any :class / @class below
 *   - :class="$x"      dynamic class (PHP expression), evaluated at render
 *   - @class([...])    conditional classes (Blade-style list), merged too
 *   - style="..."      static style, merged with :style / @style below
 *   - :style="$x"      dynamic style (PHP expression)
 *   - @style([...])    conditional styles (Blade-style list), merged too
 *   - @click="go()"    Alpine event → x-on:click (modifiers kept)
 *   - x-*              Alpine directive, literal string
 *   - :data-*  :aria-* Alpine binding on the component, literal string
 *   - data-*   aria-*  literal string
 *   - :prop="$v"       other colon keys evaluate PHP expressions
 *   - {{ $bag }}      spreads the bag's ->all() into props at this position
 *   - slot="header"    only on self-closing tags: wraps into parent's named slot
 *   - disabled         bare attribute → true
 *
 * Zero dependencies and no DOMDocument: a small hand-rolled scanner keeps the
 * library plain-PHP and fast enough to run on every request.
 */
final class ViewCompiler
{
    private const NAMESPACES = ['ui', 'blocks'];

    /**
     * Compiles tag syntax to PHP. Returns the source unchanged when it contains
     * no namespaced component tags.
     *
     * @throws UnbalancedTagException On unbalanced or malformed tags.
     */
    public function compile(string $source): string
    {
        $tokens = $this->tokenize($source);

        return $this->emit($tokens);
    }

    /**
     * @return list<array<int, mixed>>
     */
    private function tokenize(string $source): array
    {
        $length = strlen($source);
        $tokens = [];

        $i = 0;
        while ($i < $length) {
            if ($source[$i] !== '<') {
                $next = strpos($source, '<', $i);
                $end = $next === false ? $length : $next;
                $tokens[] = ['text', substr($source, $i, $end - $i)];
                $i = $end;
                continue;
            }

            // PHP open/echo blocks are passed through verbatim so markup
            // written inside PHP strings is never rewritten below.
            if (substr($source, $i, 5) === '<?php' || substr($source, $i, 3) === '<?=') {
                $close = strpos($source, '?>', $i + 2);
                $end = $close === false ? $length : $close + 2;
                $tokens[] = ['php', substr($source, $i, $end - $i)];
                $i = $end;
                continue;
            }

            if (substr($source, $i, 2) === '</') {
                $closeTag = $this->readCloseTag($source, $i);
                if ($closeTag !== null) {
                    [$lengthFrom, $ns, $name] = $closeTag;
                    $tokens[] = $name === 'slot' && $ns === 'ui'
                        ? ['slot-close']
                        : ['close', "{$ns}.{$name}"];
                    $i += $lengthFrom;
                    continue;
                }
            }

            $openTag = $this->readTag($source, $i);
            if ($openTag !== null) {
                [$lengthFrom, $ns, $name, $attrsInner, $self] = $openTag;

                if ($name === 'slot' && $ns === 'ui') {
                    $tokens[] = $self ? ['slot-self', $attrsInner] : ['slot-open', $attrsInner];
                } else {
                    $tokens[] = $self
                        ? ['self', "{$ns}.{$name}", $attrsInner]
                        : ['open', "{$ns}.{$name}", $attrsInner];
                }

                $i += $lengthFrom;
                continue;
            }

            // Foreign HTML element, comment or script/style block: consume the
            // whole element as text so "<ui:…>" inside attribute values, JS or
            // comments is never mistaken for a component.
            $foreign = $this->readForeignElement($source, $i);
            if ($foreign !== null) {
                [$lengthFrom] = $foreign;
                $tokens[] = ['text', substr($source, $i, $lengthFrom)];
                $i += $lengthFrom;
                continue;
            }

            // Not a component tag: keep the '<' as plain text and advance.
            $tokens[] = ['text', '<'];
            $i++;
        }

        return $tokens;
    }

    /**
     * Reads an opening tag. Returns [length, ns, name, attrInner, selfClosing]
     * or null when the position is not a namespaced component tag.
     *
     * @return array{0: int, 1: string, 2: string, 3: string, 4: bool}|null
     */
    private function readTag(string $source, int $offset): ?array
    {
        $length = strlen($source);

        $pos = $offset + 1;
        $ns = $this->readIdentifier($source, $pos, '');
        if ($ns === '' || ! in_array($ns, self::NAMESPACES, true)) {
            return null;
        }
        if (($source[$pos] ?? '') !== ':') {
            return null;
        }
        $pos++;

        $name = $this->readIdentifier($source, $pos, '-_');
        if ($name === '') {
            return null;
        }

        $inner = '';
        $quote = null;
        $parens = 0;
        while ($pos < $length) {
            $char = $source[$pos];

            if ($quote !== null) {
                if ($char === '\\') {
                    $inner .= $char;
                    $pos++;
                    if ($pos < $length) {
                        $inner .= $source[$pos];
                        $pos++;
                    }
                    continue;
                }
                $inner .= $char;
                if ($char === $quote) {
                    $quote = null;
                }
                $pos++;
                continue;
            }

            if ($char === '"' || $char === "'") {
                $quote = $char;
                $inner .= $char;
                $pos++;
                continue;
            }

            // Balanced parenthesis groups (e.g. @class([...])) may contain
            // unquoted '>' (the => arrow); only an ungrouped '>' closes a tag.
            if ($char === '(') {
                $parens++;
            } elseif ($char === ')') {
                $parens--;
            }

            if ($char === '>' && $parens === 0) {
                break;
            }

            $inner .= $char;
            $pos++;
        }

        if (($source[$pos] ?? '') !== '>') {
            return null;
        }

        $selfClosing = str_ends_with(trim($inner), '/');
        $attrsInner = $selfClosing ? substr($inner, 0, -1) : $inner;

        return [$pos - $offset + 1, $ns, $name, $attrsInner, $selfClosing];
    }

    /**
     * Reads a closing tag. Returns [length, ns, name] or null.
     *
     * @return array{0: int, 1: string, 2: string}|null
     */
    private function readCloseTag(string $source, int $offset): ?array
    {
        $length = strlen($source);

        $pos = $offset + 2;
        $ns = $this->readIdentifier($source, $pos, '');
        if ($ns === '' || ! in_array($ns, self::NAMESPACES, true)) {
            return null;
        }
        if (($source[$pos] ?? '') !== ':') {
            return null;
        }
        $pos++;

        $name = $this->readIdentifier($source, $pos, '-_');
        if ($name === '') {
            return null;
        }

        while (($source[$pos] ?? '') === ' ' || ($source[$pos] ?? '') === "\t" || ($source[$pos] ?? '') === "\n" || ($source[$pos] ?? '') === "\r") {
            $pos++;
        }
        if (($source[$pos] ?? '') === '>') {
            return [$pos - $offset + 1, $ns, $name];
        }

        return null;
    }

    /**
     * Reads a foreign (non-component) element or comment as raw text. Returns
     * [length] consumed from $offset, or null when the position is not the
     * start of a plausible HTML element.
     *
     * <script> and <style> bodies are swallowed whole so literal "<ui:…>"
     * strings inside them stay untouched; same for <!-- comments -->.
     *
     * @return array{0: int}|null
     */
    private function readForeignElement(string $source, int $offset): ?array
    {
        $length = strlen($source);
        $pos = $offset + 1;

        if ($pos >= $length) {
            return null;
        }

        $char = $source[$pos];
        if ($char === '/') {
            $pos++;
            if ($pos >= $length || ! ctype_alnum($source[$pos])) {
                return null;
            }

            $end = $this->findTagEnd($source, $pos);

            return $end === null ? null : [$end - $offset + 1];
        }

        if ($char === '!') {
            if (substr($source, $offset, 4) === '<!--') {
                $close = strpos($source, '-->', $offset + 4);
                $end = $close === false ? $length : $close + 3;

                return [$end - $offset];
            }

            $end = $this->findTagEnd($source, $pos);

            return $end === null ? null : [$end - $offset + 1];
        }

        if (! ctype_alnum($char)) {
            return null;
        }

        $name = '';
        while ($pos < $length && (ctype_alnum($source[$pos]) || $source[$pos] === '-' || $source[$pos] === '_')) {
            $name .= $source[$pos];
            $pos++;
        }

        $end = $this->findTagEnd($source, $pos);
        if ($end === null) {
            return null;
        }

        if (in_array(strtolower($name), ['script', 'style'], true)) {
            $closing = strripos($source, '</'.$name, $end);
            if ($closing !== false) {
                $closeEnd = $this->findTagEnd($source, $closing + 2 + strlen($name));

                return $closeEnd === null ? null : [$closeEnd - $offset + 1];
            }
        }

        return [$end - $offset + 1];
    }

    /**
     * Position of the first unquoted '>' at or after $offset.
     */
    private function findTagEnd(string $source, int $offset): ?int
    {
        $length = strlen($source);
        $quote = null;

        for ($pos = $offset; $pos < $length; $pos++) {
            $char = $source[$pos];

            if ($quote !== null) {
                if ($char === '\\') {
                    $pos++;
                    continue;
                }
                if ($char === $quote) {
                    $quote = null;
                }
                continue;
            }

            if ($char === '"' || $char === "'") {
                $quote = $char;
                continue;
            }

            if ($char === '>') {
                return $pos;
            }
        }

        return null;
    }

    private function readIdentifier(string $source, int &$pos, string $extra): string
    {
        $out = '';
        while ($pos < strlen($source)) {
            $char = $source[$pos];
            if (ctype_alnum($char) || $char === '_' || ($extra !== '' && str_contains($extra, $char))) {
                $out .= $char;
                $pos++;
                continue;
            }
            break;
        }

        return $out;
    }

    /**
     * Walks the token stream, emitting streaming calls and validating balance.
     *
     * @param list<array<int, mixed>> $tokens
     */
    private function emit(array $tokens): string
    {
        if ($tokens === []) {
            return '';
        }

        $out = '';
        $stack = [];
        $used = false;

        foreach ($tokens as $token) {
            switch ($token[0]) {
                case 'text':
                case 'php':
                    $out .= $token[1];
                    break;

                case 'open':
                    $used = true;
                    $stack[] = $token[1];
                    $out .= '<?php $__ui->open('.var_export($token[1], true).', '.$this->props($token[2]).'); ?>';
                    break;

                case 'self':
                    $used = true;
                    $slot = $this->inlineSlot($token[2]);
                    $props = $this->props($token[2], $slot === null ? [] : ['slot']);
                    if ($slot !== null) {
                        // <ui:button slot="header" /> wraps the component into the
                        // parent's named slot (Flux-compatible inline slot).
                        $out .= '<?php $__ui->into('.var_export($slot, true).'); echo $__ui->renderComponent('.var_export($token[1], true).', ['.$props.']); $__ui->into(); ?>';
                    } else {
                        $out .= '<?php echo $__ui->renderComponent('.var_export($token[1], true).', ['.$props.']); ?>';
                    }
                    break;

                case 'slot-open':
                    $used = true;
                    $stack[] = 'slot';
                    $out .= '<?php $__ui->into('.$this->slotName($token[1]).'); ?>';
                    break;

                case 'slot-self':
                    $used = true;
                    $out .= '<?php $__ui->into('.$this->slotName($token[1]).'); $__ui->into(); ?>';
                    break;

                case 'slot-close':
                    $out .= '<?php $__ui->into(); ?>';
                    $this->pop($stack, 'slot', $tokens);
                    break;

                case 'close':
                    $out .= '<?php echo $__ui->close(); ?>';
                    $this->pop($stack, $token[1], $tokens);
                    break;
            }
        }

        if ($stack !== []) {
            throw UnbalancedTagException::unclosed($stack[array_key_last($stack)]);
        }

        if (! $used) {
            return $out;
        }

        $prepend = '<?php $__ui ??= \Components\Ui::shared(); ?>';

        // Keep declare(strict_types=1) as the very first statement: the binding
        // snippet must land after the leading PHP block, not before it.
        if (preg_match('/^(<\?php\s+declare\s*\(\s*strict_types\s*=\s*1\s*\);[\s\S]*?\?>)/', $out, $m) === 1) {
            return substr($out, 0, strlen($m[1])).$prepend.substr($out, strlen($m[1]));
        }

        return $prepend.$out;
    }

    /**
     * Pops a frame matching the expected tag, or raises a compile error.
     *
     * @param list<string>                  $stack
     * @param list<array<int, mixed>>       $tokens
     */
    private function pop(array &$stack, string $expected, array $tokens): void
    {
        if ($stack === []) {
            throw UnbalancedTagException::unexpected($expected);
        }

        $top = array_pop($stack);
        if ($top !== $expected) {
            throw UnbalancedTagException::mismatched($expected, $top);
        }
    }

    /**
     * Builds the PHP props array for an opening/self-closing tag.
     *
     * @param string       $attrsInner Raw attribute string between the tag name and '>'.
     * @param list<string> $exclude    Attribute names dropped from the output
     *                                 (used by inline slot= handling).
     */
    private function props(string $attrsInner, array $exclude = []): string
    {
        $entries = [];
        $classes = [];
        $styles = [];
        $classIndex = null;
        $styleIndex = null;

        $pos = 0;
        $length = strlen($attrsInner);
        while ($pos < $length) {
            if (($attrsInner[$pos] ?? '') === ' ' || ($attrsInner[$pos] ?? '') === "\t" || ($attrsInner[$pos] ?? '') === "\n" || ($attrsInner[$pos] ?? '') === "\r") {
                $pos++;
                continue;
            }

            // {{ $attributes }} → spread the bag (or any expression exposing
            // ->all()) into the props array at this exact position.
            if (substr($attrsInner, $pos, 2) === '{{') {
                $close = strpos($attrsInner, '}}', $pos + 2);
                if ($close !== false) {
                    $expr = trim(substr($attrsInner, $pos + 2, $close - $pos - 2));
                    if ($expr !== '') {
                        $entries[] = ['bag', $expr];
                    }
                    $pos = $close + 2;
                    continue;
                }
            }

            $key = $this->readAttrName($attrsInner, $pos);
            if ($key === '') {
                $pos++;
                continue;
            }

            $this->skipWhitespace($attrsInner, $pos);
            $hasValue = ($attrsInner[$pos] ?? '') === '=';
            if ($hasValue) {
                $pos++;
                $this->skipWhitespace($attrsInner, $pos);
                $value = $this->readAttrValue($attrsInner, $pos);
            } elseif (($attrsInner[$pos] ?? '') === '(') {
                // Flux-style attached value: @class([...]) / @style([...]).
                $hasValue = true;
                $value = $this->readAttrValue($attrsInner, $pos);
            }

            if (in_array($key, $exclude, true)) {
                continue;
            }

            $entry = $this->attribute($key, $hasValue, $value ?? null);

            $bucket = match ($entry[0]) {
                'classstr', 'classexpr', 'classcond' => 'class',
                'stylestr', 'styleexpr', 'stylecond' => 'style',
                default => null,
            };

            if ($bucket === 'class') {
                [$chunkKind] = $entry;
                $classes[] = match ($chunkKind) {
                    'classexpr' => ['expr', (string) ($entry[1] ?? '')],
                    'classcond' => ['cond', (string) ($entry[1] ?? '')],
                    default => ['str', (string) ($entry[1] ?? '')],
                };
                if ($classIndex === null) {
                    $classIndex = count($entries);
                }
                $entries[] = null;
                continue;
            }

            if ($bucket === 'style') {
                [$chunkKind] = $entry;
                $styles[] = match ($chunkKind) {
                    'styleexpr' => ['expr', (string) ($entry[1] ?? '')],
                    'stylecond' => ['cond', (string) ($entry[1] ?? '')],
                    default => ['str', (string) ($entry[1] ?? '')],
                };
                if ($styleIndex === null) {
                    $styleIndex = count($entries);
                }
                $entries[] = null;
                continue;
            }

            $entries[] = $entry;
        }

        // Position each merged entry where the first chunk of its kind appeared.
        if ($classIndex !== null) {
            $entries[$classIndex] = $this->mergeClasses($classes);
        }
        if ($styleIndex !== null) {
            $entries[$styleIndex] = $this->mergeStyles($styles);
        }

        $parts = [];
        foreach ($entries as $entry) {
            if ($entry === null) {
                continue;
            }

            $parts[] = $this->renderEntry($entry);
        }

        return '['.implode(', ', $parts).']';
    }

    /**
     * Whether a raw attribute value looks like a conditional-class list: an
     * attached parenthesis group (Flux style) or any array literal.
     */
    private function isClassDirective(?string $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        $first = $value[0];

        return $first === '(' || $first === '[';
    }

    /**
     * Inner expression of a @class(...)/@style(...) value; parenthesis groups
     * are unwrapped, array literals pass through unchanged.
     */
    private function classDirectiveBody(string $value): string
    {
        return str_starts_with($value, '(') ? substr($value, 1, -1) : $value;
    }

    /**
     * @return array{0: string, 1: mixed}
     */
    private function attribute(string $key, bool $hasValue, ?string $value): array
    {
        $colon = str_starts_with($key, ':');
        $inner = $colon ? substr($key, 1) : $key;

        if ($key === '@class' && $hasValue && $this->isClassDirective($value)) {
            return ['classcond', $this->classDirectiveBody($value)];
        }
        if ($key === '@style' && $hasValue && $this->isClassDirective($value)) {
            return ['stylecond', $this->classDirectiveBody($value)];
        }
        if (! $colon && $key === 'class' && $hasValue) {
            return ['classstr', $value ?? ''];
        }
        if ($colon && $inner === 'class') {
            return ['classexpr', $value ?? 'true'];
        }
        if (! $colon && $key === 'style' && $hasValue) {
            return ['stylestr', $value ?? ''];
        }
        if ($colon && $inner === 'style') {
            return ['styleexpr', $value ?? 'true'];
        }

        if ($colon) {
            if (str_starts_with($inner, 'data-') || str_starts_with($inner, 'aria-')) {
                return ['str', $key, $value ?? ''];
            }
            if (! $hasValue) {
                return ['bool', $inner];
            }

            return ['expr', $inner, $value ?? ''];
        }

        if (str_starts_with($key, '@')) {
            return ['str', 'x-on:'.substr($key, 1), $value ?? ''];
        }
        if (str_starts_with($key, 'x-') || str_starts_with($key, 'data-') || str_starts_with($key, 'aria-')) {
            return ['str', $key, $value ?? ''];
        }

        if (! $hasValue) {
            return ['bool', $key];
        }

        return ['str', $key, $value ?? ''];
    }

    /**
     * @return array{0: string, 1: mixed}
     */
    private function renderEntry(array $entry): string
    {
        switch ($entry[0]) {
            case 'bool':
                return var_export($entry[1], true).' => true';

            case 'str':
                return var_export($entry[1], true).' => '.var_export($entry[2], true);

            case 'all':
                return var_export($entry[1], true).' => '.trim((string) $entry[2]);

            case 'bag':
                return '...('.trim((string) $entry[1]).'->all())';

            case 'expr':
            default:
                return var_export($entry[1], true).' => '.trim((string) $entry[2]);
        }
    }

    /**
     * Merges class chunks (static "class", dynamic ":class", conditional
     * "@class") into one prop so downstream twMerge resolves conflicts once.
     * Conditional chunks call Classes::render() at runtime; dynamic chunks keep
     * their PHP expression evaluated at render time.
     *
     * @param list<array{0: string, 1: string}> $classes
     */
    private function mergeClasses(array $classes): array
    {
        $static = [];
        $dynamic = [];
        foreach ($classes as [$kind, $value]) {
            if ($kind === 'str') {
                $static[] = $value;
            } else {
                $dynamic[] = $this->wrapChunk($kind, $value);
            }
        }

        if ($dynamic === []) {
            return ['str', 'class', implode(' ', $static)];
        }

        $exprs = implode(" . ' ' . ", $dynamic);

        $value = $static !== []
            ? var_export(implode(' ', $static), true)." . ' ' . ".$exprs
            : (count($dynamic) === 1 ? $dynamic[0] : $exprs);

        return ['all', 'class', $value];
    }

    /**
     * Merges style chunks (static "style", dynamic ":style", conditional
     * "@style") into one prop; pieces are joined with "; " so downstream
     * AttributeBag::merge keeps declarations well-formed.
     *
     * @param list<array{0: string, 1: string}> $styles
     */
    private function mergeStyles(array $styles): array
    {
        $static = [];
        $dynamic = [];
        foreach ($styles as [$kind, $value]) {
            if ($kind === 'str') {
                $static[] = $value;
            } else {
                $dynamic[] = $this->wrapChunk($kind, $value);
            }
        }

        if ($dynamic === []) {
            return ['str', 'style', implode('; ', $static)];
        }

        $exprs = implode(" . '; ' . ", $dynamic);

        $value = $static !== []
            ? var_export(implode('; ', $static), true)." . '; ' . ".$exprs
            : (count($dynamic) === 1 ? $dynamic[0] : $exprs);

        return ['all', 'style', $value];
    }

    /**
     * Wraps a dynamic style/class chunk into a runtime string expression.
     */
    private function wrapChunk(string $kind, string $value): string
    {
        if ($kind === 'cond') {
            return '(string)(\Components\Support\Classes::render('.trim($value).'))';
        }

        return '(string)('.trim($value).')';
    }

    private function slotName(string $attrsInner): string
    {
        $result = preg_match('/\bname\s*=\s*(["\'])(.*?)\1/', $attrsInner, $m) === 1;

        return $result ? var_export($m[2], true) : 'null';
    }

    /**
     * Named slot used by an inline `<ui:… slot="name" />`, or null when absent.
     */
    private function inlineSlot(string $attrsInner): ?string
    {
        return preg_match('/\bslot\s*=\s*(["\'])(.*?)\1/', $attrsInner, $m) === 1
            ? $m[2]
            : null;
    }

    private function readAttrName(string $source, int &$pos): string
    {
        $out = '';
        while ($pos < strlen($source)) {
            $char = $source[$pos];
            if (ctype_alnum($char) || $char === '_' || str_contains(':@.-[]', $char)) {
                $out .= $char;
                $pos++;
                continue;
            }
            break;
        }

        return $out;
    }

    private function readAttrValue(string $source, int &$pos): string
    {
        $length = strlen($source);
        $quote = $source[$pos] ?? '';

        // Balanced-parentheses literal: used by @class([...])/@style([...]).
        // Handles nested parens and quoted strings, so expressions like
        // @class(['px-4' => $active, 'w-full']) survive as one value.
        if ($quote === '(') {
            $depth = 0;
            $out = '';
            while ($pos < $length) {
                $char = $source[$pos];
                if ($char === '"' || $char === "'") {
                    $out .= $char;
                    $pos++;
                    $quote = $char;
                    while ($pos < $length) {
                        $inner = $source[$pos];
                        $out .= $inner;
                        $pos++;
                        if ($inner === '\\' && $pos < $length) {
                            $out .= $source[$pos];
                            $pos++;
                            continue;
                        }
                        if ($inner === $quote) {
                            break;
                        }
                    }
                    $quote = null;
                    continue;
                }
                if ($char === '(') {
                    $depth++;
                } elseif ($char === ')') {
                    $depth--;
                    $out .= $char;
                    $pos++;
                    if ($depth === 0) {
                        return $out;
                    }
                    continue;
                }
                $out .= $char;
                $pos++;
            }

            return $out;
        }

        if ($quote !== '"' && $quote !== "'") {
            $out = '';
            while ($pos < $length && ($source[$pos] ?? '') !== ' ' && ($source[$pos] ?? '') !== "\t" && ($source[$pos] ?? '') !== '>') {
                $out .= $source[$pos];
                $pos++;
            }

            return $out;
        }

        $pos++;
        $out = '';
        while ($pos < $length) {
            $char = $source[$pos];
            if ($char === '\\') {
                $out .= $char;
                $pos++;
                if ($pos < $length) {
                    $out .= $source[$pos];
                    $pos++;
                }
                continue;
            }
            if ($char === $quote) {
                $pos++;

                return $out;
            }
            $out .= $char;
            $pos++;
        }

        return $out;
    }

    private function skipWhitespace(string $source, int &$pos): void
    {
        while ($pos < strlen($source)) {
            $char = $source[$pos];
            if ($char === ' ' || $char === "\t" || $char === "\n" || $char === "\r") {
                $pos++;
                continue;
            }
            break;
        }
    }
}