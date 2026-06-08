<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter;

use VStelmakh\UrlHighlight\Matcher\UrlMatch;

/**
 * A URL match located by its byte range [start, end] within the string it will be rendered into.
 *
 * @internal
 */
final readonly class OffsetMatch
{
    public function __construct(
        public int $start,
        public int $end,
        public UrlMatch $match,
    ) {}
}
