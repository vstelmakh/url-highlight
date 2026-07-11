<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer\Strategy;

use VStelmakh\UrlHighlight\Matcher\UrlMatch;

/**
 * Strategy for locating URLs within a single visible text run, returning each as a range into the
 * original source. Implementations define how a run is interpreted (e.g. literal text vs HTML-encoded),
 * which is the point of variation the Highlighter is open to without modification.
 *
 * @internal
 */
interface Strategy
{
    /**
     * @param string $span Run of visible text from the source.
     * @param int $offset Byte offset of `$span` within the original source, used to map matches back into it.
     * @return iterable<UrlMatch>
     */
    public function match(string $span, int $offset): iterable;
}
