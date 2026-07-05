<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Matcher;

use VStelmakh\UrlHighlight\Domains;
use VStelmakh\UrlHighlight\Url;

/**
 * @internal
 */
final readonly class HostValidator
{
    public function isValid(Url $url): bool
    {
        if ($url->scheme !== null) {
            return true;
        }

        $last = strrchr($url->host, '.');
        $tld = $last !== false ? substr($last, 1) : null;

        if ($tld === null) {
            return false;
        }

        $normalized = mb_strtolower($tld);
        return isset(Domains::TOP_LEVEL_DOMAINS[$normalized]);
    }
}
