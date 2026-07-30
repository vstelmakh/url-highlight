<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer;

use VStelmakh\UrlHighlight\Highlighter\Highlighter;
use VStelmakh\UrlHighlight\Matcher\Matcher;
use VStelmakh\UrlHighlight\Matcher\UrlMatch;
use VStelmakh\UrlHighlight\Replacer\Decoder\Decoder;
use VStelmakh\UrlHighlight\Replacer\Strategy\EncodedStrategy;
use VStelmakh\UrlHighlight\Replacer\Strategy\PlainStrategy;
use VStelmakh\UrlHighlight\Replacer\Strategy\Strategy;
use VStelmakh\UrlHighlight\Replacer\Tokenizer\Token\PlainToken;
use VStelmakh\UrlHighlight\Replacer\Tokenizer\Token\TagToken;
use VStelmakh\UrlHighlight\Replacer\Tokenizer\Token\TagType;
use VStelmakh\UrlHighlight\Replacer\Tokenizer\Tokenizer;

/**
 * Tokenizes HTML input, collects URL matches from visible text runs (skipping the content of the tags listed in
 * {@see self::isSkipTag()}), and splices the rendered links back in. Matching within each run is delegated to the
 * injected Strategy, making new input modes possible without modifying this class.
 *
 * @internal
 */
final readonly class Replacer
{
    public static function createPlain(Matcher $matcher): self
    {
        $plainStrategy = new PlainStrategy($matcher);
        return new self(new Tokenizer(), $plainStrategy, new Renderer());
    }

    public static function createEncoded(Matcher $matcher): self
    {
        $encodedStrategy = new EncodedStrategy(new Decoder(), new Tokenizer(), $matcher);
        return new self(new Tokenizer(), $encodedStrategy, new Renderer());
    }

    public function __construct(
        private Tokenizer $tokenizer,
        private Strategy $strategy,
        private Renderer $renderer,
    ) {}

    public function highlight(string $text, Highlighter $highlighter): string
    {
        $matches = $this->collectMatches($text);
        return $this->renderer->render($text, $matches, $highlighter);
    }

    /**
     * Walk the tokens, skip the content of skip tags, and yield URL matches from every visible text run.
     *
     * @return \Generator<UrlMatch>
     */
    private function collectMatches(string $text): \Generator
    {
        $cursor = 0;
        $skipDepth = 0;

        foreach ($this->tokenizer->tokenize($text) as $token) {
            $contents = $token->__toString();

            if ($token instanceof PlainToken && $skipDepth === 0) {
                yield from $this->strategy->match($contents, $cursor);
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
