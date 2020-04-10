<?php

namespace VStelmakh\UrlHighlight;

class UrlHighlight
{
    /**
     * @var Matcher
     */
    private $matcher;

    /**
     * @var Highlighter
     */
    private $highlighter;

    /**
     * Available options:
     *
     *  - match_by_tld: if true, will map matches without scheme by top level domain
     *      (example.com will be recognized as url). For full list of valid top level
     *      domains see: Domains::TOP_LEVEL_DOMAINS (default true).
     *
     *  - default_scheme: scheme to use when highlighting urls without scheme (default 'http').
     *
     *  - scheme_blacklist: array of schemes not allowed to be recognized as url (default []).
     *
     *  - scheme_whitelist: array of schemes explicitly allowed to be recognized as url (default []).
     *
     * @param array|mixed[] $options
     */
    public function __construct(array $options = [])
    {
        $options = array_merge([
            'match_by_tld' => true,
            'default_scheme' => 'http',
            'scheme_blacklist' => [],
            'scheme_whitelist' => [],
        ], $options);

        $matchValidator = new MatchValidator($options['match_by_tld'], $options['scheme_blacklist'], $options['scheme_whitelist']);
        $this->matcher = new Matcher($matchValidator);
        $this->highlighter = new Highlighter($this->matcher, $options['default_scheme']);
    }

    /**
     * Check if string is valid url
     *
     * @param string $string
     * @return bool
     */
    public function isUrl(string $string): bool
    {
        return $this->matcher->match($string) !== null;
    }

    /**
     * Parse string and return array of urls found
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
     * Parse string and replace urls with html links
     *
     * @param string $string
     * @return string
     */
    public function highlightUrls(string $string): string
    {
        return $this->highlighter->highlightUrls($string);
    }
}
