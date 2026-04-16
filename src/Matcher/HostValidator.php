<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Matcher;

use VStelmakh\UrlHighlight\Domains;

/**
 * @internal
 */
final readonly class HostValidator
{
    public function isValid(UrlMatch $match): bool
    {
        if ($match->scheme !== null) {
            return true;
        }

        $last = strrchr($match->host, '.');
        $tld = $last !== false ? substr($last, 1) : null;

        if ($tld === null) {
            return false;
        }

        $normalized = mb_strtolower($tld);
        return isset(Domains::TOP_LEVEL_DOMAINS[$normalized]);
    }
}
