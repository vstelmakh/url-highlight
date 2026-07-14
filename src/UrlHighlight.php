<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight;

use VStelmakh\UrlHighlight\Highlighter\Highlighter;
use VStelmakh\UrlHighlight\Highlighter\SimpleHighlighter;
use VStelmakh\UrlHighlight\Matcher\Matcher;
use VStelmakh\UrlHighlight\Matcher\UrlMatch;
use VStelmakh\UrlHighlight\Replacer\Replacer;

/**
 * Library for parsing URLs from text input and rendering them as HTML links. Handles complex URLs, edge cases,
 * and HTML-encoded input.
 *
 * @api
 */
readonly class UrlHighlight
{
    private Matcher $matcher;
    private Replacer $plainReplacer;
    private Replacer $encodedReplacer;

    public function __construct()
    {
        $this->matcher = new Matcher();
        $this->plainReplacer = Replacer::createPlain($this->matcher);
        $this->encodedReplacer = Replacer::createEncoded($this->matcher);
    }

    /**
     * Replace URLs in `$string` with rendered links (or anything else).
     *
     * Example: `Check the example.com website.` -> `Check the <a href="http://example.com">example.com</a> website.`
     * For any custom replacement logic - implement your own {@see Highlighter}, and provide it as argument to this
     * method call. For implementation examples, see {@see SimpleHighlighter}.
     *
     * @param string $string Input text to search for URLs.
     * @param Highlighter $highlighter Produces the replacement for each detected URL.
     * @param bool $isHtmlEncoded Set to `true` for HTML entity-encoded input (e.g. from `htmlspecialchars`).
     *     URLs are then matched against the decoded text, to prevent invalid matching, but output keeps the original
     *     encoding. Defaults to `false` for plain text input.
     */
    public function highlight(
        string $string,
        Highlighter $highlighter = new SimpleHighlighter(),
        bool $isHtmlEncoded = false
    ): string {
        return $isHtmlEncoded
            ? $this->encodedReplacer->highlight($string, $highlighter)
            : $this->plainReplacer->highlight($string, $highlighter);
    }

    /**
     * Find all URLs in `$string`.
     *
     * Supports plain text input only. For HTML-encoded input, decode it first (e.g. via `html_entity_decode`)
     * and pass the decoded string here.
     *
     * @param string $string Input to search.
     * @return array<Url>
     */
    public function find(string $string): array
    {
        return array_map(
            static fn (UrlMatch $match): Url => $match->url,
            $this->matcher->match($string),
        );
    }
}
