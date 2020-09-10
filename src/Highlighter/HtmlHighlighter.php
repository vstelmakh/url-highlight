<?php

namespace VStelmakh\UrlHighlight\Highlighter;

use VStelmakh\UrlHighlight\Matcher\Match;
use VStelmakh\UrlHighlight\Util\LinkHelper;

class HtmlHighlighter implements HighlighterInterface
{
    /**
     * @var string
     */
    private $defaultScheme;

    /**
     * @var string $attributes
     */
    private $attributes;

    /**
     * @var string
     */
    private $contentBefore;

    /**
     * @var string
     */
    private $contentAfter;

    /**
     * @param string $defaultScheme Used to build href for urls matched without scheme
     * @param array&string[] $attributes Key/value map of tag attributes
     * @param string $contentBefore Content to add before highlight: {here}<a...
     * @param string $contentAfter Content to add after highlight: ...</a>{here}
     */
    public function __construct(
        string $defaultScheme,
        array $attributes = [],
        string $contentBefore = '',
        string $contentAfter = ''
    ) {
        $this->defaultScheme = $defaultScheme;
        $this->attributes = $this->buildAttributes($attributes);
        $this->contentBefore = $contentBefore;
        $this->contentAfter = $contentAfter;
    }

    /**
     * Return html link highlight
     * Example: <a href="http://example.com">http://example.com</a>
     *
     * @param Match $match
     * @return string
     */
    public function getHighlight(Match $match): string
    {
        $link = $this->getLink($match);
        $linkSafeQuotes = str_replace('"', '%22', $link);

        return sprintf(
            '%s<a href="%s"%s>%s</a>%s',
            $this->getContentBefore($match),
            $linkSafeQuotes,
            $this->attributes,
            $this->getText($match),
            $this->getContentAfter($match)
        );
    }

    /**
     * Filter highlight in tag attributes, e.g href, src... and in <a> tags text
     *
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
     * Link used in href attribute: <a href="{here}"...
     *
     * @param Match $match
     * @return string
     */
    protected function getLink(Match $match): string
    {
        return LinkHelper::getLink($match, $this->defaultScheme);
    }

    /**
     * Content used to display url: ...>{here}</a>
     *
     * @param Match $match
     * @return string
     */
    protected function getText(Match $match): string
    {
        return $match->getFullMatch();
    }

    /**
     * Content before highlight: {here}<a...
     *
     * @param Match $match
     * @return string
     */
    protected function getContentBefore(Match $match): string
    {
        return $this->contentBefore;
    }

    /**
     * Content after highlight: ...</a>{here}
     *
     * @param Match $match
     * @return string
     */
    protected function getContentAfter(Match $match): string
    {
        return $this->contentAfter;
    }

    /**
     * Convert attributes array to attributes string
     *
     * @param array&string[] $attributes
     * @return string
     */
    private function buildAttributes(array $attributes): string
    {
        $result = [];
        foreach ($attributes as $key => $value) {
            // According to html5 parser spec: https://html.spec.whatwg.org/multipage/syntax.html#attributes-2
            $isValidAttributeName = !preg_match_all('/[\t\n\f \/>"\'=]/', $key, $matches);
            if (!$isValidAttributeName) {
                $invalidChars = array_unique($matches[0]);
                throw new \InvalidArgumentException(sprintf(
                    'Attribute name %s contains invalid characters: %s',
                    json_encode($key),
                    json_encode(implode(', ', $invalidChars))
                ));
            }

            $valueSafeQuotes = str_replace('"', '&quot;', $value);
            $result[] = sprintf('%s="%s"', $key, $valueSafeQuotes);
        }
        return empty($result) ? '' : ' ' . implode(' ', $result);
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
