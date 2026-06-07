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
     * @param list<OffsetMatch> $matches Ranges into $source, ascending by start, non-overlapping.
     */
    public function render(string $source, array $matches, Linker $linker): string
    {
        $result = '';
        $cursor = 0;

        foreach ($matches as $offsetMatch) {
            $result .= substr($source, $cursor, $offsetMatch->start - $cursor);
            $result .= $linker->render($offsetMatch->match);
            $cursor = $offsetMatch->end;
        }

        return $result . substr($source, $cursor);
    }
}
