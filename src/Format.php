<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight;

/**
 * Format of the input, defining how it is interpreted while matching URLs.
 *
 * @api
 */
enum Format
{
    /**
     * Text without markup, taken as is. Angle brackets are ordinary characters.
     */
    case Plain;

    /**
     * HTML markup. Tags and the content of the elements that may not hold a link are left untouched.
     */
    case Html;

    /**
     * HTML markup with entity-encoded text, e.g. from `htmlspecialchars`. Matched against the decoded text.
     */
    case HtmlEncoded;
}
