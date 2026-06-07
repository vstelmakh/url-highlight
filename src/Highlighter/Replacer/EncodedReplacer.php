<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter\Replacer;

use VStelmakh\UrlHighlight\Highlighter\Encoder\EntityDecoder;
use VStelmakh\UrlHighlight\Highlighter\Linker\Linker;
use VStelmakh\UrlHighlight\Highlighter\Tokenizer\Token\TagToken;
use VStelmakh\UrlHighlight\Highlighter\Tokenizer\Tokenizer;
use VStelmakh\UrlHighlight\Matcher\Matcher;
use VStelmakh\UrlHighlight\Matcher\UrlMatch;

/**
 * Replaces URLs in HTML-entity encoded input. URLs are matched against the decoded form
 * (so entities like &amp; do not break matching) and against attribute values inside
 * decoded HTML tags (bounded by the attribute's quote characters, preserving URLs that
 * legitimately contain ' or other sub-delims). The original encoded characters are kept
 * verbatim in the output.
 *
 * @internal
 */
final readonly class EncodedReplacer implements Replacer
{
    public function __construct(
        private Matcher $matcher,
        private Tokenizer $tokenizer,
        private EntityDecoder $entityDecoder,
    ) {}

    #[\Override]
    public function replace(string $text, Linker $linker): string
    {
        $decoded = $this->entityDecoder->decode($text);
        $matches = $this->collectMatches($decoded->value);
        if ($matches === []) {
            return $text;
        }

        $result = '';
        $cursor = 0;

        foreach ($matches as [$start, $match]) {
            $encodedStart = $decoded->toEncodedOffset($start);
            $encodedEnd = $decoded->toEncodedOffset($start + strlen($match->match));

            $result .= substr($text, $cursor, $encodedStart - $cursor);
            $result .= $linker->render($match);
            $cursor = $encodedEnd;
        }
        $result .= substr($text, $cursor);

        return $result;
    }

    /**
     * Tokenize the decoded form and find URLs, returning each as a [start offset within the decoded
     * string, match] pair. Plain spans are matched directly; inside tags only attribute values are
     * searched so a URL is bounded by its quote characters rather than swallowing the closing quote.
     *
     * @return list<array{int, UrlMatch}>
     */
    private function collectMatches(string $decoded): array
    {
        $result = [];
        $cursor = 0;

        foreach ($this->tokenizer->tokenize($decoded) as $token) {
            $contents = $token->toString();
            $matches = $token instanceof TagToken
                ? $this->matchInAttributes($contents)
                : $this->matchInText($contents);

            foreach ($matches as [$offset, $match]) {
                $result[] = [$cursor + $offset, $match];
            }

            $cursor += strlen($contents);
        }

        return $result;
    }

    /**
     * @return \Generator<array{int, UrlMatch}> [offset within text, match] pairs.
     */
    private function matchInText(string $text): \Generator
    {
        foreach ($this->matcher->match($text) as $match) {
            yield [$match->offset, $match];
        }
    }

    /**
     * @return \Generator<array{int, UrlMatch}> [offset within tag, match] pairs.
     */
    private function matchInAttributes(string $tagContents): \Generator
    {
        $found = preg_match_all(
            '/=\s*(?:"([^"]*)"|\'([^\']*)\')/',
            $tagContents,
            $matches,
            PREG_OFFSET_CAPTURE | PREG_SET_ORDER | PREG_UNMATCHED_AS_NULL,
        );
        if ($found === false || $found === 0) {
            return;
        }

        foreach ($matches as $attribute) {
            // Exactly one quote branch matches; the other capture group is null (PREG_UNMATCHED_AS_NULL).
            [$value, $valueOffset] = $attribute[1][1] !== -1 ? $attribute[1] : $attribute[2];
            if ($value === null) {
                continue;
            }
            foreach ($this->matcher->match($value) as $match) {
                yield [$valueOffset + $match->offset, $match];
            }
        }
    }
}
