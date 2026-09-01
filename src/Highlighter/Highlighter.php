<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter;

use VStelmakh\UrlHighlight\Url;

/**
 * Renders a detected URL into its highlighted form. Implement to control how URLs appear in the output, for example
 * wrap in an anchor tag, add a CSS class, or emit any custom markup.
 *
 * @see SimpleHighlighter A ready-to-use implementation that wraps each URL in an anchor tag.
 *
 * @api
 */
interface Highlighter
{
    /**
     * Called once per detected URL. The returned string replaces the original URL text directly in the output.
     *
     * @param Url $url Detected URL with parsed components.
     *
     * @return string Replacement string written directly into the output.
     */
    public function render(Url $url): string;
}
