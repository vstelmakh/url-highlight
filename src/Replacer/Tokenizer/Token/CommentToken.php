<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer\Tokenizer\Token;

/**
 * An HTML comment segment (`<!-- ... -->`).
 *
 * @internal
 */
final readonly class CommentToken implements Token
{
    public function __construct(
        public string $contents,
    ) {}

    #[\Override]
    public function __toString(): string
    {
        return $this->contents;
    }
}
