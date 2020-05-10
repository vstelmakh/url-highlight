<?php

namespace VStelmakh\UrlHighlight;

use VStelmakh\UrlHighlight\Encoder\EncoderInterface;
use VStelmakh\UrlHighlight\Highlighter\HighlighterInterface;
use VStelmakh\UrlHighlight\Highlighter\HtmlHighlighter;
use VStelmakh\UrlHighlight\Matcher\EncodedMatcher;
use VStelmakh\UrlHighlight\Matcher\Matcher;
use VStelmakh\UrlHighlight\Matcher\MatcherInterface;
use VStelmakh\UrlHighlight\Matcher\MatchValidator;

class UrlHighlight
{
    /**
     * @var MatcherInterface
     */
    private $matcher;

    /**
     * @var HighlighterInterface
     */
    private $highlighter;

    /**
     * Available options:
     *
     *  - match_by_tld (bool): if true, will map matches without scheme by top level domain
     *      (example.com will be recognized as url). For full list of valid top level
     *      domains see: Domains::TOP_LEVEL_DOMAINS (default true).
     *
     *  - default_scheme (string): scheme to use when highlighting urls without scheme (default 'http').
     *
     *  - scheme_blacklist (string[]): array of schemes not allowed to be recognized as url (default []).
     *
     *  - scheme_whitelist (string[]): array of schemes explicitly allowed to be recognized as url (default []).
     *
     * @param array|mixed[] $options
     * @param HighlighterInterface|null $highlighter
     * @param EncoderInterface|null $encoder
     */
    public function __construct(array $options = [], ?HighlighterInterface $highlighter = null, ?EncoderInterface $encoder = null)
    {
        $options = array_merge([
            'match_by_tld' => true,
            'default_scheme' => 'http',
            'scheme_blacklist' => [],
            'scheme_whitelist' => [],
        ], $options);

        $matchValidator = new MatchValidator($options['match_by_tld'], $options['scheme_blacklist'], $options['scheme_whitelist']);
        $matcher = new Matcher($matchValidator);
        $this->matcher = $encoder ? new EncodedMatcher($matcher, $encoder) : $matcher;
        $this->highlighter = $highlighter ?? new HtmlHighlighter($options['default_scheme']);
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
            $result[] = $match->getFullMatch();
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
        $string = $this->matcher->replaceCallback($string, [$this->highlighter, 'getHighlight']);
        $string = $this->highlighter->filterOverhighlight($string);
        return $string;
    }
}
