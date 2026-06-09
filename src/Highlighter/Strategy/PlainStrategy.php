<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter\Strategy;

use VStelmakh\UrlHighlight\Highlighter\OffsetMatch;
use VStelmakh\UrlHighlight\Matcher\Matcher;

/**
 * Matches URLs in a run of literal text.
 *
 * @internal
 */
final readonly class PlainStrategy implements Strategy
{
    public function __construct(
        private Matcher $matcher,
    ) {}

    /**
     * @return list<OffsetMatch>
     */
    #[\Override]
    public function match(string $span, int $offset): array
    {
        $result = [];
        foreach ($this->matcher->match($span) as $match) {
            $start = $offset + $match->offset;
            $end = $start + strlen($match->match);
            $result[] = new OffsetMatch($start, $end, $match);
        }
        return $result;
    }
}
