<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Matcher\Domains;

/**
 * @internal
 */
final class TopLevelDomains
{
    /** @var array<string, true>|null */
    private static ?array $domains = null;

    public static function contains(string $domain): bool
    {
        self::$domains ??= self::load();
        $normalized = mb_strtolower($domain);
        return isset(self::$domains[$normalized]);
    }

    /**
     * @return array<string, true>
     */
    private static function load(): array
    {
        return require __DIR__ . '/tld_map.php';
    }
}
