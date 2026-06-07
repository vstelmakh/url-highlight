<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter\Replacer;

use VStelmakh\UrlHighlight\Matcher\UrlMatch;

/**
 * A URL match together with its start byte offset within the decoded string.
 *
 * @internal
 */
final readonly class OffsetMatch
{
    public function __construct(
        public int $offset,
        public UrlMatch $match,
    ) {}
}
