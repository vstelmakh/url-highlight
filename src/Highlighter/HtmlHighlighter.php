<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter;

use InvalidArgumentException;
use VStelmakh\UrlHighlight\Matcher\UrlMatch;
use VStelmakh\UrlHighlight\Replacer\ReplacerInterface;
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
     * @param array&string[] $attributes Key/value map of tag attributes, e.g.: ['rel' => 'nofollow']
     * @param string $contentBefore Content to add before highlight: {here}<a...
     * @param string $contentAfter Content to add after highlight: ...</a>{here}
     */
    public function __construct(
        string $defaultScheme = 'http',
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
     * Replace all valid matches by html <a> tags
     * Additional filtering done here to avoid highlight in html tags or inside a tag content
     *
     * @param string $string
     * @param ReplacerInterface $replacer
     * @return string
     */
    public function highlight(string $string, ReplacerInterface $replacer): string
    {
        $result = '';
        /** @var array&string[] $parts */
        $parts = preg_split('/(<[^<>]+>)/u', $string, -1, PREG_SPLIT_DELIM_CAPTURE);

        $isLinkContent = false;
        foreach ($parts as $num => $part) {
            $isTag = $num % 2 !== 0;

            if ($isTag) {
                $isOpenLinkTag = (bool) preg_match('/^<\s*a(?:\s|>)/iu', $part);
                $isLinkContent = $isOpenLinkTag ? true : $isLinkContent;

                if (!$isOpenLinkTag) {
                    $isCloseLinkTag = (bool) preg_match('/<\s*\/\s*a\s*>$/iu', $part);
                    $isLinkContent = $isCloseLinkTag ? false : $isLinkContent;
                }
            } elseif (!$isLinkContent) {
                $part = $this->doHighlight($part, $replacer);
            }

            $result .= $part;
        }

        return $result;
    }

    /**
     * Use replacer with callable to highlight urls
     * String input at this point is not html tag or a tag content and could be safely highlighted
     *
     * @param string $string
     * @param ReplacerInterface $replacer
     * @return string
     */
    protected function doHighlight(string $string, ReplacerInterface $replacer): string
    {
        return $replacer->replaceCallback($string, \Closure::fromCallable([$this, 'getMatchHighlight']));
    }

    /**
     * Return html link highlight
     * Example: {content before}<a href="http://example.com">http://example.com</a>{content after}
     *
     * @param UrlMatch $match
     * @return string
     */
    protected function getMatchHighlight(UrlMatch $match): string
    {
        $link = $this->getLink($match);
        $linkSafeQuotes = str_replace('"', '%22', $link);

        return sprintf(
            '%s<a href="%s"%s>%s</a>%s',
            $this->getContentBefore($match),
            $linkSafeQuotes,
            $this->getAttributes($match),
            $this->getText($match),
            $this->getContentAfter($match)
        );
    }

    /**
     * Link used in href attribute: <a href="{here}"...
     *
     * @param UrlMatch $match
     * @return string
     */
    protected function getLink(UrlMatch $match): string
    {
        return LinkHelper::getLink($match, $this->getDefaultScheme());
    }

    /**
     * Link default scheme. Used to build href attribute
     *
     * @return string
     */
    protected function getDefaultScheme(): string
    {
        return $this->defaultScheme;
    }

    /**
     * Content used to display url: ...>{here}</a>
     *
     * @param UrlMatch $match
     * @return string
     */
    protected function getText(UrlMatch $match): string
    {
        return $match->getFullMatch();
    }

    /**
     * Additional link attributes <a href="#"{here}>...
     * Consider to add leading space and escape quotes, tag brackets e.g. " < > etc.
     *
     * @param UrlMatch $match
     * @return string
     */
    protected function getAttributes(UrlMatch $match): string
    {
        return $this->attributes;
    }

    /**
     * Content before highlight: {here}<a...
     *
     * @param UrlMatch $match
     * @return string
     */
    protected function getContentBefore(UrlMatch $match): string
    {
        return $this->contentBefore;
    }

    /**
     * Content after highlight: ...</a>{here}
     *
     * @param UrlMatch $match
     * @return string
     */
    protected function getContentAfter(UrlMatch $match): string
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
                throw new InvalidArgumentException(sprintf(
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
}
