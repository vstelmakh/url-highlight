<?php

namespace VStelmakh\UrlHighlight;

class UrlHighlight
{
    /** @var string */
    private $defaultScheme;

    /** @var MatchValidator */
    private $matchValidator;

    /**
     * Available options:
     *  - match_by_tld: When set to true, will map matches without scheme by top level domain
     *      (example.com will be recognized as url). For full list of valid top level
     *      domains see: Domains::TOP_LEVEL_DOMAINS (default true).
     *  - default_scheme: Scheme to use when highlighting urls without scheme (default 'http').
     *  - scheme_blacklist: List of schemes not allowed to be recognized as url (default []).
     *  - scheme_whitelist: List of schemes explicitly allowed to be recognized as url (default []).
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $options = array_merge([
            'match_by_tld' => true,
            'default_scheme' => 'http',
            'scheme_blacklist' => [],
            'scheme_whitelist' => [],
        ], $options);

        $this->defaultScheme = (string) $options['default_scheme'];
        $this->matchValidator = new MatchValidator(
            $options['match_by_tld'],
            $options['scheme_blacklist'],
            $options['scheme_whitelist']
        );
    }

    /**
     * Check if string is valid url
     *
     * @param string $string
     * @return bool
     */
    public function isUrl(string $string): bool
    {
        $urlRegex = $this->getUrlRegex(true);
        $isMatch = preg_match($urlRegex, $string, $matches);
        return $isMatch && $this->isValidUrlMatch($matches);
    }

    /**
     * Parse string and return array of urls found
     *
     * @param string $string
     * @return array|string[]
     */
    public function getUrls(string $string): array
    {
        $urlRegex = $this->getUrlRegex(false);
        preg_match_all($urlRegex, $string, $matches, PREG_SET_ORDER);
        $result = [];
        foreach ($matches as $match) {
            if ($this->isValidUrlMatch($match)) {
                $result[] = $match[0];
            }
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
        $urlRegex = $this->getUrlRegex(false);
        $callback = function ($matches) {
            $scheme = empty($matches['scheme']) ? $this->defaultScheme . '://' : '';
            return $this->isValidUrlMatch($matches)
                ? '<a href="' . $scheme . $matches[0] . '">' . $matches[0] . '</a>'
                : $matches[0];
        };
        $result = preg_replace_callback($urlRegex, $callback, $string) ?? $string;
        $result = $this->filterHighlightInTagAttributes($result);
        $result = $this->filterHighlightInLinks($result);
        return $result;
    }

    /**
     * @param bool $strict
     * @return string
     */
    private function getUrlRegex(bool $strict): string
    {
        $prefix = $strict ? '^' : '';
        $suffix = $strict ? '$' : '';

        return '/' . $prefix . '                                                 
            (?:                                                  # scheme or possible host
                (?<scheme>[a-z][\w-]+):                            # url scheme and colon
                (?:         
                    \/{2}                                                # 2 slashes
                    |                                                    # or
                    [\w\d]                                               # single letter or digit
                )           
                |                                                    # or
                (?<host>[^\s`!()\[\]{};:\'",<>?«»“”‘’\/]+\.\w{2,})   # possible host (captured only if scheme missing)
            )  
            (?:                                                  # port, path, query, fragment (one or none)
                (?:                                                  # one or more:
                    [^\s()<>]+                                           # run of non-space, non-()<>
                    |                                                    # or
                    \((?:[^\s()<>]+|(?:\([^\s()<>]+\)))*\)                   # balanced brackets (up to 2 levels)
                )*           
                (?:                                                  # end with:
                    \((?:[^\s()<>]+|(?:\([^\s()<>]+\)))*\)                   # balanced brackets (up to 2 levels)
                    |                                                    # or
                    [^\s`!()\[\]{};:\'".,<>?«»“”‘’]                      # not a space or punctuation chars
                )
            )?
        ' . $suffix . '/ixu';
    }

    /**
     * Check if preg_match result contains valid host
     *
     * @param array|string[] $match
     * @return bool
     */
    private function isValidUrlMatch(array $match): bool
    {
        $scheme = $match['scheme'] ?? null;
        $host = $match['host'] ?? null;
        return $this->matchValidator->isValidUrl($scheme, $host);
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
