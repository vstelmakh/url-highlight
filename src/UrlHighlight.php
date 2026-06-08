<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight;

use VStelmakh\UrlHighlight\Highlighter\Decoder\Decoder;
use VStelmakh\UrlHighlight\Highlighter\EncodedHighlighter;
use VStelmakh\UrlHighlight\Highlighter\PlainHighlighter;
use VStelmakh\UrlHighlight\Highlighter\Linker\Linker;
use VStelmakh\UrlHighlight\Highlighter\Linker\SimpleLinker;
use VStelmakh\UrlHighlight\Highlighter\Renderer;
use VStelmakh\UrlHighlight\Highlighter\Tokenizer\Tokenizer;
use VStelmakh\UrlHighlight\Matcher\Matcher;
use VStelmakh\UrlHighlight\Matcher\UrlMatch;

/**
 * Url highlight - library to parse URLs from string input and render them as HTML links.
 * Works with complex URLs, edge cases, and encoded input.
 *
 * @api
 */
readonly class UrlHighlight
{
    private Matcher $matcher;
    private PlainHighlighter $plainHighlighter;
    private EncodedHighlighter $encodedHighlighter;

    public function __construct()
    {
        $this->matcher = new Matcher();
        $tokenizer = new Tokenizer();
        $renderer = new Renderer();
        $this->plainHighlighter = new PlainHighlighter($tokenizer, $this->matcher, $renderer);
        $this->encodedHighlighter = new EncodedHighlighter(new Decoder(), $tokenizer, $this->matcher, $renderer);
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
     * @param Linker $linker Renderer used to produce the link for each URL match.
     * @param bool $isHtmlEncoded Set to true when the input contains HTML entities (e.g. produced by htmlspecialchars).
     *                            URLs are then matched against the decoded form (including inside attribute values
     *                            of decoded tags), while the original encoded characters are preserved verbatim in
     *                            the output. Leave false for literal text input.
     */
    public function highlight(string $string, Linker $linker = new SimpleLinker(), bool $isHtmlEncoded = false): string
    {
        return $isHtmlEncoded
            ? $this->encodedHighlighter->highlight($string, $linker)
            : $this->plainHighlighter->highlight($string, $linker);
    }
}
