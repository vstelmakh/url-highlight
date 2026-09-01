<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer\Strategy;

use VStelmakh\UrlHighlight\Matcher\Matcher;
use VStelmakh\UrlHighlight\Replacer\Replacement;

/**
 * Matches URLs in the input as is, ignoring any markup.
 *
 * @internal
 */
final readonly class PlainStrategy implements Strategy
{
    public function __construct(
        private Matcher $matcher,
    ) {}

    /**
     * @return \Generator<Replacement>
     */
    #[\Override]
    public function findReplacements(string $text): \Generator
    {
        foreach ($this->matcher->match($text) as $match) {
            yield new Replacement($match->start, $match->end, $match->url);
        }
    }
}
