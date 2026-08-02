<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Matcher;

use VStelmakh\UrlHighlight\Url;

/**
 * A URL matched in a string, located by its byte range [start, end] within that same string.
 *
 * @internal
 */
final readonly class UrlMatch
{
    public int $end;

    public function __construct(
        public int $start,
        public Url $url,
    ) {
        $this->end = $start + strlen($url->full);
    }
}
