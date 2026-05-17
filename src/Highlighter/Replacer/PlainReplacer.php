<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter\Replacer;

use VStelmakh\UrlHighlight\Highlighter\Linker\Linker;
use VStelmakh\UrlHighlight\Matcher\Matcher;

/**
 * Replaces URLs found by direct matching against the input text.
 *
 * @internal
 */
final readonly class PlainReplacer implements Replacer
{
    public function __construct(
        private Matcher $matcher,
    ) {}

    #[\Override]
    public function replace(string $text, Linker $linker): string
    {
        $offset = 0;
        foreach ($this->matcher->match($text) as $match) {
            $replacement = $linker->render($match);
            $text = substr_replace($text, $replacement, $match->offset + $offset, strlen($match->match));
            $offset += strlen($replacement) - strlen($match->match);
        }
        return $text;
    }
}
