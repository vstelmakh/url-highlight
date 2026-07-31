<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer\Tokenizer;

use VStelmakh\UrlHighlight\Replacer\Tokenizer\Token\CommentToken;
use VStelmakh\UrlHighlight\Replacer\Tokenizer\Token\PlainToken;
use VStelmakh\UrlHighlight\Replacer\Tokenizer\Token\TagToken;
use VStelmakh\UrlHighlight\Replacer\Tokenizer\Token\TagType;
use VStelmakh\UrlHighlight\Replacer\Tokenizer\Token\Token;

/**
 * Splits an HTML string into a sequence of {@see Token}.
 *
 * @internal
 */
final readonly class Tokenizer
{
    /**
     * @return \Generator<Token>
     */
    public function tokenize(string $html): \Generator
    {
        // Matches HTML comments and tags, handling quoted attributes so that ">" inside values is not treated as tag.
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
                '>',                          // tag end
            ')',                          // close group
            '/s',                     // single-line (dot matches newline)
        ]);
        $split = preg_split($regex, $html, -1, PREG_SPLIT_DELIM_CAPTURE);

        if ($split === false) {
            // Tokenization failed (e.g. backtrack limit exceeded). Fallback to treat the input string as plain text.
            yield new PlainToken($html);
            return;
        }

        // preg_split with a single capture group always alternates [plain, tag, plain, tag, ...],
        // so even indices are plain text tokens, and odd indices are tag/comment tokens.
        foreach ($split as $i => $contents) {
            if ($i % 2 === 0) {
                yield new PlainToken($contents);
                continue;
            }

            if (str_starts_with($contents, '<!--')) {
                yield new CommentToken($contents);
                continue;
            }

            yield $this->createTagToken($contents);
        }
    }

    private function createTagToken(string $contents): TagToken
    {
        preg_match(
            '/^<(?<closing>\/)?(?<name>[a-z][a-z0-9]*)/i',
            $contents,
            $matches,
            PREG_UNMATCHED_AS_NULL,
        );

        $name = strtolower($matches['name'] ?? '');

        $type = match (true) {
            ($matches['closing'] ?? null) !== null => TagType::Closing,
            (bool) preg_match('/\/\s*>$/', $contents) => TagType::SelfClosing,
            default => TagType::Opening,
        };

        return new TagToken($contents, $name, $type);
    }
}
