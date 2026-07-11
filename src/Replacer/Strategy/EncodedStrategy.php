<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer\Strategy;

use VStelmakh\UrlHighlight\Replacer\Decoder\DecodedString;
use VStelmakh\UrlHighlight\Replacer\Decoder\Decoder;
use VStelmakh\UrlHighlight\Replacer\Tokenizer\Token\TagToken;
use VStelmakh\UrlHighlight\Replacer\Tokenizer\Tokenizer;
use VStelmakh\UrlHighlight\Matcher\Matcher;
use VStelmakh\UrlHighlight\Matcher\UrlMatch;

/**
 * Matches URLs in a run of HTML-escaped text (e.g. from htmlspecialchars). The run is decoded so URLs
 * are matched against their true characters (so entities like &amp; do not break matching) - both in
 * escaped link text and inside escaped tag attribute values - while each match is mapped back onto the
 * original encoded characters so they stay verbatim in the output.
 *
 * @internal
 */
final readonly class EncodedStrategy implements Strategy
{
    public function __construct(
        private Decoder $decoder,
        private Tokenizer $tokenizer,
        private Matcher $matcher,
    ) {}

    /**
     * @return \Generator<UrlMatch>
     */
    #[\Override]
    public function match(string $span, int $offset): \Generator
    {
        $decoded = $this->decoder->decode($span);
        $decodedOffset = 0;

        foreach ($this->tokenizer->tokenize($decoded->value) as $token) {
            $contents = $token->toString();

            yield from $token instanceof TagToken
                ? $this->matchTagAttributes($contents, $decoded, $offset, $decodedOffset)
                : $this->matchPlainText($contents, $decoded, $offset, $decodedOffset);

            $decodedOffset += strlen($contents);
        }
    }

    /**
     * @return \Generator<UrlMatch>
     */
    private function matchPlainText(
        string $text,
        DecodedString $decoded,
        int $absoluteOffset,
        int $decodedOffset,
    ): \Generator {
        foreach ($this->matcher->match($text) as $match) {
            $start = $absoluteOffset + $decoded->toEncodedOffset($decodedOffset + $match->start);
            $end = $absoluteOffset + $decoded->toEncodedOffset($decodedOffset + $match->end);
            yield new UrlMatch($start, $end, $match->url);
        }
    }

    /**
     * @return \Generator<UrlMatch>
     */
    private function matchTagAttributes(
        string $tagContents,
        DecodedString $decoded,
        int $absoluteOffset,
        int $decodedOffset,
    ): \Generator {
        // Backreference (\1) makes the closing quote match the opening one, so a single
        // "value" group is enough - no need to branch on which quote style matched.
        $found = (bool) preg_match_all(
            '/=\s*(["\'])(?<value>(?:(?!\1).)*)\1/s',
            $tagContents,
            $attributes,
            PREG_OFFSET_CAPTURE | PREG_SET_ORDER,
        );

        if (!$found) {
            return;
        }

        foreach ($attributes as $attribute) {
            [$value, $offset] = $attribute['value'];
            yield from $this->matchPlainText($value, $decoded, $absoluteOffset, $decodedOffset + $offset);
        }
    }
}
