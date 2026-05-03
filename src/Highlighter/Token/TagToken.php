<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter\Token;

/**
 * @internal
 */
final readonly class TagToken extends AbstractToken
{
    public function __construct(
        string $contents,
        public string $name,
        public bool $isClosing,
        public bool $isSelfClosing,
    ) {
        parent::__construct($contents);
    }

    /**
     * Returns true if the tag content should not be highlighted (e.g. already a link, or non-visible content).
     */
    public function shouldSkip(): bool
    {
        return isset(['a' => true, 'script' => true, 'style' => true][$this->name]);
    }
}
