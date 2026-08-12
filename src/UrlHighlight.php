<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight;

use VStelmakh\UrlHighlight\Highlighter\Highlighter;
use VStelmakh\UrlHighlight\Highlighter\SimpleHighlighter;
use VStelmakh\UrlHighlight\Matcher\Matcher;
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
    private Replacer $htmlReplacer;
    private Replacer $htmlEncodedReplacer;

    public function __construct()
    {
        $this->matcher = new Matcher();
        $this->plainReplacer = Replacer::createPlain($this->matcher);
        $this->htmlReplacer = Replacer::createHtml($this->matcher);
        $this->htmlEncodedReplacer = Replacer::createHtmlEncoded($this->matcher);
    }

    /**
     * Replace URLs in `$text` with rendered links.
     *
     * Example: `Check the example.com website.` -> `Check the <a href="http://example.com">example.com</a> website.`
     * For custom replacement logic implement your own {@see Highlighter}, see {@see SimpleHighlighter} for example.
     *
     * The `$format` must describe the input, otherwise URLs are missed or matched past their end:
     * - {@see Format::Plain} takes the text as is, ignoring any markup.
     * - {@see Format::Html} leaves tags and the content of the elements that may not hold a link untouched.
     * - {@see Format::HtmlEncoded} is required for entity-encoded input, e.g. from `htmlspecialchars`. It matches
     *   against the decoded text to prevent invalid matches and keeps the original encoding in the output.
     *
     * @param string $text Input text to search for URLs.
     * @param Highlighter $highlighter Produces the replacement for each detected URL.
     * @param Format $format Format of `$text`.
     */
    public function highlight(
        string $text,
        Highlighter $highlighter = new SimpleHighlighter(),
        Format $format = Format::Html,
    ): string {
        $replacer = match ($format) {
            Format::Plain => $this->plainReplacer,
            Format::Html => $this->htmlReplacer,
            Format::HtmlEncoded => $this->htmlEncodedReplacer,
        };

        return $replacer->replace($text, $highlighter);
    }

    /**
     * Find all URLs in `$text`, ignoring any markup.
     *
     * @param string $text Input text to search for URLs.
     *
     * @return array<Url>
     */
    public function find(string $text): array
    {
        $result = [];

        foreach ($this->matcher->match($text) as $match) {
            $result[] = $match->url;
        }

        return $result;
    }
}
