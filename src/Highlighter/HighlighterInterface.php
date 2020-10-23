<?php

namespace VStelmakh\UrlHighlight\Highlighter;

use VStelmakh\UrlHighlight\Replacer\ReplacerInterface;

interface HighlighterInterface
{
    /**
     * Get string and replacer as input. Return string with highlighted urls.
     *
     * @param string $string
     * @param ReplacerInterface $replacer
     * @return string
     */
    public function highlight(string $string, ReplacerInterface $replacer): string;
}
