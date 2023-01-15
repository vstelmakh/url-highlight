<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter;

use VStelmakh\UrlHighlight\Replacer\ReplacerInterface;

interface HighlighterInterface
{
    /**
     * Get string and replacer as input. Return string with highlighted urls.
     *
     * @param string $string Raw string input
     * @param ReplacerInterface $replacer Main tool to find and replace urls
     * @return string Highlighted string
     */
    public function highlight(string $string, ReplacerInterface $replacer): string;
}
