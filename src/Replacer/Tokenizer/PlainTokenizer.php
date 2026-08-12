<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer\Tokenizer;

use VStelmakh\UrlHighlight\Replacer\Tokenizer\Token\PlainToken;
use VStelmakh\UrlHighlight\Replacer\Tokenizer\Token\Token;

/**
 * Yields the whole string as a single {@see PlainToken}, leaving any markup in it uninterpreted.
 *
 * @internal
 */
final readonly class PlainTokenizer implements Tokenizer
{
    /**
     * @return \Generator<Token>
     */
    #[\Override]
    public function tokenize(string $text): \Generator
    {
        yield new PlainToken($text);
    }
}
