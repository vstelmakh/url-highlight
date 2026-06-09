<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter;

use VStelmakh\UrlHighlight\Matcher\UrlMatch;

final readonly class SimpleHighlighter implements Highlighter
{
    #[\Override]
    public function render(UrlMatch $match): string
    {
        return sprintf(
            '<a href="%s">%s</a>',
            $match->toHref(),
            htmlspecialchars($match->match, ENT_QUOTES | ENT_HTML5),
        );
    }
}
