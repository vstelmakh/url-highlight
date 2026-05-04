<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter\Tokenizer\Token;

/**
 * @internal
 */
interface Token
{
    public function toString(): string;
}
