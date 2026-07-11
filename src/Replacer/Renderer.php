<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer;

use VStelmakh\UrlHighlight\Highlighter\Highlighter;
use VStelmakh\UrlHighlight\Matcher\UrlMatch;

/**
 * Replaces each URL match in a source string with the output of {@see Highlighter}, leaving the surrounding text
 * unchanged.
 *
 * @internal
 */
final readonly class Renderer
{
    /**
     * @param iterable<UrlMatch> $matches Ranges into $source, ascending by start, non-overlapping.
     */
    public function render(string $source, iterable $matches, Highlighter $highlighter): string
    {
        $result = '';
        $cursor = 0;

        foreach ($matches as $match) {
            $result .= substr($source, $cursor, $match->start - $cursor);
            $result .= $highlighter->render($match->url);
            $cursor = $match->end;
        }

        return $result . substr($source, $cursor);
    }
}
