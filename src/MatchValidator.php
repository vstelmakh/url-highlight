<?php

namespace VStelmakh\UrlHighlight;

/**
 * Class to verify if url scheme or host fit the requirements
 *
 * @internal
 */
class MatchValidator
{
    /** @var bool */
    private $matchByTLD;

    /** @var NormalizedCollection */
    private $schemeBlacklist;

    /** @var NormalizedCollection */
    private $schemeWhitelist;

    /**
     * @param bool $matchByTLD
     * @param array|string[] $schemeBlacklist
     * @param array|string[] $schemeWhitelist
     */
    public function __construct(bool $matchByTLD, array $schemeBlacklist, array $schemeWhitelist)
    {
        $this->matchByTLD = $matchByTLD;
        $this->schemeBlacklist = new NormalizedCollection($schemeBlacklist);
        $this->schemeWhitelist = new NormalizedCollection($schemeWhitelist);
    }

    /**
     * @param string|null $scheme
     * @param string|null $local
     * @param string|null $host
     * @param string|null $tld
     * @return bool
     */
    public function isValidUrl(?string $scheme = null, ?string $local = null, ?string $host = null, ?string $tld = null): bool
    {
        if ($scheme) {
            return $this->isAllowedScheme($scheme);
        }

        if ($local) {
            return false; // TODO: email, not valid for now
        }

        if ($host && !$this->isValidHost($host)) {
            return false;
        }

        if ($tld && $this->matchByTLD) {
            return $this->isValidTopLevelDomain($tld);
        }

        return false;
    }

    /**
     * @param string $scheme
     * @return bool
     */
    private function isAllowedScheme(string $scheme): bool
    {
        $isAllowedByBlacklist = !$this->schemeBlacklist->isContains($scheme);
        $isAllowedByWhitelist = $this->schemeWhitelist->isEmpty() || $this->schemeWhitelist->isContains($scheme);
        return $isAllowedByBlacklist && $isAllowedByWhitelist;
    }

    /**
     * @param string $host
     * @return bool
     */
    private function isValidHost(string $host): bool
    {
        // TODO: implement host validation
        return true;
    }

    /**
     * @param string $topLevelDomain
     * @return bool
     */
    private function isValidTopLevelDomain(string $topLevelDomain): bool
    {
        $topLevelDomain = \mb_strtolower($topLevelDomain);
        return isset(Domains::TOP_LEVEL_DOMAINS[$topLevelDomain]);
    }
}
