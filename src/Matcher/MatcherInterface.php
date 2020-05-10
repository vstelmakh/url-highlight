<?php

namespace VStelmakh\UrlHighlight\Matcher;

/**
 * @internal
 */
interface MatcherInterface
{
    /**
     * @param string $string
     * @return MatchInterface|null
     */
    public function match(string $string): ?MatchInterface;

    /**
     * @param string $string
     * @return array&MatchInterface[]
     */
    public function matchAll(string $string): array;

    /**
     * @param string $string
     * @param callable $callback
     * @return string
     */
    public function replaceCallback(string $string, callable $callback): string;
}
