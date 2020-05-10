<?php

namespace VStelmakh\UrlHighlight\Matcher;

/**
 * @internal
 */
class EncodedMatch implements MatchInterface
{
    /**
     * @var string
     */
    private $fullMatch;

    /**
     * @var int
     */
    private $byteOffset;

    /**
     * @var Match
     */
    private $match;

    public function __construct(string $fullMatch, int $byteOffset, Match $match)
    {
        $this->fullMatch = $fullMatch;
        $this->byteOffset = $byteOffset;
        $this->match = $match;
    }

    /**
     * @return string
     */
    public function getEncodedFullMatch(): string
    {
        return $this->fullMatch;
    }

    /**
     * @return int
     */
    public function getEncodedByteOffset(): int
    {
        return $this->byteOffset;
    }

    public function getFullMatch(): string
    {
        return $this->match->getFullMatch();
    }

    public function getScheme(): ?string
    {
        return $this->match->getScheme();
    }

    public function getLocal(): ?string
    {
        return $this->match->getLocal();
    }

    public function getHost(): ?string
    {
        return $this->match->getHost();
    }

    public function getTld(): ?string
    {
        return $this->match->getTld();
    }

    public function getByteOffset(): ?int
    {
        return $this->match->getByteOffset();
    }
}
