<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer;

use VStelmakh\UrlHighlight\Highlighter\Highlighter;
use VStelmakh\UrlHighlight\Matcher\UrlMatch;
use VStelmakh\UrlHighlight\Replacer\Strategy\Strategy;
use VStelmakh\UrlHighlight\Replacer\Tokenizer\Token\PlainToken;
use VStelmakh\UrlHighlight\Replacer\Tokenizer\Token\TagToken;
use VStelmakh\UrlHighlight\Replacer\Tokenizer\Tokenizer;

/**
 * Tokenizes HTML input, collects URL matches from visible text runs (skipping `<a>`, `<script>`, and `<style>`
 * content), and splices the rendered links back in. Matching within each run is delegated to the injected Strategy,
 * making new input modes possible without modifying this class.
 *
 * @internal
 */
final readonly class Replacer
{
    /**
     * Tags whose content must not be highlighted (an existing link, or non-visible content).
     * @var array<string, true>
     */
    private const array SKIP_TAG_MAP = ['a' => true, 'script' => true, 'style' => true];

    public function __construct(
        private Tokenizer $tokenizer,
        private Strategy $strategy,
        private Renderer $renderer,
    ) {}

    public function highlight(string $html, Highlighter $highlighter): string
    {
        $matches = $this->collectMatches($html);
        return $this->renderer->render($html, $matches, $highlighter);
    }

    /**
     * Walk the tokens, skip the content of skip tags, and yield URL matches from every visible text run.
     *
     * @return \Generator<UrlMatch>
     */
    private function collectMatches(string $html): \Generator
    {
        $cursor = 0;
        $skipDepth = 0;

        foreach ($this->tokenizer->tokenize($html) as $token) {
            $contents = $token->toString();

            if ($token instanceof PlainToken && $skipDepth === 0) {
                yield from $this->strategy->match($contents, $cursor);
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
