<?php

namespace VStelmakh\UrlHighlight\Highlighter;

use VStelmakh\UrlHighlight\Matcher\MatchInterface;

interface HighlighterInterface
{
    /**
     * @param MatchInterface $match
     * @param string|null $displayText
     * @return string
     */
    public function getHighlight(MatchInterface $match, ?string $displayText = null): string;

    /**
     * @param string $string
     * @return string
     */
    public function filterOverhighlight(string $string): string;
}
