<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter\Linker;

use VStelmakh\UrlHighlight\Matcher\UrlMatch;

interface LinkerInterface
{
    public function link(UrlMatch $match): string;
}
