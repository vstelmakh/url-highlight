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

    /**
     * Replace all valid url matches by callback
     *
     * @param string $string
     * @param callable $callback
     * @return string
     */
    public function replaceCallback(string $string, callable $callback): string;
}
