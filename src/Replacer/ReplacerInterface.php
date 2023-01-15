<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer;

interface ReplacerInterface
{
    /**
     * Replace all valid url matches by callback.
     * Callback should accept Match as an input and return string replacement.
     *
     * @param string $string
     * @param callable $callback
     * @return string
     */
    public function replaceCallback(string $string, callable $callback): string;
}
