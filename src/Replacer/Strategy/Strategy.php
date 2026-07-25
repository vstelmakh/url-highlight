<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer\Strategy;

use VStelmakh\UrlHighlight\Matcher\UrlMatch;

/**
 * Locates URLs within a single run of visible text, returning each as a range into the original source.
 * Implementations decide how the text is interpreted (e.g. literal vs HTML-encoded); this is the point of
 * variation that lets the Replacer support new input modes without being changed.
 *
 * @internal
 */
interface Strategy
{
    /**
     * @param string $text Run of visible text from the source.
     * @param int $offset Byte offset of `$text` within the original source, used to map matches back into it.
     * @return iterable<UrlMatch>
     */
    public function match(string $text, int $offset): iterable;
}
