<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter;

use VStelmakh\UrlHighlight\Url;

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
