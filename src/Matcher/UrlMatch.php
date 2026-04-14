<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Matcher;

/**
 * @internal
 */
final readonly class UrlMatch
{
    public function __construct(
        public string $match,
        public int $offset,
        public ?string $scheme,
        public ?string $userinfo,
        public string $host,
        public ?string $tld,
        public ?string $port,
        public ?string $path,
        public ?string $query,
        public ?string $fragment,
    ) {}
}
