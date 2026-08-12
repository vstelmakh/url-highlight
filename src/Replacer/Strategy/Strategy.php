<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer\Strategy;

use VStelmakh\UrlHighlight\Replacer\Replacement;

/**
 * Locates the URLs of an input, returning each as a {@see Replacement} spanning the input. Implementations decide how
 * the input is interpreted, one per supported format, this is the point of variation that lets the
 * {@see \VStelmakh\UrlHighlight\Replacer\Replacer} support new formats without being changed.
 *
 * @internal
 */
interface Strategy
{
    /**
     * @return iterable<Replacement>
     */
    public function findReplacements(string $text): iterable;
}
