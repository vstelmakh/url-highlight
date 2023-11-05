<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Validator;

use VStelmakh\UrlHighlight\Matcher\UrlMatch;
use VStelmakh\UrlHighlight\Util\CaseInsensitiveSet;
use VStelmakh\UrlHighlight\Validator\DomainChecker\DomainChecker;
use VStelmakh\UrlHighlight\Validator\DomainChecker\DomainCheckerInterface;

class Validator implements ValidatorInterface
{
    /** @var bool */
    private $hasTLDMatch;

    /** @var CaseInsensitiveSet */
    private $schemeBlacklist;

    /** @var CaseInsensitiveSet */
    private $schemeWhitelist;

    /** @var bool */
    private $hasEmailMatch;

    /** @var DomainCheckerInterface */
    private $domainChecker;

    /**
     * @param bool $hasTLDMatch Allow to use top level domain to match urls without scheme
     * @param string[] $schemeBlacklist Blacklisted schemes (not listed here are allowed)
     * @param string[] $schemeWhitelist Whitelisted schemes (only listed here are allowed)
     * @param bool $hasEmailMatch Allow to match emails (if match by TLD set to "false" - will match only "mailto" urls)
     * @param ?DomainCheckerInterface $domainChecker
     */
    public function __construct(
        bool $hasTLDMatch = true,
        array $schemeBlacklist = [],
        array $schemeWhitelist = [],
        bool $hasEmailMatch = true,
        ?DomainCheckerInterface $domainChecker = null
    ) {
        $this->hasTLDMatch = $hasTLDMatch;
        $this->schemeBlacklist = new CaseInsensitiveSet($schemeBlacklist);
        $this->schemeWhitelist = new CaseInsensitiveSet($schemeWhitelist);
        $this->hasEmailMatch = $hasEmailMatch;
        $this->domainChecker = $domainChecker ?? new DomainChecker();
    }

    /**
     * Verify if url match (scheme or host) fit config requirements
     *
     * @interal
     * @param UrlMatch $match
     * @return bool
     */
    public function isValidMatch(UrlMatch $match): bool
    {
        return $this->isEmail($match) ? $this->isValidEmail($match) : $this->isValidURL($match);
    }

    /**
     * @param UrlMatch $match
     * @return bool
     */
    private function isEmail(UrlMatch $match): bool
    {
        $scheme = $match->getScheme();
        $userinfo = $match->getUserinfo();
        return (empty($scheme) && !empty($userinfo)) || $scheme === 'mailto';
    }

    /**
     * @param UrlMatch $match
     * @return bool
     */
    private function isValidEmail(UrlMatch $match): bool
    {
        if (!$this->hasEmailMatch) {
            return false;
        }

        if ($match->getScheme() === 'mailto') {
            return true;
        }

        return $this->isValidDomain($match->getTld());
    }

    /**
     * @param UrlMatch $match
     * @return bool
     */
    private function isValidURL(UrlMatch $match): bool
    {
        $scheme = $match->getScheme();
        return empty($scheme) ? $this->isValidDomain($match->getTld()) : $this->isValidScheme($scheme);
    }

    /**
     * @param string|null $tld
     * @return bool
     */
    private function isValidDomain(?string $tld): bool
    {
        if (!empty($tld) && $this->hasTLDMatch) {
            return $this->domainChecker->isValidDomain($tld);
        }

        return false;
    }

    /**
     * @param string|null $scheme
     * @return bool
     */
    private function isValidScheme(?string $scheme): bool
    {
        if (empty($scheme)) {
            return false;
        }

        $isAllowedByBlacklist = !$this->schemeBlacklist->contains($scheme);
        $isAllowedByWhitelist = $this->schemeWhitelist->isEmpty() || $this->schemeWhitelist->contains($scheme);
        return $isAllowedByBlacklist && $isAllowedByWhitelist;
    }
}
