<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Matcher;

use VStelmakh\UrlHighlight\Matcher\Domains\TopLevelDomains;
use VStelmakh\UrlHighlight\Url;

/**
 * URLs with a scheme are always valid. For scheme-less URLs, the host must have a recognized TLD, or be an IP address
 * followed by a port or a path.
 *
 * @internal
 */
final readonly class HostValidator
{
    public function isValid(Url $url): bool
    {
        if ($this->hasScheme($url)) {
            return true;
        }

        if ($this->isValidIpHost($url)) {
            return true;
        }

        return $this->hasValidTopLevelDomain($url);
    }

    private function hasScheme(Url $url): bool
    {
        return $url->scheme !== null;
    }

    private function isValidIpHost(Url $url): bool
    {
        // A bare IP is indistinguishable from strings like a version number, therefore, valid only with path or port.
        if ($url->port === null && $url->path === null) {
            return false;
        }

        return filter_var($url->host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }

    private function hasValidTopLevelDomain(Url $url): bool
    {
        $last = strrchr($url->host, '.');
        $tld = $last !== false ? substr($last, 1) : null;

        if ($tld === null) {
            return false;
        }

        return TopLevelDomains::contains($tld);
    }
}
