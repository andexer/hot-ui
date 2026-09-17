<?php

declare(strict_types=1);

namespace Components\Support\Exception;

/**
 * The compiled source opens and closes component tags inconsistently. Tags are
 * held internally as "ui.card" and printed as "<ui:card>", so the message
 * always shows the markup the developer wrote.
 */
final class UnbalancedTagException extends \InvalidArgumentException implements SupportException
{
    private function __construct(
        private readonly string $tag,
        private readonly ?string $expected,
        string $message,
    ) {
        parent::__construct($message);
    }

    /** A component tag was opened and never closed. */
    public static function unclosed(string $tag): self
    {
        return new self($tag, null, sprintf(
            'Unclosed Hot-UI tag [<%s>] in compiled source; every <ui:/<blocks:> tag needs a matching close tag.',
            self::display($tag),
        ));
    }

    /** A closing tag arrived with nothing open. */
    public static function unexpected(string $tag): self
    {
        return new self($tag, null, sprintf(
            'Unexpected closing tag [</%s>] without an opening tag in compiled source.',
            self::display($tag),
        ));
    }

    /**
     * A closing tag arrived for a different component than the innermost open
     * one; $expected is the tag the source closed, $open the one still open.
     */
    public static function mismatched(string $expected, string $open): self
    {
        return new self($expected, $open, sprintf(
            'Mismatched closing tag [</%s>]; expected [</%s>].',
            self::display($expected),
            self::display($open),
        ));
    }

    /** The offending tag, in internal "ns.name" form. */
    public function getTag(): string
    {
        return $this->tag;
    }

    /** The tag that was still open, or null when nothing was expected. */
    public function getExpected(): ?string
    {
        return $this->expected;
    }

    private static function display(string $tag): string
    {
        return str_replace('.', ':', $tag);
    }
}
