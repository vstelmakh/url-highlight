<?php

namespace VStelmakh\UrlHighlight\Highlighter;

use VStelmakh\UrlHighlight\Matcher\Match;

class HtmlHighlighter implements HighlighterInterface
{
    /**
     * @var string
     */
    private $defaultScheme;

    public function __construct(string $defaultScheme)
    {
        $this->defaultScheme = $defaultScheme;
    }

    /**
     * @param Match $match
     * @param string|null $displayText
     * @return string
     */
    public function getHighlight(Match $match, ?string $displayText = null): string
    {
        $fullMatch = $match->getUrl();
        $scheme = empty($match->getScheme()) ? $this->defaultScheme . '://' : '';
        $href = $scheme . $fullMatch;
        $hrefSafeQuotes = str_replace('"', '%22', $href);
        $text = $displayText ?? $fullMatch;
        return sprintf('<a href="%s">%s</a>', $hrefSafeQuotes, $text);
    }

    /**
     * @param string $string
     * @return string
     */
    public function filterOverhighlight(string $string): string
    {
        $string = $this->filterHighlightInTagAttributes($string);
        $string = $this->filterHighlightInLinks($string);
        return $string;
    }

    /**
     * Filter a tags in html attributes
     * Example: <a href="<a href="http://example.com">http://example.com</a>">http://example.com</a>
     * Result: <a href="http://example.com">http://example.com</a>
     *
     * @param string $string
     * @return string
     */
    private function filterHighlightInTagAttributes(string $string): string
    {
        $regex = '/
            (
                <\w+\s[^>]+                              # tag start: "<tag"
                \w\s?=\s?[\'"]                           # attribute start: "href=""
            )
            <a\s[^>]*href=[\'"].*[\'"][^>]*>([^<]*)<\/a> # html link: "<a href="#"><\/a>"
            (
                [\'"]                                    # attribute end: """
                [^>]*>                                   # tag end: ">"
            )
        /ixuU';

        return preg_replace($regex, '$1$2$3', $string) ?? $string;
    }

    /**
     * Filter a tags in a tags
     * Example: <a href="#"><a href="http://example.com">http://example.com</a></a>
     * Result: <a href="#"http://example.com">http://example.com</a>
     *
     * @param string $string
     * @return string
     */
    private function filterHighlightInLinks(string $string): string
    {
        $regex = '/
            (<a[^>]*>)                 # parent tag start "<a"
            <a[^>]*>([^<]*)<\s*\/\s*a> # child tag "<a><\/a>"
            (<\s*\/\s*a>)              # parent tag end "<\/a>"
        /ixuU';

        return preg_replace($regex, '$1$2$3', $string) ?? $string;
    }
}
