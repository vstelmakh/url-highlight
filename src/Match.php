<?php

namespace VStelmakh\UrlHighlight;

class Match
{
    /**
     * @var string
     */
    private $fullMatch;

    /**
     * @var string|null
     */
    private $scheme;

    /**
     * @var string|null
     */
    private $local;

    /**
     * @var string|null
     */
    private $host;

    /**
     * @var string|null
     */
    private $tld;

    public function __construct(string $fullMatch, ?string $scheme, ?string $local, ?string $host, ?string $tld)
    {
        $this->fullMatch = $fullMatch;
        $this->scheme = $scheme;
        $this->local = $local;
        $this->host = $host;
        $this->tld = $tld;
    }

    /**
     * @return string
     */
    public function getFullMatch(): string
    {
        return $this->fullMatch;
    }

    /**
     * @return string|null
     */
    public function getScheme(): ?string
    {
        return $this->scheme;
    }

    /**
     * @return string|null
     */
    public function getLocal(): ?string
    {
        return $this->local;
    }
    /**
     * @return string|null
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * @return string|null
     */
    public function getTld(): ?string
    {
        return $this->tld;
    }
}
