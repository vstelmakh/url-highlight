<?php

namespace VStelmakh\UrlHighlight\Highlighter;

use VStelmakh\UrlHighlight\Matcher\Match;

/**
 * @internal
 */
class HighlightBuilder
{
    /**
     * @var string
     */
    private $href;

    /**
     * @var string
     */
    private $text;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var string
     */
    private $suffix;

    public function __construct(Match $match, string $defaultScheme)
    {
        $scheme = empty($match->getScheme()) ? $defaultScheme . '://' : '';
        $this->href = $scheme . $match->getFullMatch();
        $this->text = $match->getFullMatch();

        $this->prefix = '';
        $this->suffix = '';
    }

    /**
     * Set href custom value, e.g. <a href="custom_value"></a>
     *
     * @param string $href
     * @return $this
     */
    public function setHref(string $href): self
    {
        $this->href = $href;
        return $this;
    }

    /**
     * Set custom text value, e.g. <a>custom text</a>
     *
     * @param string $text
     * @return $this
     */
    public function setText(string $text): self
    {
        $this->text = $text;
        return $this;
    }

    /**
     * Set highlight prefix, e.g. prefix<a></a>
     *
     * @param string $prefix
     * @return $this
     */
    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * Set highlight suffix, e.g. <a></a>suffix
     *
     * @param string $suffix
     * @return $this
     */
    public function setSuffix(string $suffix): self
    {
        $this->suffix = $suffix;
        return $this;
    }

    /**
     * Return html highlight
     *
     * @return string
     */
    public function getHighlight(): string
    {
        $hrefSafeQuotes = str_replace('"', '%22', $this->href);
        return sprintf('%s<a href="%s">%s</a>%s', $this->prefix, $hrefSafeQuotes, $this->text, $this->suffix);
    }
}
