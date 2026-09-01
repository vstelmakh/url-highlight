<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer;

use VStelmakh\UrlHighlight\Format;
use VStelmakh\UrlHighlight\Matcher\Matcher;
use VStelmakh\UrlHighlight\Replacer\Decoder\Decoder;
use VStelmakh\UrlHighlight\Replacer\Extractor\Extractor;
use VStelmakh\UrlHighlight\Replacer\Extractor\Tokenizer;
use VStelmakh\UrlHighlight\Replacer\Strategy\HtmlEncodedStrategy;
use VStelmakh\UrlHighlight\Replacer\Strategy\HtmlStrategy;
use VStelmakh\UrlHighlight\Replacer\Strategy\PlainStrategy;
use VStelmakh\UrlHighlight\Replacer\Strategy\Strategy;

/**
 * Creates the {@see Strategy} that interprets the input of a given {@see Format}.
 *
 * @internal
 */
final readonly class StrategyFactory
{
    public static function create(Matcher $matcher): self
    {
        return new self($matcher, new Extractor(new Tokenizer()), new Decoder());
    }

    public function __construct(
        private Matcher $matcher,
        private Extractor $extractor,
        private Decoder $decoder,
    ) {}

    public function createStrategy(Format $format): Strategy
    {
        return match ($format) {
            Format::Plain => new PlainStrategy($this->matcher),
            Format::Html => new HtmlStrategy($this->extractor, $this->matcher),
            Format::HtmlEncoded => new HtmlEncodedStrategy($this->extractor, $this->decoder, $this->matcher),
        };
    }
}
