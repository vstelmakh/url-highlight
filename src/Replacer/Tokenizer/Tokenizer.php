<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer\Tokenizer;

use VStelmakh\UrlHighlight\Replacer\Tokenizer\Token\Token;

/**
 * Splits a string into a sequence of {@see Token}. Implementations decide how much of the input is markup, this is the
 * point of variation that lets the {@see \VStelmakh\UrlHighlight\Replacer\Replacer} support new input formats without
 * being changed.
 *
 * @internal
 */
interface Tokenizer
{
    /**
     * @return iterable<Token>
     */
    public function tokenize(string $text): iterable;
}
