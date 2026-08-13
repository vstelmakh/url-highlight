<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer;

use VStelmakh\UrlHighlight\Format;
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
 * Locating the URLs is delegated to the {@see Strategy} of the given {@see Format}.
 *
 * @internal
 */
final readonly class Replacer
{
    public static function create(Matcher $matcher): self
    {
        $extractor = new Extractor(new Tokenizer());

        return new self(
            new PlainStrategy($matcher),
            new HtmlStrategy($extractor, $matcher),
            new HtmlEncodedStrategy($extractor, new Decoder(), $matcher),
        );
    }

    public function __construct(
        private Strategy $plainStrategy,
        private Strategy $htmlStrategy,
        private Strategy $htmlEncodedStrategy,
    ) {}

    public function replace(string $text, Highlighter $highlighter, Format $format): string
    {
        $strategy = $this->resolveStrategy($format);

        $result = '';
        $cursor = 0;

        foreach ($strategy->findReplacements($text) as $replacement) {
            $result .= substr($text, $cursor, $replacement->start - $cursor);
            $result .= $highlighter->render($replacement->url);
            $cursor = $replacement->end;
        }

        return $result . substr($text, $cursor);
    }

    private function resolveStrategy(Format $format): Strategy
    {
        return match ($format) {
            Format::Plain => $this->plainStrategy,
            Format::Html => $this->htmlStrategy,
            Format::HtmlEncoded => $this->htmlEncodedStrategy,
        };
    }
}
