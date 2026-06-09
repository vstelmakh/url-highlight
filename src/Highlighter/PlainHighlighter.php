<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter;

use VStelmakh\UrlHighlight\Highlighter\Linker\Linker;
use VStelmakh\UrlHighlight\Matcher\Matcher;

/**
 * Highlights URLs in plain HTML: matches URLs in the text runs eligible for highlighting, leaving the
 * content of tags that must not be linkified (existing links, scripts, styles) untouched.
 *
 * @internal
 */
final readonly class PlainHighlighter
{
    public function __construct(
        private TextSpanExtractor $spanExtractor,
        private Matcher $matcher,
        private Renderer $renderer,
    ) {}

    public function highlight(string $html, Linker $linker): string
    {
        $matches = $this->collectMatches($html);
        return $this->renderer->render($html, $matches, $linker);
    }

    /**
     * @return list<OffsetMatch>
     */
    private function collectMatches(string $html): array
    {
        $result = [];
        foreach ($this->spanExtractor->extract($html) as $span) {
            foreach ($this->matcher->match($span->content) as $match) {
                $start = $span->offset + $match->offset;
                $end = $start + strlen($match->match);
                $result[] = new OffsetMatch($start, $end, $match);
            }
        }
        return $result;
    }
}
