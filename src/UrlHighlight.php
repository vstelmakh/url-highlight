<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight;

use VStelmakh\UrlHighlight\Highlighter\Highlighter;
use VStelmakh\UrlHighlight\Highlighter\Linker\Linker;
use VStelmakh\UrlHighlight\Highlighter\Linker\SimpleLinker;
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
    private Highlighter $highlighter;

    public function __construct()
    {
        $this->matcher = new Matcher();
        $this->highlighter = new Highlighter($this->matcher, new Tokenizer());
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
     */
    public function highlight(string $string, Linker $linker = new SimpleLinker()): string
    {
        return $this->highlighter->highlight($string, $linker);
    }
}
