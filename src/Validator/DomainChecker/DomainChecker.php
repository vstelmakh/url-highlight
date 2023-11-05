<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Validator\DomainChecker;

use VStelmakh\UrlHighlight\Domains;

/**
 * @internal
 */
class DomainChecker implements DomainCheckerInterface
{
    /**
     * @inheritdoc
     */
    public function isValidDomain(string $tld): bool
    {
        $tld = \mb_strtolower($tld);
        return isset(Domains::TOP_LEVEL_DOMAINS[$tld]);
    }
}
