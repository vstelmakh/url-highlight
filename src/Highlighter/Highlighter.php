<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter;

use VStelmakh\UrlHighlight\Highlighter\Linker\LinkerInterface;
use VStelmakh\UrlHighlight\Highlighter\Token\AbstractToken;
use VStelmakh\UrlHighlight\Highlighter\Token\CommentToken;
use VStelmakh\UrlHighlight\Highlighter\Token\PlainToken;
use VStelmakh\UrlHighlight\Highlighter\Token\TagToken;
use VStelmakh\UrlHighlight\Matcher\Matcher;

/**
 * @internal
 */
final readonly class Highlighter
{
    public function __construct(
        private Matcher $matcher,
    ) {}

    public function highlight(string $html, LinkerInterface $linker): string
    {
        $tokens = $this->tokenize($html);

        $result = '';
        $skipDepth = 0;

        foreach ($tokens as $token) {
            if ($token instanceof PlainToken) {
                $result .= $skipDepth > 0 ? $token->contents : $this->replaceUrls($token->contents, $linker);
                continue;
            }

            if ($token instanceof TagToken && $token->shouldSkip()) {
                if ($token->isClosing) {
                    $skipDepth = max(0, $skipDepth - 1);
                } elseif (!$token->isSelfClosing) {
                    $skipDepth++;
                }
            }

            $result .= $token->contents;
        }

        return $result;
    }

    /**
     * @return \Generator<AbstractToken>
     */
    private function tokenize(string $string): \Generator
    {
        // Handling quoted attributes so that ">" inside values is not treated as tag end.
        $regex = implode('', [
            '/',
            '(',                          // capturing group
                '<!--.*?-->',                 // html comment
                '|',                          // or
                '<',                          // tag start
                    '(?:',                        // non-capturing group
                        '[^"\'<>]',                   // any chars except: "<", ">", '"', "'",
                        '|',                          // or
                        '"[^"]*"',                    // double-quoted attribute value
                        '|',                          // or
                        '\'[^\']*\'',                 // single-quoted attribute value
                    ')*',                         // close group, optional
                '>',                          // end tag
            ')',                          // close group
            '/s',                     // single-line (dot matches newline)
        ]);

        $split = preg_split($regex, $string, -1, PREG_SPLIT_DELIM_CAPTURE);

        if ($split === false) {
            // Tokenization failed (e.g. backtrack limit exceeded). Fallback to treat the input string as plain text.
            yield new PlainToken($string);
            return;
        }

        // preg_split with a single capture group always alternates [plain, tag, plain, tag, ...],
        // so "even" indices are plain text tokens, and "odd" indices are tag/comment tokens.
        foreach ($split as $i => $contents) {
            if ($i % 2 === 0) {
                yield new PlainToken($contents);
                continue;
            }

            if (str_starts_with($contents, '<!--')) {
                yield new CommentToken($contents);
                continue;
            }

            preg_match(
                '/^<(?<closing>\/)?(?<name>[a-z][a-z0-9]*)/i',
                $contents,
                $matches,
                PREG_UNMATCHED_AS_NULL
            );

            $isClosing = ($matches['closing'] ?? null) !== null;
            yield new TagToken(
                contents: $contents,
                name: strtolower($matches['name'] ?? ''),
                isClosing: $isClosing,
                isSelfClosing: !$isClosing && (bool) preg_match('/\/\s*>$/', $contents),
            );
        }
    }

    private function replaceUrls(string $string, LinkerInterface $linker): string
    {
        $matches = $this->matcher->match($string);
        $offset = 0;

        foreach ($matches as $match) {
            $replacement = $linker->link($match);
            $position = $match->offset + $offset;
            $length = strlen($match->match);
            $string = substr_replace($string, $replacement, $position, $length);
            $offset += strlen($replacement) - $length;
        }

        return $string;
    }
}
