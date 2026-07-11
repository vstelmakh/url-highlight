<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer\Tokenizer\Token;

/**
 * An HTML tag segment with its name and structural flags.
 *
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
