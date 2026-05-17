<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter\Replacer;

use VStelmakh\UrlHighlight\Highlighter\Linker\Linker;
use VStelmakh\UrlHighlight\Highlighter\Tokenizer\Token\TagToken;
use VStelmakh\UrlHighlight\Highlighter\Tokenizer\Tokenizer;
use VStelmakh\UrlHighlight\Matcher\EntityDecoder;
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

        foreach ($matches as [$start, $end, $match]) {
            $encodedStart = $decoded->toEncodedOffset($start);
            $encodedEnd = $decoded->toEncodedOffset($end);

            $result .= substr($text, $cursor, $encodedStart - $cursor);
            $result .= $linker->render($match);
            $cursor = $encodedEnd;
        }
        $result .= substr($text, $cursor);

        return $result;
    }

    /**
     * Tokenize the decoded form and find URLs in plain text spans and inside attribute values.
     *
     * @return list<array{int, int, UrlMatch}>
     */
    private function collectMatches(string $decoded): array
    {
        $result = [];
        $cursor = 0;

        foreach ($this->tokenizer->tokenize($decoded) as $token) {
            $contents = $token->toString();

            if ($token instanceof TagToken) {
                foreach ($this->matchInAttributes($contents) as [$valueOffset, $match]) {
                    $start = $cursor + $valueOffset + $match->offset;
                    $result[] = [$start, $start + strlen($match->match), $match];
                }
            } else {
                foreach ($this->matcher->match($contents) as $match) {
                    $start = $cursor + $match->offset;
                    $result[] = [$start, $start + strlen($match->match), $match];
                }
            }

            $cursor += strlen($contents);
        }

        return $result;
    }

    /**
     * @return \Generator<array{int, UrlMatch}> [valueOffsetWithinTag, UrlMatch] pairs.
     */
    private function matchInAttributes(string $tagContents): \Generator
    {
        if (!preg_match_all(
            '/=\s*(?:"([^"]*)"|\'([^\']*)\')/',
            $tagContents,
            $matches,
            PREG_OFFSET_CAPTURE | PREG_SET_ORDER,
        )) {
            return;
        }

        foreach ($matches as $m) {
            [$value, $valueOffset] = $m[1][1] !== -1 ? $m[1] : $m[2];
            foreach ($this->matcher->match($value) as $urlMatch) {
                yield [$valueOffset, $urlMatch];
            }
        }
    }
}
