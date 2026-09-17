<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer;

use VStelmakh\UrlHighlight\Url;

/**
 * An instruction to replace the byte range [start, end] of the source string with the rendered form of the URL
 * found there.
 *
 * The range is given rather than derived from the URL, because the source may hold the URL in a different form. For
 * example HTML-encoded input is matched against its decoded copy, so a `&amp;` occupying five bytes of the source
 * stands for a single byte of the URL, and the two lengths part ways.
 *
 * @internal
 */
final readonly class Replacement
{
    public function __construct(
        public int $start,
        public int $end,
        public Url $url,
    ) {}
}
