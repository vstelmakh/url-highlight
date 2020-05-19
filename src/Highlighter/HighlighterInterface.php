<?php

namespace VStelmakh\UrlHighlight\Highlighter;

use VStelmakh\UrlHighlight\Matcher\Match;

interface HighlighterInterface
{
    /**
     * Return corresponding match highlight. In other words - replacement for found url match.
     *
     * @param Match $match
     * @return string
     */
    public function getHighlight(Match $match): string;

    /**
     * If input string contains already highlighted urls - this urls will be highlighted once more.
     * Here it should be filtered out.
     *
     * @param string $string
     * @return string
     */
    public function filterOverhighlight(string $string): string;
}
