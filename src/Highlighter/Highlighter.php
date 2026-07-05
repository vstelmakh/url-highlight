<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter;

use VStelmakh\UrlHighlight\Url;

/**
 * @api
 */
interface Highlighter
{
    public function render(Url $url): string;
}
