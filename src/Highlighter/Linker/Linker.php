<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter\Linker;

use VStelmakh\UrlHighlight\Matcher\UrlMatch;

interface Linker
{
    public function render(UrlMatch $match): string;
}
