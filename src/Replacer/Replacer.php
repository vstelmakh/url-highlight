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
 * Replaces every URL of the input with a rendered link. Locating the URLs is delegated to the injected Strategy,
 * making new input formats possible without modifying this class.
 *
 * @internal
 */
final readonly class Replacer
{
    public static function createPlain(Matcher $matcher): self
    {
        return new self(new PlainStrategy($matcher), new Renderer());
    }

    public static function createHtml(Matcher $matcher): self
    {
        $htmlExtractor = new Extractor(new Tokenizer());
        return new self(new HtmlStrategy($htmlExtractor, $matcher), new Renderer());
    }

    public static function createHtmlEncoded(Matcher $matcher): self
    {
        $htmlExtractor = new Extractor(new Tokenizer());
        return new self(new HtmlEncodedStrategy($htmlExtractor, new Decoder(), $matcher), new Renderer());
    }

    public function __construct(
        private Strategy $strategy,
        private Renderer $renderer,
    ) {}

    public function replace(string $text, Highlighter $highlighter): string
    {
        $replacements = $this->strategy->findReplacements($text);
        return $this->renderer->render($text, $replacements, $highlighter);
    }
}
