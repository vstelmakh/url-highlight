<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer\Strategy;

use VStelmakh\UrlHighlight\Replacer\Replacement;

/**
 * Locates the URLs of an input, returning each as a {@see Replacement} spanning the input. One implementation per
 * {@see \VStelmakh\UrlHighlight\Format}, deciding how the input is interpreted.
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
