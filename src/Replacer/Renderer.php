<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer;

use VStelmakh\UrlHighlight\Highlighter\Highlighter;

/**
 * Replaces each URL match in a source string with the output of {@see Highlighter}, leaving the surrounding text
 * unchanged.
 *
 * @internal
 */
final readonly class Renderer
{
    /**
     * @param iterable<Replacement> $replacements
     */
    public function render(string $source, iterable $replacements, Highlighter $highlighter): string
    {
        $result = '';
        $cursor = 0;

        foreach ($replacements as $replacement) {
            $result .= substr($source, $cursor, $replacement->start - $cursor);
            $result .= $highlighter->render($replacement->url);
            $cursor = $replacement->end;
        }

        return $result . substr($source, $cursor);
    }
}
