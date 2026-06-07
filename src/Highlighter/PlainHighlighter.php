<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter;

use VStelmakh\UrlHighlight\Highlighter\Linker\Linker;
use VStelmakh\UrlHighlight\Highlighter\Tokenizer\Token\PlainToken;
use VStelmakh\UrlHighlight\Highlighter\Tokenizer\Token\TagToken;
use VStelmakh\UrlHighlight\Highlighter\Tokenizer\Tokenizer;
use VStelmakh\UrlHighlight\Matcher\Matcher;

/**
 * Highlights URLs in plain HTML: tokenizes the input, matches URLs in text while skipping the
 * content of tags that must not be linkified (e.g. existing links, scripts, styles).
 *
 * @internal
 */
final readonly class PlainHighlighter
{
    /**
     * Tags whose content should not be highlighted (e.g. a link, or non-visible content).
     * @var array<string, true>
     */
    private const array SKIP_TAG_MAP = ['a' => true, 'script' => true, 'style' => true];

    public function __construct(
        private Tokenizer $tokenizer,
        private Matcher $matcher,
        private Renderer $renderer,
    ) {}

    public function highlight(string $html, Linker $linker): string
    {
        $matches = $this->collectMatches($html);
        return $this->renderer->render($html, $matches, $linker);
    }

    /**
     * @return list<OffsetMatch>
     */
    private function collectMatches(string $html): array
    {
        $result = [];
        $cursor = 0;
        $skipDepth = 0;

        foreach ($this->tokenizer->tokenize($html) as $token) {
            $contents = $token->toString();

            if ($token instanceof PlainToken && $skipDepth === 0) {
                $matches = $this->matcher->match($contents);
                foreach ($matches as $match) {
                    $start = $cursor + $match->offset;
                    $end = $start + strlen($match->match);
                    $result[] = new OffsetMatch($start, $end, $match);
                }
            } elseif ($token instanceof TagToken && $this->isSkipTag($token->name)) {
                if ($token->isClosing) {
                    $skipDepth = max(0, $skipDepth - 1);
                } elseif (!$token->isSelfClosing) {
                    $skipDepth++;
                }
            }

            $cursor += strlen($contents);
        }

        return $result;
    }

    private function isSkipTag(string $tag): bool
    {
        return isset(self::SKIP_TAG_MAP[$tag]);
    }
}
