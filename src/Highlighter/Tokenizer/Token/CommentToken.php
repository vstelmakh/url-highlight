<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter\Tokenizer\Token;

/**
 * @internal
 */
final readonly class CommentToken implements Token
{
    public function __construct(
        public string $contents,
    ) {}

    #[\Override]
    public function toString(): string
    {
        return $this->contents;
    }
}
