<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter;

use VStelmakh\UrlHighlight\Highlighter\Linker\Linker;
use VStelmakh\UrlHighlight\Highlighter\Strategy\Strategy;
use VStelmakh\UrlHighlight\Highlighter\Tokenizer\Token\PlainToken;
use VStelmakh\UrlHighlight\Highlighter\Tokenizer\Token\TagToken;
use VStelmakh\UrlHighlight\Highlighter\Tokenizer\Tokenizer;

/**
 * Renders URLs in HTML as links: tokenizes the input and collects URL matches only in visible text runs
 * - leaving the content of existing links, scripts and styles untouched - then splices the rendered
 * links back in. How a run is matched is delegated to the injected Strategy, so a new input mode is
 * supported by providing a different strategy rather than changing this class.
 *
 * @internal
 */
final readonly class Highlighter
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

    public function highlight(string $html, Linker $linker): string
    {
        $matches = $this->collectMatches($html);
        return $this->renderer->render($html, $matches, $linker);
    }

    /**
     * Walk the tokens, skip the content of skip tags, and yield URL matches from every visible text run.
     *
     * @return \Generator<RangeMatch>
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
