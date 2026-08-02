<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer\Strategy;

use VStelmakh\UrlHighlight\Matcher\Matcher;
use VStelmakh\UrlHighlight\Replacer\Decoder\DecodedString;
use VStelmakh\UrlHighlight\Replacer\Decoder\Decoder;
use VStelmakh\UrlHighlight\Replacer\Replacement;
use VStelmakh\UrlHighlight\Replacer\Tokenizer\Token\TagToken;
use VStelmakh\UrlHighlight\Replacer\Tokenizer\Tokenizer;

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
        private Tokenizer $tokenizer,
        private Matcher $matcher,
    ) {}

    /**
     * @return \Generator<Replacement>
     */
    #[\Override]
    public function findReplacements(string $text, int $offset): \Generator
    {
        $decoded = $this->decoder->decode($text);
        $decodedOffset = 0;

        foreach ($this->tokenizer->tokenize($decoded->value) as $token) {
            $contents = $token->__toString();

            yield from $token instanceof TagToken
                ? $this->findInTagAttributes($contents, $decoded, $offset, $decodedOffset)
                : $this->findInPlainText($contents, $decoded, $offset, $decodedOffset);

            $decodedOffset += strlen($contents);
        }
    }

    /**
     * @return \Generator<Replacement>
     */
    private function findInPlainText(
        string $text,
        DecodedString $decoded,
        int $absoluteOffset,
        int $decodedOffset,
    ): \Generator {
        foreach ($this->matcher->match($text) as $match) {
            $start = $absoluteOffset + $decoded->toEncodedOffset($decodedOffset + $match->start);
            $end = $absoluteOffset + $decoded->toEncodedOffset($decodedOffset + $match->end);
            yield new Replacement($start, $end, $match->url);
        }
    }

    /**
     * @return \Generator<Replacement>
     */
    private function findInTagAttributes(
        string $tagContents,
        DecodedString $decoded,
        int $absoluteOffset,
        int $decodedOffset,
    ): \Generator {
        // Matches quoted attribute values. Backreference \1 ties the closing quote to the opening one.
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
            yield from $this->findInPlainText($value, $decoded, $absoluteOffset, $decodedOffset + $offset);
        }
    }
}
