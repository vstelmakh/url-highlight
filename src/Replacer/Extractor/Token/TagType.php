<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer\Extractor\Token;

/**
 * Structural role of an HTML tag, determining how it affects nesting depth.
 *
 * @internal
 */
enum TagType
{
    case Opening;
    case Closing;
    case SelfClosing;
}
