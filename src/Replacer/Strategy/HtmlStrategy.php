<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer\Strategy;

use VStelmakh\UrlHighlight\Matcher\Matcher;
use VStelmakh\UrlHighlight\Replacer\Extractor\Extractor;
use VStelmakh\UrlHighlight\Replacer\Replacement;

/**
 * Matches URLs in the text of an HTML input, leaving the markup itself untouched.
 *
 * @internal
 */
final readonly class HtmlStrategy implements Strategy
{
    public function __construct(
        private Extractor $extractor,
        private Matcher $matcher,
    ) {}

    /**
     * @return \Generator<Replacement>
     */
    #[\Override]
    public function findReplacements(string $text): \Generator
    {
        foreach ($this->extractor->extract($text) as $offset => $linkableText) {
            foreach ($this->matcher->match($linkableText) as $match) {
                yield new Replacement($offset + $match->start, $offset + $match->end, $match->url);
            }
        }
    }
}
