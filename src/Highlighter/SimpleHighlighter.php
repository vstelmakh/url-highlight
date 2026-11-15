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
        return Anchor::fromUrl($url)->render();
    }
}
