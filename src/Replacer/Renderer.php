<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer;

use VStelmakh\UrlHighlight\Highlighter\Highlighter;
use VStelmakh\UrlHighlight\Matcher\UrlMatch;

/**
 * Splices rendered links into a source string: the bytes between matches are kept verbatim, and each
 * match's [start, end] range is replaced by the linker's output. Matches must be ordered by start
 * and non-overlapping.
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
