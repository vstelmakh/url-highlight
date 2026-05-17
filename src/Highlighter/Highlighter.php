<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter;

use VStelmakh\UrlHighlight\Highlighter\Linker\Linker;
use VStelmakh\UrlHighlight\Highlighter\Replacer\Replacer;
use VStelmakh\UrlHighlight\Highlighter\Tokenizer\Token\PlainToken;
use VStelmakh\UrlHighlight\Highlighter\Tokenizer\Token\TagToken;
use VStelmakh\UrlHighlight\Highlighter\Tokenizer\Tokenizer;

/**
 * @internal
 */
final readonly class Highlighter
{
    /**
     * Tags whose content should not be highlighted (e.g. a link, or non-visible content).
     * @var array<string, true>
     */
    private const array SKIP_TAG_MAP = ['a' => true, 'script' => true, 'style' => true];

    public function __construct(
        private Tokenizer $tokenizer,
        private Replacer $replacer,
    ) {}

    public function highlight(string $html, Linker $linker): string
    {
        $result = '';
        $skipDepth = 0;

        foreach ($this->tokenizer->tokenize($html) as $token) {
            if ($token instanceof PlainToken) {
                $result .= $skipDepth > 0 ? $token->contents : $this->replacer->replace($token->contents, $linker);
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

    private function isSkipTag(string $tag): bool
    {
        return isset(self::SKIP_TAG_MAP[$tag]);
    }
}
