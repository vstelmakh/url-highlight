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
        $cursor = 0;

        foreach ($this->tokenizer->tokenize($decoded->value) as $token) {
            $contents = $token->toString();
            yield from $token instanceof TagToken
                ? $this->attributeMatches($contents, $decoded, $offset, $cursor)
                : $this->textMatches($contents, $decoded, $offset, $cursor);

            $cursor += strlen($contents);
        }
    }

    /**
     * @return \Generator<UrlMatch>
     */
    private function textMatches(string $text, DecodedString $decoded, int $spanOffset, int $baseOffset): \Generator
    {
        foreach ($this->matcher->match($text) as $match) {
            yield $this->mapToSource($decoded, $spanOffset, $baseOffset, $match);
        }
    }

    /**
     * @return \Generator<UrlMatch>
     */
    private function attributeMatches(string $tagContents, DecodedString $decoded, int $spanOffset, int $baseOffset): \Generator
    {
        $found = preg_match_all(
            '/=\s*(?:"([^"]*)"|\'([^\']*)\')/',
            $tagContents,
            $attributes,
            PREG_OFFSET_CAPTURE | PREG_SET_ORDER | PREG_UNMATCHED_AS_NULL,
        );
        if ($found === false || $found === 0) {
            return;
        }

        foreach ($attributes as $attribute) {
            // Exactly one quote branch matches; the other capture group is null (PREG_UNMATCHED_AS_NULL).
            $quoted = $attribute[1][1] !== -1 ? $attribute[1] : $attribute[2];
            $value = $quoted[0];
            if ($value === null) {
                continue;
            }
            foreach ($this->matcher->match($value) as $match) {
                yield $this->mapToSource($decoded, $spanOffset, $baseOffset + $quoted[1], $match);
            }
        }
    }

    private function mapToSource(DecodedString $decoded, int $spanOffset, int $baseOffset, UrlMatch $match): UrlMatch
    {
        $start = $spanOffset + $decoded->toEncodedOffset($baseOffset + $match->start);
        $end = $spanOffset + $decoded->toEncodedOffset($baseOffset + $match->end);
        return new UrlMatch($start, $end, $match->url);
    }
}
