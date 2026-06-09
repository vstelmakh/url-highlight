<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter\Strategy;

use VStelmakh\UrlHighlight\Highlighter\Decoder\DecodedString;
use VStelmakh\UrlHighlight\Highlighter\Decoder\Decoder;
use VStelmakh\UrlHighlight\Highlighter\OffsetMatch;
use VStelmakh\UrlHighlight\Highlighter\Tokenizer\Token\TagToken;
use VStelmakh\UrlHighlight\Highlighter\Tokenizer\Tokenizer;
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
     * @return list<OffsetMatch>
     */
    #[\Override]
    public function match(string $span, int $offset): array
    {
        $decoded = $this->decoder->decode($span);

        $result = [];
        $cursor = 0;

        foreach ($this->tokenizer->tokenize($decoded->value) as $token) {
            $contents = $token->toString();
            $matches = $token instanceof TagToken
                ? $this->attributeMatches($contents, $decoded, $offset, $cursor)
                : $this->textMatches($contents, $decoded, $offset, $cursor);

            foreach ($matches as $offsetMatch) {
                $result[] = $offsetMatch;
            }

            $cursor += strlen($contents);
        }

        return $result;
    }

    /**
     * @return list<OffsetMatch>
     */
    private function textMatches(string $text, DecodedString $decoded, int $spanOffset, int $baseOffset): array
    {
        $result = [];
        foreach ($this->matcher->match($text) as $match) {
            $result[] = $this->toEncodedMatch($decoded, $spanOffset, $baseOffset + $match->offset, $match);
        }
        return $result;
    }

    /**
     * @return list<OffsetMatch>
     */
    private function attributeMatches(string $tagContents, DecodedString $decoded, int $spanOffset, int $baseOffset): array
    {
        $found = preg_match_all(
            '/=\s*(?:"([^"]*)"|\'([^\']*)\')/',
            $tagContents,
            $attributes,
            PREG_OFFSET_CAPTURE | PREG_SET_ORDER | PREG_UNMATCHED_AS_NULL,
        );
        if ($found === false || $found === 0) {
            return [];
        }

        $result = [];
        foreach ($attributes as $attribute) {
            // Exactly one quote branch matches; the other capture group is null (PREG_UNMATCHED_AS_NULL).
            $quoted = $attribute[1][1] !== -1 ? $attribute[1] : $attribute[2];
            $value = $quoted[0];
            if ($value === null) {
                continue;
            }
            foreach ($this->matcher->match($value) as $match) {
                $result[] = $this->toEncodedMatch($decoded, $spanOffset, $baseOffset + $quoted[1] + $match->offset, $match);
            }
        }
        return $result;
    }

    private function toEncodedMatch(DecodedString $decoded, int $spanOffset, int $decodedStart, UrlMatch $match): OffsetMatch
    {
        $start = $spanOffset + $decoded->toEncodedOffset($decodedStart);
        $end = $spanOffset + $decoded->toEncodedOffset($decodedStart + strlen($match->match));
        return new OffsetMatch($start, $end, $match);
    }
}
