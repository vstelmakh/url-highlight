<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Url;

class UrlTest extends TestCase
{
    #[DataProvider('isEmailDataProvider')]
    public function testIsEmail(Url $url, bool $expected): void
    {
        $actual = $url->isEmail();
        self::assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{Url, bool}>
     */
    public static function isEmailDataProvider(): array
    {
        return [
            'userinfo without scheme' => [self::url('user@example.com', userinfo: 'user'), true],
            'userinfo with mailto scheme' => [
                self::url('mailto:user@example.com', scheme: 'mailto', userinfo: 'user'),
                true,
            ],

            'userinfo with http scheme' => [
                self::url('http://user@example.com', scheme: 'http', userinfo: 'user'),
                false,
            ],
            'no userinfo' => [self::url('example.com'), false],
            'no userinfo with mailto scheme' => [self::url('mailto:example.com', scheme: 'mailto'), false],
            'userinfo with port' => [self::url('user@example.com:25', userinfo: 'user', port: 25), false],
            'userinfo with path' => [self::url('user@example.com/path', userinfo: 'user', path: '/path'), false],
            'userinfo with query' => [self::url('user@example.com?a=1', userinfo: 'user', query: '?a=1'), false],
            'userinfo with fragment' => [self::url('user@example.com#top', userinfo: 'user', fragment: '#top'), false],
        ];
    }

    #[DataProvider('toHrefDataProvider')]
    public function testToHref(Url $url, string $expected): void
    {
        $actual = $url->toHref();
        self::assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{Url, string}>
     */
    public static function toHrefDataProvider(): array
    {
        return [
            'scheme is kept' => [
                self::url('https://example.com/path', scheme: 'https', path: '/path'),
                'https://example.com/path',
            ],
            'no scheme gets default fallback' => [self::url('example.com'), 'http://example.com'],
            'email without scheme gets mailto' => [
                self::url('user@example.com', userinfo: 'user'),
                'mailto:user@example.com',
            ],
            'email with mailto scheme is kept' => [
                self::url('mailto:user@example.com', scheme: 'mailto', userinfo: 'user'),
                'mailto:user@example.com',
            ],
            'userinfo with path is not email' => [
                self::url('user@example.com/path', userinfo: 'user', path: '/path'),
                'http://user@example.com/path',
            ],
        ];
    }

    #[DataProvider('toHrefWithFallbackSchemeDataProvider')]
    public function testToHrefWithFallbackScheme(Url $url, string $fallbackScheme, string $expected): void
    {
        $actual = $url->toHref($fallbackScheme);
        self::assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{Url, string, string}>
     */
    public static function toHrefWithFallbackSchemeDataProvider(): array
    {
        return [
            'no scheme gets given fallback' => [self::url('example.com'), 'https', 'https://example.com'],
            'own scheme wins over fallback' => [
                self::url('http://example.com', scheme: 'http'),
                'https',
                'http://example.com',
            ],
            'email gets mailto instead of fallback' => [
                self::url('user@example.com', userinfo: 'user'),
                'https',
                'mailto:user@example.com',
            ],
        ];
    }

    public function testToStringReturnsFullUrl(): void
    {
        $url = self::url(
            'https://user@example.com:8080/path?a=1#top',
            scheme: 'https',
            userinfo: 'user',
            host: 'example.com',
            port: 8080,
            path: '/path',
            query: '?a=1',
            fragment: '#top',
        );

        $actual = (string) $url;

        self::assertSame('https://user@example.com:8080/path?a=1#top', $actual);
    }

    private static function url(
        string $full,
        ?string $scheme = null,
        ?string $userinfo = null,
        string $host = 'example.com',
        ?int $port = null,
        ?string $path = null,
        ?string $query = null,
        ?string $fragment = null,
    ): Url {
        return new Url($full, $scheme, $userinfo, $host, $port, $path, $query, $fragment);
    }
}
