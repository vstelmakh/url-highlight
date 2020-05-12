<?php

namespace VStelmakh\UrlHighlight\Highlighter;

use VStelmakh\UrlHighlight\Matcher\Match;

interface HighlighterInterface
{
    /**
     * @param Match $match
     * @param string|null $displayText
     * @return string
     */
    public function getHighlight(Match $match, ?string $displayText = null): string;

    /**
     * @param string $string
     * @return string
     */
    public function filterOverhighlight(string $string): string;
}
