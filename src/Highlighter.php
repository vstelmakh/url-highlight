<?php

namespace VStelmakh\UrlHighlight;

/**
 * @internal
 */
class Highlighter
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
        $callback = function ($match) {
            $scheme = empty($match['scheme']) ? $this->defaultScheme . '://' : '';
            return '<a href="' . $scheme . $match[0] . '">' . $match[0] . '</a>';
        };
        $result = $this->matcher->replaceCallback($string, $callback);
        $result = $this->filterHighlightInTagAttributes($result);
        $result = $this->filterHighlightInLinks($result);
        return $result;
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