<?php

namespace VStelmakh\UrlHighlight\Matcher;

/**
 * @internal
 */
interface MatcherInterface
{
    /**
     * Match string by url regex
     *
     * @param string $string
     * @return Match|null
     */
    public function match(string $string): ?Match;

    /**
     * Get all valid url regex matches from string
     *
     * @param string $string
     * @return array&Match[]
     */
    public function matchAll(string $string): array;
}
