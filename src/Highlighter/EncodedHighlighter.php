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
 * Highlights URLs in partially HTML-entity encoded input. The input may mix genuine HTML markup with
 * HTML-escaped text (e.g. a fragment produced by htmlspecialchars embedded in a real page). Genuine
 * tags keep their literal "<"..">", while escaped markup stays inside the text runs as entities like
 * &lt;. The same skip-tag rules as plain mode apply (the content of existing links, scripts and styles
 * is left untouched) - this is handled by the shared TextSpanExtractor. Each remaining text run is then
 * decoded so URLs are matched against their true characters (so entities like &amp; do not break
 * matching) both in escaped tag attribute values and in escaped link text, while the original encoded
 * characters are kept verbatim in the output.
 *
 * @internal
 */
final readonly class EncodedHighlighter
{
    public function __construct(
        private Decoder $decoder,
        private TextSpanExtractor $spanExtractor,
        private Tokenizer $tokenizer,
        private Matcher $matcher,
        private Renderer $renderer,
    ) {}

    public function highlight(string $encoded, Linker $linker): string
    {
        return $this->renderer->render($encoded, $this->collectMatches($encoded), $linker);
    }

    /**
     * @return list<OffsetMatch>
     */
    private function collectMatches(string $encoded): array
    {
        $result = [];
        foreach ($this->spanExtractor->extract($encoded) as $span) {
            foreach ($this->encodedSpanMatches($span->content, $span->offset) as $offsetMatch) {
                $result[] = $offsetMatch;
            }
        }
        return $result;
    }

    /**
     * Decode a span of escaped text, then find URLs in its plain text spans and inside the attribute
     * values of any escaped tags it contains, mapping each range back into the original encoded input.
     *
     * @return list<OffsetMatch>
     */
    private function encodedSpanMatches(string $encodedSpan, int $spanOffset): array
    {
        $decoded = $this->decoder->decode($encodedSpan);

        $result = [];
        $cursor = 0;

        foreach ($this->tokenizer->tokenize($decoded->value) as $token) {
            $contents = $token->toString();
            $matches = $token instanceof TagToken
                ? $this->attributeMatches($contents, $decoded, $spanOffset, $cursor)
                : $this->textMatches($contents, $decoded, $spanOffset, $cursor);

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
