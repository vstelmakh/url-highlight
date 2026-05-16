<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Matcher;

final readonly class UrlMatch
{
    public function __construct(
        public string $match,
        public int $offset,
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
            $url = $this->scheme === null ? "mailto:{$this->match}" : $this->match;
        } elseif ($this->scheme !== null) {
            $url = $this->match;
        } else {
            $url = "{$fallbackScheme}://{$this->match}";
        }

        return htmlspecialchars($url, ENT_QUOTES | ENT_HTML5);
    }
}
