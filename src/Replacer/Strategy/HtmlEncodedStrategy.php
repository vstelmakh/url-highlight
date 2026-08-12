<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer\Strategy;

use VStelmakh\UrlHighlight\Matcher\Matcher;
use VStelmakh\UrlHighlight\Replacer\Decoder\Decoder;
use VStelmakh\UrlHighlight\Replacer\Extractor\Extractor;
use VStelmakh\UrlHighlight\Replacer\Replacement;

/**
 * Matches URLs in the entity-encoded text of an HTML input, e.g. from `htmlspecialchars`. The text is first decoded so
 * entities like `&amp;` do not break matching, covering both escaped link text and escaped tag attribute values. Each
 * match is then mapped back onto the original encoded characters, leaving the output unchanged.
 *
 * @internal
 */
final readonly class HtmlEncodedStrategy implements Strategy
{
    public function __construct(
        private Extractor $extractor,
        private Decoder $decoder,
        private Matcher $matcher,
    ) {}

    /**
     * @return \Generator<Replacement>
     */
    #[\Override]
    public function findReplacements(string $text): \Generator
    {
        foreach ($this->extractor->extract($text) as $offset => $linkableText) {
            yield from $this->findInLinkableText($linkableText, $offset);
        }
    }

    /**
     * @param int $offset Byte offset of `$linkableText` within the input, used to map replacements back into it.
     *
     * @return \Generator<Replacement>
     */
    private function findInLinkableText(string $linkableText, int $offset): \Generator
    {
        $decoded = $this->decoder->decode($linkableText);

        foreach ($this->splitByMarkup($decoded->value) as $decodedOffset => $segment) {
            foreach ($this->matcher->match($segment) as $match) {
                $start = $offset + $decoded->toEncodedOffset($decodedOffset + $match->start);
                $end = $offset + $decoded->toEncodedOffset($decodedOffset + $match->end);
                yield new Replacement($start, $end, $match->url);
            }
        }
    }

    /**
     * Splits the text into the segments a URL may span: the contents of the escaped markup, e.g. an attribute value,
     * and the surrounding text. A lone angle bracket stays a regular character, the same as for a plain input.
     *
     * @return \Generator<int, string> Segment text, keyed by its offset in the given text.
     */
    private function splitByMarkup(string $decoded): \Generator
    {
        $cursor = 0;

        // Matching one markup construct at a time from a moving cursor, rather than splitting the whole text up
        // front, keeps only the current segment in memory. This significantly saves memory for markup-dense inputs.
        while (preg_match('/<[^<>]*>/', $decoded, $match, PREG_OFFSET_CAPTURE, $cursor) === 1) {
            [$markup, $markupOffset] = $match[0];

            $text = substr($decoded, $cursor, $markupOffset - $cursor);
            if ($text !== '') {
                yield $cursor => $text;
            }

            // Escaped markup is text as well, so its contents are matched too, but without the enclosing brackets.
            yield $markupOffset + 1 => substr($markup, 1, -1);

            $cursor = $markupOffset + strlen($markup);
        }

        // On PCRE failure the rest is yielded as one segment.
        yield $cursor => substr($decoded, $cursor);
    }
}
