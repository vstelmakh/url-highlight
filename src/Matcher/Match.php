<?php

namespace VStelmakh\UrlHighlight\Matcher;

/**
 * Data in this class don't represent exact url parts. Could contain null values,
 * as data filled in depends on regex match. If method return null - means match was done by some other parameter.
 * E.g. http://example.com matched via scheme and will be mapped as:
 *     fullMatch: http://example.com
 *     url: http://example.com
 *     scheme: http
 *     local: null
 *     host: null
 *     tld: null
 */
class Match
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
     * @var string
     */
    private $url;

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
     * @internal
     * @param string $fullMatch
     * @param int $byteOffset
     * @param string $url
     * @param string|null $scheme
     * @param string|null $local
     * @param string|null $host
     * @param string|null $tld
     */
    public function __construct(
        string $fullMatch,
        int $byteOffset,
        string $url,
        ?string $scheme,
        ?string $local,
        ?string $host,
        ?string $tld
    ) {
        $this->fullMatch = $fullMatch;
        $this->byteOffset = $byteOffset;
        $this->url = $url;
        $this->scheme = $this->getNotEmptyStringOrNull($scheme);
        $this->local = $this->getNotEmptyStringOrNull($local);
        $this->host = $this->getNotEmptyStringOrNull($host);
        $this->tld = $this->getNotEmptyStringOrNull($tld);
    }

    /**
     * Return match found in the text. For encoded input will contain not decoded match.
     *
     * @return string
     */
    public function getFullMatch(): string
    {
        return $this->fullMatch;
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
     * Example match: http://example.com -> http://example.com
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Example match: http://example.com -> http
     *
     * @return string|null
     */
    public function getScheme(): ?string
    {
        return $this->scheme;
    }

    /**
     * Example match: user:password@example.com -> user:password
     *
     * @return string|null
     */
    public function getLocal(): ?string
    {
        return $this->local;
    }

    /**
     * Example match: example.com -> example.com
     *
     * @return string|null
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * Example match: example.com -> com
     *
     * @return string|null
     */
    public function getTld(): ?string
    {
        return $this->tld;
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
