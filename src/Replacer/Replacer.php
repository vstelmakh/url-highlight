<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer;

use VStelmakh\UrlHighlight\Highlighter\Highlighter;
use VStelmakh\UrlHighlight\Matcher\Matcher;
use VStelmakh\UrlHighlight\Replacer\Decoder\Decoder;
use VStelmakh\UrlHighlight\Replacer\Extractor\Extractor;
use VStelmakh\UrlHighlight\Replacer\Extractor\Tokenizer;
use VStelmakh\UrlHighlight\Replacer\Strategy\HtmlEncodedStrategy;
use VStelmakh\UrlHighlight\Replacer\Strategy\HtmlStrategy;
use VStelmakh\UrlHighlight\Replacer\Strategy\PlainStrategy;
use VStelmakh\UrlHighlight\Replacer\Strategy\Strategy;

/**
 * Replaces every URL of the input with the output of {@see Highlighter}, leaving the surrounding text unchanged.
 * Locating the URLs is delegated to the injected {@see Strategy}, making new input formats possible without modifying
 * this class.
 *
 * @internal
 */
final readonly class Replacer
{
    public static function createPlain(Matcher $matcher): self
    {
        return new self(new PlainStrategy($matcher));
    }

    public static function createHtml(Matcher $matcher): self
    {
        $htmlExtractor = new Extractor(new Tokenizer());
        return new self(new HtmlStrategy($htmlExtractor, $matcher));
    }

    public static function createHtmlEncoded(Matcher $matcher): self
    {
        $htmlExtractor = new Extractor(new Tokenizer());
        return new self(new HtmlEncodedStrategy($htmlExtractor, new Decoder(), $matcher));
    }

    public function __construct(
        private Strategy $strategy,
    ) {}

    public function replace(string $text, Highlighter $highlighter): string
    {
        $result = '';
        $cursor = 0;

        foreach ($this->strategy->findReplacements($text) as $replacement) {
            $result .= substr($text, $cursor, $replacement->start - $cursor);
            $result .= $highlighter->render($replacement->url);
            $cursor = $replacement->end;
        }

        return $result . substr($text, $cursor);
    }
}
