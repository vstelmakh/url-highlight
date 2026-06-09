<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight;

use VStelmakh\UrlHighlight\Highlighter\Highlighter;
use VStelmakh\UrlHighlight\Highlighter\SimpleHighlighter;
use VStelmakh\UrlHighlight\Matcher\Matcher;
use VStelmakh\UrlHighlight\Matcher\UrlMatch;
use VStelmakh\UrlHighlight\Replacer\Decoder\Decoder;
use VStelmakh\UrlHighlight\Replacer\Renderer;
use VStelmakh\UrlHighlight\Replacer\Replacer;
use VStelmakh\UrlHighlight\Replacer\Strategy\EncodedStrategy;
use VStelmakh\UrlHighlight\Replacer\Strategy\PlainStrategy;
use VStelmakh\UrlHighlight\Replacer\Tokenizer\Tokenizer;

/**
 * Url highlight - library to parse URLs from string input and render them as HTML links.
 * Works with complex URLs, edge cases, and encoded input.
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
        $tokenizer = new Tokenizer();
        $renderer = new Renderer();
        $plainStrategy = new PlainStrategy($this->matcher);
        $encodedStrategy = new EncodedStrategy(new Decoder(), $tokenizer, $this->matcher);
        $this->plainReplacer = new Replacer($tokenizer, $plainStrategy, $renderer);
        $this->encodedReplacer = new Replacer($tokenizer, $encodedStrategy, $renderer);
    }

    /**
     * Find all URLs in the input string.
     *
     * @return array<UrlMatch>
     */
    public function find(string $string): array
    {
        return $this->matcher->match($string);
    }

    /**
     * Replace URLs in the input with rendered links.
     * Example: "Check the example.com website." -> "Check the <a href="http://example.com">example.com</a> website."
     *
     * @param Highlighter $highlighter Renderer used to produce the link for each URL match.
     * @param bool $isHtmlEncoded Set to true when the input contains HTML entities (e.g. produced by htmlspecialchars).
     *                            URLs are then matched against the decoded form (including inside attribute values
     *                            of decoded tags), while the original encoded characters are preserved verbatim in
     *                            the output. Leave false for literal text input.
     */
    public function highlight(string $string, Highlighter $highlighter = new SimpleHighlighter(), bool $isHtmlEncoded = false): string
    {
        return $isHtmlEncoded
            ? $this->encodedReplacer->highlight($string, $highlighter)
            : $this->plainReplacer->highlight($string, $highlighter);
    }
}
