<?php

declare(strict_types=1);

namespace Components\Support;

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
 *   - class="a"        static class, merged with any :class below
 *   - :class="$x"      dynamic class (PHP expression), evaluated at render
 *   - @click="go()"    Alpine event → x-on:click (modifiers kept)
 *   - x-*              Alpine directive, literal string
 *   - :data-*  :aria-* Alpine binding on the component, literal string
 *   - data-*   aria-*  literal string
 *   - :prop="$v"       other colon keys evaluate PHP expressions
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
     * @throws \InvalidArgumentException On unbalanced or malformed tags.
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

            if ($char === '>') {
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
                    $out .= '<?php echo $__ui->renderComponent('.var_export($token[1], true).', ['.$this->props($token[2]).']); ?>';
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
            throw new \InvalidArgumentException(sprintf(
                'Unclosed Hot-UI tag [<%s>] in compiled source; every <ui:/<blocks:> tag needs a matching close tag.',
                str_replace('.', ':', $stack[array_key_last($stack)]),
            ));
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
            throw new \InvalidArgumentException(sprintf(
                'Unexpected closing tag [</%s>] without an opening tag in compiled source.',
                str_replace('.', ':', $expected),
            ));
        }

        $top = array_pop($stack);
        if ($top !== $expected) {
            throw new \InvalidArgumentException(sprintf(
                'Mismatched closing tag [</%s>]; expected [</%s>].',
                str_replace('.', ':', $expected),
                str_replace('.', ':', $top),
            ));
        }
    }

    /**
     * Builds the PHP props array for an opening/self-closing tag.
     */
    private function props(string $attrsInner): string
    {
        $entries = [];
        $classes = [];

        $pos = 0;
        $length = strlen($attrsInner);
        while ($pos < $length) {
            if (($attrsInner[$pos] ?? '') === ' ' || ($attrsInner[$pos] ?? '') === "\t" || ($attrsInner[$pos] ?? '') === "\n" || ($attrsInner[$pos] ?? '') === "\r") {
                $pos++;
                continue;
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
            }

            $entry = $this->attribute($key, $hasValue, $value ?? null);

            if ($entry[0] === 'classstr') {
                if (($entry[1] ?? '') !== '') {
                    $classes[] = ['str', $entry[1]];
                }
                $entries[] = null;
                continue;
            }
            if ($entry[0] === 'classexpr') {
                $classes[] = ['expr', $entry[1]];
                $entries[] = null;
                continue;
            }

            $entries[] = $entry;
        }

        // Position the merged class entry where the first class attribute appeared.
        $firstClassIndex = array_search(null, $entries, true);
        if ($firstClassIndex !== false) {
            $entries[$firstClassIndex] = $this->mergeClasses($classes);
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
     * @return array{0: string, 1: mixed}
     */
    private function attribute(string $key, bool $hasValue, ?string $value): array
    {
        $colon = str_starts_with($key, ':');
        $inner = $colon ? substr($key, 1) : $key;

        if (! $colon && $key === 'class' && $hasValue) {
            return ['classstr', $value ?? ''];
        }
        if ($colon && $inner === 'class') {
            return ['classexpr', $value ?? 'true'];
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

            case 'expr':
            default:
                return var_export($entry[1], true).' => '.trim((string) $entry[2]);
        }
    }

    /**
     * Merges "class" and ":class" chunks into one prop, runtime expressions
     * last so Tailwind conflict resolution keeps caller classes winning.
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
                $dynamic[] = trim($value);
            }
        }

        if ($dynamic === []) {
            return ['str', 'class', implode(' ', $static)];
        }

        $exprs = implode(
            " . ' ' . ",
            array_map(static fn (string $expr): string => '(string)('.$expr.')', $dynamic),
        );

        $value = $static !== []
            ? var_export(implode(' ', $static), true)." . ' ' . ".$exprs
            : (count($dynamic) === 1 ? '(string)('.$dynamic[0].')' : $exprs);

        return ['all', 'class', $value];
    }

    private function slotName(string $attrsInner): string
    {
        $result = preg_match('/\bname\s*=\s*(["\'])(.*?)\1/', $attrsInner, $m) === 1;

        return $result ? var_export($m[2], true) : 'null';
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