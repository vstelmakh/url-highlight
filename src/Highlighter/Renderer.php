<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter;

use VStelmakh\UrlHighlight\Highlighter\Linker\Linker;

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
     * @param iterable<RangeMatch> $matches Ranges into $source, ascending by start, non-overlapping.
     */
    public function render(string $source, iterable $matches, Linker $linker): string
    {
        $result = '';
        $cursor = 0;

        foreach ($matches as $rangeMatch) {
            $result .= substr($source, $cursor, $rangeMatch->start - $cursor);
            $result .= $linker->render($rangeMatch->match);
            $cursor = $rangeMatch->end;
        }

        return $result . substr($source, $cursor);
    }
}
