<?php

namespace VStelmakh\UrlHighlight\Highlighter;

use VStelmakh\UrlHighlight\Match;
use VStelmakh\UrlHighlight\Matcher;

/**
 * @internal
 */
class PlainTextHighlighter extends AbstractHighlighter
{
    /**
     * @var Matcher
     */
    private $matcher;

    /**
     * @var string
     */
    private $defaultScheme;

    public function __construct(Matcher $matcher, string $defaultScheme)
    {
        $this->matcher = $matcher;
        $this->defaultScheme = $defaultScheme;
    }

    /**
     * Parse string and replace urls with html links
     *
     * @param string $string
     * @return string
     */
    public function highlightUrls(string $string): string
    {
        $result = $this->matcher->replaceCallback($string, [$this, 'getMatchAsHighlight']);
        $result = $this->filterHighlightInTagAttributes($result);
        $result = $this->filterHighlightInLinks($result);
        return $result;
    }

    /**
     * Convert match to highlighted string
     *
     * @param Match $match
     * @return string
     */
    public function getMatchAsHighlight(Match $match): string
    {
        return $this->getHighlightBuilder($match, $this->defaultScheme)->getHighlight();
    }
}
