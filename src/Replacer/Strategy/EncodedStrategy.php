<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer\Strategy;

use VStelmakh\UrlHighlight\Matcher\Matcher;
use VStelmakh\UrlHighlight\Replacer\Decoder\Decoder;
use VStelmakh\UrlHighlight\Replacer\Replacement;

/**
 * Matches URLs in HTML-escaped text (e.g. from htmlspecialchars). The text is first decoded so entities like
 * `&amp;` do not break matching, covering both escaped link text and escaped tag attribute values. Each match is
 * then mapped back onto the original encoded characters, leaving the output unchanged.
 *
 * @internal
 */
final readonly class EncodedStrategy implements Strategy
{
    public function __construct(
        private Decoder $decoder,
        private Matcher $matcher,
    ) {}

    /**
     * @return \Generator<Replacement>
     */
    #[\Override]
    public function findReplacements(string $text, int $offset): \Generator
    {
        $decoded = $this->decoder->decode($text);

        foreach ($this->splitByMarkup($decoded->value) as $segment) {
            [$value, $decodedOffset] = $segment;

            foreach ($this->matcher->match($value) as $match) {
                $start = $offset + $decoded->toEncodedOffset($decodedOffset + $match->start);
                $end = $offset + $decoded->toEncodedOffset($decodedOffset + $match->end);
                yield new Replacement($start, $end, $match->url);
            }
        }
    }

    /**
     * @return list<array{string, int}> Segment value and its offset in the decoded text.
     */
    private function splitByMarkup(string $decoded): array
    {
        $flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE;
        $segments = preg_split('/<([^<>]*)>/', $decoded, -1, $flags);

        // On PCRE failure fallback to the whole text as a single segment.
        return $segments === false ? [[$decoded, 0]] : $segments;
    }
}
