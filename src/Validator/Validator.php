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
    private $isNoSchemeAllowed;

    /** @var CaseInsensitiveSet */
    private $schemeBlacklist;

    /** @var CaseInsensitiveSet */
    private $schemeWhitelist;

    /** @var bool */
    private $isEmailAllowed;

    /** @var DomainCheckerInterface */
    private $domainChecker;

    /**
     * @param bool $isNoSchemeAllowed Allow to use top level domain to match urls without scheme
     * @param string[] $schemeBlacklist Blacklisted schemes (not listed here are allowed)
     * @param string[] $schemeWhitelist Whitelisted schemes (only listed here are allowed)
     * @param bool $isEmailAllowed Allow to match emails (if match by TLD set to "false" - will match only "mailto" urls)
     * @param ?DomainCheckerInterface $domainChecker Custom top level domain checker implementation
     */
    public function __construct(
        bool $isNoSchemeAllowed = true,
        array $schemeBlacklist = [],
        array $schemeWhitelist = [],
        bool $isEmailAllowed = true,
        ?DomainCheckerInterface $domainChecker = null
    ) {
        $this->isNoSchemeAllowed = $isNoSchemeAllowed;
        $this->schemeBlacklist = new CaseInsensitiveSet($schemeBlacklist);
        $this->schemeWhitelist = new CaseInsensitiveSet($schemeWhitelist);
        $this->isEmailAllowed = $isEmailAllowed;
        $this->domainChecker = $domainChecker ?? new DomainChecker();
    }

    /**
     * Verify if url match satisfy requirements
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
        if (!$this->isEmailAllowed) {
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
        if (!empty($tld) && $this->isNoSchemeAllowed) {
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
        // @codeCoverageIgnoreStart
        if (empty($scheme)) {
            return false;
        }
        // @codeCoverageIgnoreEnd

        $isAllowedByBlacklist = !$this->schemeBlacklist->contains($scheme);
        $isAllowedByWhitelist = $this->schemeWhitelist->isEmpty() || $this->schemeWhitelist->contains($scheme);
        return $isAllowedByBlacklist && $isAllowedByWhitelist;
    }
}
