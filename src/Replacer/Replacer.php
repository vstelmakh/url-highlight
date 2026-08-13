<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer;

use VStelmakh\UrlHighlight\Highlighter\Highlighter;
use VStelmakh\UrlHighlight\Replacer\Strategy\Strategy;

/**
 * Replaces every URL of the input with the output of {@see Highlighter}, leaving the surrounding text unchanged.
 * Locating the URLs is delegated to the given {@see Strategy}.
 *
 * @internal
 */
final readonly class Replacer
{
    public function replace(string $text, Highlighter $highlighter, Strategy $strategy): string
    {
        $result = '';
        $cursor = 0;

        foreach ($strategy->findReplacements($text) as $replacement) {
            $result .= substr($text, $cursor, $replacement->start - $cursor);
            $result .= $highlighter->render($replacement->url);
            $cursor = $replacement->end;
        }

        return $result . substr($text, $cursor);
    }
}
