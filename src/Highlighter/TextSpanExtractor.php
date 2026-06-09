<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter;

use VStelmakh\UrlHighlight\Highlighter\Tokenizer\Token\PlainToken;
use VStelmakh\UrlHighlight\Highlighter\Tokenizer\Token\TagToken;
use VStelmakh\UrlHighlight\Highlighter\Tokenizer\Tokenizer;

/**
 * Tokenizes HTML and yields the text runs that are eligible for URL highlighting: plain text outside
 * of tags whose content must not be linkified (existing links, scripts and styles). The content of
 * those skip tags is omitted, while every other tag merely acts as a boundary between adjacent runs.
 *
 * @internal
 */
final readonly class TextSpanExtractor
{
    /**
     * Tags whose content should not be highlighted (e.g. a link, or non-visible content).
     * @var array<string, true>
     */
    private const array SKIP_TAG_MAP = ['a' => true, 'script' => true, 'style' => true];

    public function __construct(
        private Tokenizer $tokenizer,
    ) {}

    /**
     * @return \Generator<TextSpan>
     */
    public function extract(string $html): \Generator
    {
        $cursor = 0;
        $skipDepth = 0;

        foreach ($this->tokenizer->tokenize($html) as $token) {
            $contents = $token->toString();

            if ($token instanceof PlainToken && $skipDepth === 0) {
                yield new TextSpan($contents, $cursor);
            } elseif ($token instanceof TagToken && $this->isSkipTag($token->name)) {
                if ($token->isClosing) {
                    $skipDepth = max(0, $skipDepth - 1);
                } elseif (!$token->isSelfClosing) {
                    $skipDepth++;
                }
            }

            $cursor += strlen($contents);
        }
    }

    private function isSkipTag(string $tag): bool
    {
        return isset(self::SKIP_TAG_MAP[$tag]);
    }
}
