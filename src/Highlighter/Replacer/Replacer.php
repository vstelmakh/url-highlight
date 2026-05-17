<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter\Replacer;

use VStelmakh\UrlHighlight\Highlighter\Linker\Linker;

/**
 * Replaces URLs found within a plain-text segment with rendered links.
 *
 * @internal
 */
interface Replacer
{
    public function replace(string $text, Linker $linker): string;
}
