<?php

namespace VStelmakh\UrlHighlight\Matcher;

/**
 * Data in this class don't represent exact url parts. Could contain null values,
 * as data filled in depends on regex match. If method return null - means match was done by some other parameter.
 * E.g. http://example.com matched via scheme and will be mapped as:
 *     fullMatch: http://example.com
 *     scheme: http
 *     local: null
 *     host: null
 *     tld: null
 *
 * @internal
 */
interface MatchInterface
{
    /**
     * Example match: http://example.com -> http://example.com
     *
     * @return string
     */
    public function getFullMatch(): string;

    /**
     * Example match: http://example.com -> http
     *
     * @return string|null
     */
    public function getScheme(): ?string;

    /**
     * Example match: user:password@example.com -> user:password
     *
     * @return string|null
     */
    public function getLocal(): ?string;

    /**
     * Example match: example.com -> example.com
     *
     * @return string|null
     */
    public function getHost(): ?string;

    /**
     * Example match: example.com -> com
     *
     * @return string|null
     */
    public function getTld(): ?string;

    /**
     * preg_* functions with flag PREG_OFFSET_CAPTURE return offset in bytes.
     * Keep this in mind working with multi byte encodings.
     *
     * @return int|null
     */
    public function getByteOffset(): ?int;
}
