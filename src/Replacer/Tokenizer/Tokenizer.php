<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer\Tokenizer;

use VStelmakh\UrlHighlight\Replacer\Tokenizer\Token\CommentToken;
use VStelmakh\UrlHighlight\Replacer\Tokenizer\Token\PlainToken;
use VStelmakh\UrlHighlight\Replacer\Tokenizer\Token\TagToken;
use VStelmakh\UrlHighlight\Replacer\Tokenizer\Token\Token;

/**
 * Splits an HTML string into a sequence of {@see Token}.
 *
 * @internal
 */
final class Tokenizer
{
    /**
     * @return \Generator<Token>
     */
    public function tokenize(string $string): \Generator
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
}
