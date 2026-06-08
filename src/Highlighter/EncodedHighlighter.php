<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter;

use VStelmakh\UrlHighlight\Highlighter\Decoder\DecodedString;
use VStelmakh\UrlHighlight\Highlighter\Decoder\Decoder;
use VStelmakh\UrlHighlight\Highlighter\Linker\Linker;
use VStelmakh\UrlHighlight\Highlighter\Tokenizer\Token\TagToken;
use VStelmakh\UrlHighlight\Highlighter\Tokenizer\Tokenizer;
use VStelmakh\UrlHighlight\Matcher\Matcher;
use VStelmakh\UrlHighlight\Matcher\UrlMatch;

/**
 * Highlights URLs in HTML-entity encoded input. URLs are matched against the decoded form (so
 * entities like &amp; do not break matching) and everywhere it appears - in plain text and inside
 * attribute values of decoded tags (bounded by the attribute's quote characters so a trailing quote
 * is not swallowed). The original encoded characters are kept verbatim in the output.
 *
 * @internal
 */
final readonly class EncodedHighlighter
{
    public function __construct(
        private Decoder $decoder,
        private Tokenizer $tokenizer,
        private Matcher $matcher,
        private Renderer $renderer,
    ) {}

    public function highlight(string $encoded, Linker $linker): string
    {
        $decoded = $this->decoder->decode($encoded);
        return $this->renderer->render($encoded, $this->collectMatches($decoded), $linker);
    }

    /**
     * Tokenize the decoded form and find URLs in plain text spans and inside attribute values,
     * returning each as an OffsetMatch whose range is mapped back into the encoded string.
     *
     * @return list<OffsetMatch>
     */
    private function collectMatches(DecodedString $decoded): array
    {
        $result = [];
        $cursor = 0;

        foreach ($this->tokenizer->tokenize($decoded->value) as $token) {
            $contents = $token->toString();
            $matches = $token instanceof TagToken
                ? $this->attributeMatches($contents, $decoded, $cursor)
                : $this->textMatches($contents, $decoded, $cursor);

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
    private function textMatches(string $text, DecodedString $decoded, int $baseOffset): array
    {
        $result = [];
        foreach ($this->matcher->match($text) as $match) {
            $result[] = $this->toEncodedMatch($decoded, $baseOffset + $match->offset, $match);
        }
        return $result;
    }

    /**
     * @return list<OffsetMatch>
     */
    private function attributeMatches(string $tagContents, DecodedString $decoded, int $baseOffset): array
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
                $result[] = $this->toEncodedMatch($decoded, $baseOffset + $quoted[1] + $match->offset, $match);
            }
        }
        return $result;
    }

    private function toEncodedMatch(DecodedString $decoded, int $decodedStart, UrlMatch $match): OffsetMatch
    {
        $start = $decoded->toEncodedOffset($decodedStart);
        $end = $decoded->toEncodedOffset($decodedStart + strlen($match->match));
        return new OffsetMatch($start, $end, $match);
    }
}
