<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter;

use VStelmakh\UrlHighlight\Url;

/**
 * Wraps each detected URL in `<a href="{href}">{text}</a>`, using the full URL as both href and link text.
 *
 * @api
 */
final readonly class SimpleHighlighter implements Highlighter
{
    #[\Override]
    public function render(Url $url): string
    {
        return sprintf(
            '<a href="%s">%s</a>',
            htmlspecialchars($url->toHref(), ENT_QUOTES | ENT_HTML5),
            htmlspecialchars($url->full, ENT_QUOTES | ENT_HTML5),
        );
    }
}
