<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Validator\DomainChecker;

interface DomainCheckerInterface
{
    /**
     * Check for validity of provided top level domain.
     *
     * @param string $tld Expect case-insensitive value (lowercase and uppercase characters)
     * @return bool
     */
    public function isValidDomain(string $tld): bool;
}
