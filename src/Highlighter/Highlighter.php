<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter;

use VStelmakh\UrlHighlight\Highlighter\Linker\Linker;
use VStelmakh\UrlHighlight\Highlighter\Tokenizer\Token\PlainToken;
use VStelmakh\UrlHighlight\Highlighter\Tokenizer\Token\TagToken;
use VStelmakh\UrlHighlight\Highlighter\Tokenizer\Tokenizer;
use VStelmakh\UrlHighlight\Matcher\DecodedString;
use VStelmakh\UrlHighlight\Matcher\EntityDecoder;
use VStelmakh\UrlHighlight\Matcher\Matcher;

/**
 * @internal
 */
final readonly class Highlighter
{
    /**
     * Tags which content should not be highlighted (e.g. a link, or non-visible content).
     * @var array<string, true>
     */
    private const array SKIP_TAG_MAP = ['a' => true, 'script' => true, 'style' => true];

    public function __construct(
        private Matcher $matcher,
        private Tokenizer $tokenizer,
        private EntityDecoder $entityDecoder,
    ) {}

    public function highlight(string $html, Linker $linker, bool $isHtmlEncoded): string
    {
        $result = '';
        $skipDepth = 0;

        $tokens = $this->tokenizer->tokenize($html);

        foreach ($tokens as $token) {
            if ($token instanceof PlainToken) {
                $result .= $skipDepth > 0 ? $token->toString() : $this->highlightUrls($token->toString(), $linker, $isHtmlEncoded);
                continue;
            }

            if ($token instanceof TagToken && $this->isSkipTag($token->name)) {
                if ($token->isClosing) {
                    $skipDepth = max(0, $skipDepth - 1);
                } elseif (!$token->isSelfClosing) {
                    $skipDepth++;
                }
            }

            $result .= $token->toString();
        }

        return $result;
    }

    private function highlightUrls(string $string, Linker $linker, bool $isHtmlEncoded): string
    {
        $decoded = $isHtmlEncoded
            ? $this->entityDecoder->decode($string)
            : new DecodedString($string, []);

        $matches = $this->matcher->match($decoded->value);
        if ($matches === []) {
            return $string;
        }

        $result = '';
        $cursor = 0;

        foreach ($matches as $match) {
            $start = $decoded->toEncodedOffset($match->offset);
            $end = $decoded->toEncodedOffset($match->offset + strlen($match->match));

            $result .= substr($string, $cursor, $start - $cursor);
            $result .= $linker->render($match);
            $cursor = $end;
        }
        $result .= substr($string, $cursor);

        return $result;
    }

    private function isSkipTag(string $tag): bool
    {
        return isset(self::SKIP_TAG_MAP[$tag]);
    }
}
