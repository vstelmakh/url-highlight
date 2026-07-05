<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight;

/**
 * A URL detected in the input, exposed as parsed components.
 *
 * @api
 */
final readonly class Url
{
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
}
