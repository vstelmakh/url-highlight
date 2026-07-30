<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer\Tokenizer\Token;

/**
 * An HTML tag segment with its name and structural type.
 *
 * @internal
 */
final readonly class TagToken implements Token
{
    public function __construct(
        public string $contents,
        public string $name,
        public TagType $type,
    ) {}

    #[\Override]
    public function __toString(): string
    {
        return $this->contents;
    }
}
