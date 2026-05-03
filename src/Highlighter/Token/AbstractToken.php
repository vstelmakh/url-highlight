<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter\Token;

/**
 * @internal
 */
abstract readonly class AbstractToken
{
    public function __construct(
        public string $contents,
    ) {}
}
