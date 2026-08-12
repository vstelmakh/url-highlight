<?php

declare(strict_types=1);

// @php-cs-fixer-ignore array_indentation

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
final readonly class HtmlTokenizer implements Tokenizer
{
    /**
     * @return \Generator<Token>
     */
    #[\Override]
    public function tokenize(string $text): \Generator
    {
        // Matches HTML comments and tags, handling quoted attributes so that ">" inside values is not treated as tag.
        $pattern = implode('', [
            '/',
            '<!--.*?-->',             // html comment
            '|',                      // or
            '<',                      // tag start
                '(?:',                    // non-capturing group
                    '[^"\'<>]',               // any chars except: "<", ">", '"', "'",
                    '|',                      // or
                    '"[^"]*"',                // double-quoted attribute value
                    '|',                      // or
                    '\'[^\']*\'',             // single-quoted attribute value
                ')*',                     // close group, optional
            '>',                      // tag end
            '/s',                     // single-line (dot matches newline)
        ]);

        $cursor = 0;

        // Matching one tag at a time from a moving cursor, rather than splitting the whole input up front, keeps only
        // the current token in memory. This significantly saves memory for markup-dense inputs.
        while (preg_match($pattern, $text, $match, PREG_OFFSET_CAPTURE, $cursor) === 1) {
            [$contents, $contentsOffset] = $match[0];

            // Adjacent tags, e.g. "</b><i>", leave nothing in between.
            $plain = substr($text, $cursor, $contentsOffset - $cursor);
            if ($plain !== '') {
                yield new PlainToken($plain);
            }

            yield str_starts_with($contents, '<!--') ? new CommentToken($contents) : $this->createTagToken($contents);

            $cursor = $contentsOffset + strlen($contents);
        }

        // On PCRE failure the rest is treated as plain text.
        yield new PlainToken(substr($text, $cursor));
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
