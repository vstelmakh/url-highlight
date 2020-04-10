<?php

namespace VStelmakh\UrlHighlight;

/**
 * @internal
 */
class MatchValidator
{
    /**
     * @var bool
     */
    private $matchByTLD;

    /**
     * @var NormalizedCollection
     */
    private $schemeBlacklist;

    /**
     * @var NormalizedCollection
     */
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
     * Verify if url match (scheme or host) fit config requirements
     *
     * @param array $match
     * @return bool
     */
    public function isValidMatch(array $match): bool
    {
        $scheme = $match['scheme'] ?? null;
        if ($scheme) {
            return $this->isAllowedScheme($scheme);
        }

        $local = $match['local'] ?? null;
        if ($local) {
            return false; // TODO: email, not valid for now
        }

        $tld = $match['tld'] ?? null;
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
     * @param string $topLevelDomain
     * @return bool
     */
    private function isValidTopLevelDomain(string $topLevelDomain): bool
    {
        $topLevelDomain = \mb_strtolower($topLevelDomain);
        return isset(Domains::TOP_LEVEL_DOMAINS[$topLevelDomain]);
    }
}
