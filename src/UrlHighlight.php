<?php

namespace VStelmakh\UrlHighlight;

use VStelmakh\UrlHighlight\Highlighter\AbstractHighlighter;
use VStelmakh\UrlHighlight\Highlighter\HtmlSpecialCharsHighlighter;
use VStelmakh\UrlHighlight\Highlighter\PlainTextHighlighter;

class UrlHighlight
{
    public const HIGHLIGHT_TYPE_PLAIN_TEXT = 'plain_text';
    public const HIGHLIGHT_TYPE_HTML_SPECIAL_CHARS = 'html_special_chars';

    /**
     * @var Matcher
     */
    private $matcher;

    /**
     * @var AbstractHighlighter
     */
    private $highlighter;

    /**
     * Available options:
     *
     *  - match_by_tld (bool): if true, will map matches without scheme by top level domain
     *      (example.com will be recognized as url). For full list of valid top level
     *      domains see: Domains::TOP_LEVEL_DOMAINS (default true).
     *
     *  - highlight_type (string): define how to process input text. Allowed types: plain_text, html_special_chars.
     *      Use class constants to specify type, see UrlHighlight::HIGHLIGHT_TYPE_*
     *      - plain_text: simply find and replace urls by html links. (default).
     *      - html_special_chars: expect text to be html entities encoded. Works with both, plain text
     *          and html escaped string. Perform more regex operations than plain_text.
     *
     *  - default_scheme (string): scheme to use when highlighting urls without scheme (default 'http').
     *
     *  - scheme_blacklist (string[]): array of schemes not allowed to be recognized as url (default []).
     *
     *  - scheme_whitelist (string[]): array of schemes explicitly allowed to be recognized as url (default []).
     *
     * @param array|mixed[] $options
     */
    public function __construct(array $options = [])
    {
        $options = array_merge([
            'match_by_tld' => true,
            'highlight_type' => self::HIGHLIGHT_TYPE_PLAIN_TEXT,
            'default_scheme' => 'http',
            'scheme_blacklist' => [],
            'scheme_whitelist' => [],
        ], $options);

        $matchValidator = new MatchValidator($options['match_by_tld'], $options['scheme_blacklist'], $options['scheme_whitelist']);
        $this->matcher = new Matcher($matchValidator);
        $this->highlighter = $this->getHighlighterByType($options['highlight_type'], $options['default_scheme']);
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

    /**
     * @param string $type
     * @param string $defaultScheme
     * @return AbstractHighlighter
     */
    private function getHighlighterByType(string $type, string $defaultScheme): AbstractHighlighter
    {
        switch ($type) {
            case self::HIGHLIGHT_TYPE_PLAIN_TEXT:
                return new PlainTextHighlighter($this->matcher, $defaultScheme);
            case self::HIGHLIGHT_TYPE_HTML_SPECIAL_CHARS:
                return new HtmlSpecialCharsHighlighter($this->matcher, $defaultScheme);
            default:
                // TODO: Change to lib own exception
                throw new \RuntimeException(sprintf(
                    'Unsupported highlighter type provided "%s". Supported types [%s].',
                    $type,
                    implode(', ', [self::HIGHLIGHT_TYPE_PLAIN_TEXT, self::HIGHLIGHT_TYPE_HTML_SPECIAL_CHARS])
                ));
        }
    }
}
