<?php

namespace VStelmakh\UrlHighlight;

/**
 * @internal
 */
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

    /**
     * @var int
     */
    private $byteOffset;

    public function __construct(
        string $fullMatch,
        ?string $scheme,
        ?string $local,
        ?string $host,
        ?string $tld,
        int $byteOffset
    ) {
        $this->fullMatch = $fullMatch;
        $this->scheme = $this->getNotEmptyStringOrNull($scheme);
        $this->local = $this->getNotEmptyStringOrNull($local);
        $this->host = $this->getNotEmptyStringOrNull($host);
        $this->tld = $this->getNotEmptyStringOrNull($tld);
        $this->byteOffset = $byteOffset;
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

    /**
     * preg_* functions with flag PREG_OFFSET_CAPTURE return offset in bytes.
     * Keep this in mind working with multi byte encodings.
     *
     * @return int
     */
    public function getByteOffset(): int
    {
        return $this->byteOffset;
    }

    /**
     * @param string|null $string
     * @return string|null
     */
    private function getNotEmptyStringOrNull(?string $string): ?string
    {
        return ($string !== null && $string !== '') ? $string : null;
    }
}
