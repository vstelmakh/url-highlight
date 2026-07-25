<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer\Strategy;

use VStelmakh\UrlHighlight\Matcher\Matcher;
use VStelmakh\UrlHighlight\Matcher\UrlMatch;

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
     * @return \Generator<UrlMatch>
     */
    #[\Override]
    public function match(string $text, int $offset): \Generator
    {
        foreach ($this->matcher->match($text) as $match) {
            yield new UrlMatch($offset + $match->start, $offset + $match->end, $match->url);
        }
    }
}
