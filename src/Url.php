<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight;

/**
 * A URL detected in the input, exposed as parsed components.
 *
 * @api
 */
final readonly class Url implements \Stringable
{
    /**
     * @param string $full The URL exactly as it appears in the input (e.g. `http://example.com/path`).
     * @param string|null $scheme Scheme without `://`, e.g. `http`, `https`, `mailto`. `null` if absent.
     * @param string|null $userinfo Credentials before `@`, e.g. `user` in `user@example.com`. `null` if absent.
     * @param string $host Host name, e.g. `example.com`.
     * @param int|null $port Port number. `null` if absent.
     * @param string|null $path Path including the leading `/`. `null` if absent.
     * @param string|null $query Query including the leading `?`. `null` if absent.
     * @param string|null $fragment Fragment including the leading `#`. `null` if absent.
     */
    public function __construct(
        public string $full,
        public ?string $scheme,
        public ?string $userinfo,
        public string $host,
        public ?int $port,
        public ?string $path,
        public ?string $query,
        public ?string $fragment,
    ) {}

    /**
     * Build a value for an `href` attribute pointing to this URL.
     *
     * @param string $fallbackScheme Scheme prepended when the URL has none.
     *                               Example: `example.com` -> `http://example.com`.
     */
    public function toHref(string $fallbackScheme = 'http'): string
    {
        if ($this->isEmail()) {
            return $this->scheme === null ? "mailto:{$this->full}" : $this->full;
        }

        if ($this->scheme !== null) {
            return $this->full;
        }

        return "{$fallbackScheme}://{$this->full}";
    }

    /**
     * Whether this URL is an email address (with or without the `mailto:` scheme).
     */
    public function isEmail(): bool
    {
        if (!in_array($this->scheme, [null, 'mailto'], true)) {
            return false;
        }

        if ($this->userinfo === null) {
            return false;
        }

        if ($this->port !== null || $this->path !== null || $this->query !== null || $this->fragment !== null) {
            return false;
        }

        return true;
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->full;
    }
}
