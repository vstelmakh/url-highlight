<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer;

use VStelmakh\UrlHighlight\Highlighter\Highlighter;
use VStelmakh\UrlHighlight\Matcher\Matcher;
use VStelmakh\UrlHighlight\Replacer\Decoder\Decoder;
use VStelmakh\UrlHighlight\Replacer\Strategy\DirectStrategy;
use VStelmakh\UrlHighlight\Replacer\Strategy\EncodedStrategy;
use VStelmakh\UrlHighlight\Replacer\Strategy\Strategy;
use VStelmakh\UrlHighlight\Replacer\Tokenizer\HtmlTokenizer;
use VStelmakh\UrlHighlight\Replacer\Tokenizer\PlainTokenizer;
use VStelmakh\UrlHighlight\Replacer\Tokenizer\Token\PlainToken;
use VStelmakh\UrlHighlight\Replacer\Tokenizer\Token\TagToken;
use VStelmakh\UrlHighlight\Replacer\Tokenizer\Token\TagType;
use VStelmakh\UrlHighlight\Replacer\Tokenizer\Tokenizer;

/**
 * Collects replacements for every URL in a visible text run of the input (skipping the content of the tags listed in
 * {@see self::isSkipTag()}), and splices the rendered links back in. Splitting the input and matching within each run
 * are delegated to the injected Tokenizer and Strategy, making new input formats possible without modifying this class.
 *
 * @internal
 */
final readonly class Replacer
{
    public static function createPlain(Matcher $matcher): self
    {
        $directStrategy = new DirectStrategy($matcher);
        return new self(new PlainTokenizer(), $directStrategy, new Renderer());
    }

    public static function createHtml(Matcher $matcher): self
    {
        $directStrategy = new DirectStrategy($matcher);
        return new self(new HtmlTokenizer(), $directStrategy, new Renderer());
    }

    public static function createHtmlEncoded(Matcher $matcher): self
    {
        $encodedStrategy = new EncodedStrategy(new Decoder(), $matcher);
        return new self(new HtmlTokenizer(), $encodedStrategy, new Renderer());
    }

    public function __construct(
        private Tokenizer $tokenizer,
        private Strategy $strategy,
        private Renderer $renderer,
    ) {}

    public function replace(string $text, Highlighter $highlighter): string
    {
        $replacements = $this->collectReplacements($text);
        return $this->renderer->render($text, $replacements, $highlighter);
    }

    /**
     * @return \Generator<Replacement>
     */
    private function collectReplacements(string $text): \Generator
    {
        $cursor = 0;
        $skipDepth = 0;

        foreach ($this->tokenizer->tokenize($text) as $token) {
            $contents = $token->__toString();

            if ($token instanceof PlainToken && $skipDepth === 0) {
                yield from $this->strategy->findReplacements($contents, $cursor);
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
