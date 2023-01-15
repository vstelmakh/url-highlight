<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter;

use VStelmakh\UrlHighlight\Matcher\UrlMatch;
use VStelmakh\UrlHighlight\Replacer\ReplacerInterface;

class MarkdownHighlighter extends HtmlHighlighter
{
    /**
     * @param string $defaultScheme Used to build link for urls matched without scheme
     * @param string $contentBefore Content to add before highlight: {here}[...
     * @param string $contentAfter Content to add after highlight: ...){here}
     */
    public function __construct(string $defaultScheme = 'http', string $contentBefore = '', string $contentAfter = '')
    {
        parent::__construct($defaultScheme, [], $contentBefore, $contentAfter);
    }

    /**
     * Replace all valid matches by markdown links
     * Additional filtering done here to avoid highlight in existing markdown links
     *
     * @param string $string
     * @param ReplacerInterface $replacer
     * @return string
     */
    protected function doHighlight(string $string, ReplacerInterface $replacer): string
    {
        $result = '';
        $regex = '/(
            \[.+\]\([^\(\)]+\)       # markdown link
            |                        # or
            (?:^|\s+)\[.+\]:\s*\S+   # markdown link reference
            |                        # or
            (?:^|\s+)\[.+\](?:$|\s+) # markdown link short
        )/ux';

        /** @var array&string[] $parts */
        $parts = preg_split($regex, $string, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($parts as $num => $part) {
            $isMarkdownLink = $num % 2 !== 0;
            $result .= $isMarkdownLink
                ? $part
                : $replacer->replaceCallback($part, \Closure::fromCallable([$this, 'getMarkdownHighlight']));
        }

        return $result;
    }

    /**
     * Return markdown link highlight
     * Example: [http://example.com](http://example.com)
     *
     * @param UrlMatch $match
     * @return string
     */
    private function getMarkdownHighlight(UrlMatch $match): string
    {
        $text = $this->getText($match);
        $textSafeBrackets = str_replace(['[', ']'], ['\\[', '\\]'], $text);

        $link = $this->getLink($match);
        $linkSafeBrackets = str_replace(['(', ')'], ['%28', '%29'], $link);

        return sprintf(
            '%s[%s](%s)%s',
            $this->getContentBefore($match),
            $textSafeBrackets,
            $linkSafeBrackets,
            $this->getContentAfter($match)
        );
    }
}
