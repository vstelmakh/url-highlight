<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight;

/**
 * Encoding of the input, defining how it is interpreted while matching URLs.
 *
 * @api
 */
enum Format
{
    /**
     * Plain text or raw HTML, taken as is.
     */
    case Plain;

    /**
     * HTML entity-encoded input, e.g. from `htmlspecialchars`. Matched against the decoded text.
     */
    case HtmlEncoded;
}
