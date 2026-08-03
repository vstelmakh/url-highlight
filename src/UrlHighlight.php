<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight;

use VStelmakh\UrlHighlight\Highlighter\Highlighter;
use VStelmakh\UrlHighlight\Highlighter\SimpleHighlighter;
use VStelmakh\UrlHighlight\Matcher\Matcher;
use VStelmakh\UrlHighlight\Matcher\UrlMatch;
use VStelmakh\UrlHighlight\Replacer\Replacer;

/**
 * Entry point of the library. Finds URLs in text input and renders them as links.
 *
 * Usage example:
 * ```
 * $urlHighlight = new UrlHighlight();
 * echo $urlHighlight->highlight('Check the example.com website.');
 *  ```
 *
 * @api
 */
final readonly class UrlHighlight
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
     * Replace URLs in `$text` with rendered links.
     *
     * Example: `Check the example.com website.` -> `Check the <a href="http://example.com">example.com</a> website.`
     * For custom replacement logic implement your own {@see Highlighter}, see {@see SimpleHighlighter} for example.
     *
     * HTML entity-encoded input (e.g. from `htmlspecialchars`) must be passed with {@see Format::HtmlEncoded}.
     * Otherwise entities count as literal URL characters and the match runs past the URL end, for example
     * `example.com?a=1&quot;` matches as `example.com?a=1&quot`. The encoded format matches against the decoded
     * text, and keeps the original encoding in the output.
     *
     * @param string $text Input text to search for URLs.
     * @param Highlighter $highlighter Produces the replacement for each detected URL.
     * @param Format $format Encoding of `$text`.
     */
    public function highlight(
        string $text,
        Highlighter $highlighter = new SimpleHighlighter(),
        Format $format = Format::Plain,
    ): string {
        $replacer = match ($format) {
            Format::Plain => $this->plainReplacer,
            Format::HtmlEncoded => $this->encodedReplacer,
        };

        return $replacer->highlight($text, $highlighter);
    }

    /**
     * Find all URLs in `$text`.
     *
     * For accurate results provide plain text input. HTML entity-encoded text must be decoded before.
     *
     * @param string $text Input text to search for URLs.
     *
     * @return array<Url>
     */
    public function find(string $text): array
    {
        return array_map(
            static fn (UrlMatch $match): Url => $match->url,
            $this->matcher->match($text),
        );
    }
}
