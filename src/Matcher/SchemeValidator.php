<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Matcher;

use VStelmakh\UrlHighlight\Url;

/**
 * Rejects URLs with an unsafe scheme. Such a scheme holds code or a document inline instead of pointing to a resource,
 * so a link with it may run a script when opened, e.g. `javascript://example.com/%0aalert(1)`.
 *
 * @internal
 */
final readonly class SchemeValidator
{
    private const array UNSAFE_SCHEMES = [
        'javascript' => true, // runs as a script
        'vbscript' => true,   // runs as a script in legacy browsers
        'data' => true,       // opens an inline document, which may hold a script
    ];

    public function isValid(Url $url): bool
    {
        if ($url->scheme === null) {
            return true;
        }

        $scheme = strtolower($url->scheme);
        return !isset(self::UNSAFE_SCHEMES[$scheme]);
    }
}
