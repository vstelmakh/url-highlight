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

    /**@var NormalizedCollection */
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
     * @param string|null $host
     * @return bool
     */
    public function isValidUrl(?string $scheme = null, ?string $host = null): bool
    {
        if ($scheme) {
            return $this->isAllowedScheme($scheme);
        }

        if ($host && $this->matchByTLD) {
            return $this->isValidDomainHost($host);
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
    private function isValidDomainHost(string $host): bool
    {
        preg_match('/[^.]+$/', $host, $matches);
        $topLevelDomain = mb_strtolower($matches[0]);
        return isset(Domains::TOP_LEVEL_DOMAINS[$topLevelDomain]);
    }
}
