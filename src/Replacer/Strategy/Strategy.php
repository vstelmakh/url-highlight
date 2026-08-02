<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer\Strategy;

use VStelmakh\UrlHighlight\Replacer\Replacement;

/**
 * Strategy responsible for locating URLs within a single run of visible text, returning each as a {@see Replacement}
 * spanning the original source. Implementations decide how the text is interpreted (e.g. literal vs HTML-encoded),
 * this is the point of variation that lets the {@see Replacer} support new input modes without being changed.
 *
 * @internal
 */
interface Strategy
{
    /**
     * @param string $text Run of visible text from the source.
     * @param int $offset Byte offset of `$text` within the original source, used to map replacements back into it.
     * @return iterable<Replacement>
     */
    public function findReplacements(string $text, int $offset): iterable;
}
