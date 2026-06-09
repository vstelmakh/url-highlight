<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter;

use VStelmakh\UrlHighlight\Matcher\UrlMatch;

interface Highlighter
{
    public function render(UrlMatch $match): string;
}
