<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight;

use VStelmakh\UrlHighlight\Encoder\EncoderInterface;
use VStelmakh\UrlHighlight\Highlighter\HighlighterInterface;
use VStelmakh\UrlHighlight\Highlighter\HtmlHighlighter;
use VStelmakh\UrlHighlight\Matcher\MatcherFactory;
use VStelmakh\UrlHighlight\Matcher\MatcherInterface;
use VStelmakh\UrlHighlight\Replacer\ReplacerFactory;
use VStelmakh\UrlHighlight\Replacer\ReplacerInterface;
use VStelmakh\UrlHighlight\Validator\Validator;
use VStelmakh\UrlHighlight\Validator\ValidatorInterface;

class UrlHighlight
{
    /** @var MatcherInterface */
    private $matcher;

    /** @var ReplacerInterface */
    private $replacer;

    /** @var HighlighterInterface */
    private $highlighter;

    /**
     * By default, urls without scheme will be matched by top level domain using http scheme.
     * If you need different behavior see existent or create your own implementations of:
     *  - ValidatorInterface - define if match is valid and should be recognized as url
     *  - HighlighterInterface - define the way how url should be highlighted
     *  - EncoderInterface - define how to work with encoded input (e.g. html special chars)
     *
     * @param ValidatorInterface|null $validator
     * @param HighlighterInterface|null $highlighter
     * @param EncoderInterface|null $encoder
     */
    public function __construct(
        ?ValidatorInterface $validator = null,
        ?HighlighterInterface $highlighter = null,
        ?EncoderInterface $encoder = null
    ) {
        $validator = $validator ?? new Validator(true);
        $this->matcher = MatcherFactory::createMatcher($validator, $encoder);
        $this->replacer = ReplacerFactory::createReplacer($this->matcher);
        $this->highlighter = $highlighter ?? new HtmlHighlighter('http');
    }

    /**
     * Check if string is valid url.
     * If encoder provided - string will be decoded, than check performed.
     *
     * @param string $string
     * @return bool
     */
    public function isUrl(string $string): bool
    {
        return $this->matcher->match($string) !== null;
    }

    /**
     * Parse string and return array of urls found.
     * If encoder provided - will return decoded urls.
     *
     * @param string $string
     * @return array|string[]
     */
    public function getUrls(string $string): array
    {
        $result = [];
        $matches = $this->matcher->matchAll($string);
        foreach ($matches as $match) {
            $result[] = $match->getUrl();
        }
        return $result;
    }

    /**
     * Parse string and replace urls with highlighted links
     * e.g. http://example.com -> <a href="http://example.com">http://example.com</a>
     *
     * @param string $string
     * @return string
     */
    public function highlightUrls(string $string): string
    {
        return $this->highlighter->highlight($string, $this->replacer);
    }
}
