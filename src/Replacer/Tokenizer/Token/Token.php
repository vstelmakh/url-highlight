<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer\Tokenizer\Token;

/**
 * A segment of tokenized HTML input, identified by its concrete type.
 *
 * @internal
 */
interface Token
{
    public function toString(): string;
}
