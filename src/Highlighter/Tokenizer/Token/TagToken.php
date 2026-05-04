<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter\Tokenizer\Token;

/**
 * @internal
 */
final readonly class TagToken implements Token
{
    public function __construct(
        public string $contents,
        public string $name,
        public bool $isClosing,
        public bool $isSelfClosing,
    ) {}

    #[\Override]
    public function toString(): string
    {
        return $this->contents;
    }
}
