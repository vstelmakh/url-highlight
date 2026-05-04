<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter\Linker;

use VStelmakh\UrlHighlight\Matcher\UrlMatch;

final readonly class SimpleLinker implements Linker
{
    #[\Override]
    public function render(UrlMatch $match): string
    {
        if ($match->isEmail()) {
            $href = $match->scheme === 'mailto' ? $match->match : 'mailto:' . $match->match;
        } elseif ($match->scheme !== null) {
            $href = $match->match;
        } else {
            $href = 'http://' . $match->match;
        }

        return sprintf(
            '<a href="%s">%s</a>',
            htmlspecialchars($href, ENT_QUOTES | ENT_HTML5),
            htmlspecialchars($match->match, ENT_QUOTES | ENT_HTML5),
        );
    }
}
