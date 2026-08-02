<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer\Strategy;

use VStelmakh\UrlHighlight\Matcher\Matcher;
use VStelmakh\UrlHighlight\Replacer\Replacement;

/**
 * Matches URLs directly in plain text, without entity decoding.
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
    public function findReplacements(string $text, int $offset): \Generator
    {
        foreach ($this->matcher->match($text) as $match) {
            yield new Replacement($offset + $match->start, $offset + $match->end, $match->url);
        }
    }
}
