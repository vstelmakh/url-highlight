<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer\Extractor;

use VStelmakh\UrlHighlight\Replacer\Extractor\Token\PlainToken;
use VStelmakh\UrlHighlight\Replacer\Extractor\Token\TagToken;
use VStelmakh\UrlHighlight\Replacer\Extractor\Token\TagType;

/**
 * Extracts the linkable text of an HTML input, skipping tags and the content of the elements listed in
 * {@see self::isSkipTag()}.
 *
 * @internal
 */
final readonly class Extractor
{
    public function __construct(
        private Tokenizer $tokenizer,
    ) {}

    /**
     * @return \Generator<int, string> Text that may hold a link, keyed by its byte offset in the given input.
     */
    public function extract(string $html): \Generator
    {
        $cursor = 0;
        $skipDepth = 0;

        foreach ($this->tokenizer->tokenize($html) as $token) {
            $contents = $token->__toString();

            if ($token instanceof PlainToken && $skipDepth === 0) {
                yield $cursor => $contents;
            } elseif ($token instanceof TagToken && $this->isSkipTag($token->name)) {
                $skipDepth = match ($token->type) {
                    TagType::Opening => $skipDepth + 1,
                    TagType::Closing => max(0, $skipDepth - 1),
                    TagType::SelfClosing => $skipDepth,
                };
            }

            $cursor += strlen($contents);
        }
    }

    private function isSkipTag(string $tag): bool
    {
        return match ($tag) {
            'a' => true,        // already a link
            'button' => true,   // content model forbids interactive descendants
            'datalist' => true, // text-only content model (covers the nested option elements)
            'math' => true,     // foreign content, an HTML anchor becomes a MathML element
            'script' => true,   // raw text
            'select' => true,   // text-only content model (covers the nested option and optgroup elements)
            'style' => true,    // raw text
            'svg' => true,      // foreign content, an HTML anchor becomes an SVG element
            'textarea' => true, // raw text
            'title' => true,    // raw text
            default => false,
        };
    }
}
