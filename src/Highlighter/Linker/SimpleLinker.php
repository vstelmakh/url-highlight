<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter\Linker;

use VStelmakh\UrlHighlight\Matcher\UrlMatch;

final readonly class SimpleLinker implements Linker
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
