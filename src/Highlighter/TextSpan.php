<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter;

/**
 * A run of non-tag text from the source, eligible for URL highlighting, with its byte offset in the source.
 *
 * @internal
 */
final readonly class TextSpan
{
    public function __construct(
        public string $content,
        public int $offset,
    ) {}
}
