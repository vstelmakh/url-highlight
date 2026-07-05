<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Matcher;

use VStelmakh\UrlHighlight\Url;

/**
 * A URL matched in a source string, located by its byte range [start, end] within it.
 *
 * @internal
 */
final readonly class UrlMatch
{
    public function __construct(
        public int $start,
        public int $end,
        public Url $url,
    ) {}
}
