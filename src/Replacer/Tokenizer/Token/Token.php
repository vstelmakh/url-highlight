<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer\Tokenizer\Token;

/**
 * @internal
 */
interface Token
{
    public function toString(): string;
}
