<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests\Matcher;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Matcher\HostValidator;
use VStelmakh\UrlHighlight\Url;

class HostValidatorTest extends TestCase
{
    private HostValidator $hostValidator;

    #[\Override]
    protected function setUp(): void
    {
        $this->hostValidator = new HostValidator();
    }

    #[DataProvider('isValidDataProvider')]
    public function testIsValid(Url $url, bool $expected): void
    {
        $actual = $this->hostValidator->isValid($url);
        self::assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{Url, bool}>
     */
    public static function isValidDataProvider(): array
    {
        return [
            'scheme: known tld' => [self::url('example.com', scheme: 'http'), true],
            'scheme: unknown tld' => [self::url('example.invalidtld', scheme: 'http'), true],
            'scheme: single label' => [self::url('localhost', scheme: 'http'), true],
            'scheme: ip address' => [self::url('127.0.0.1', scheme: 'http'), true],
            'scheme: mailto' => [self::url('example.invalidtld', scheme: 'mailto'), true],

            'tld: known' => [self::url('example.com'), true],
            'tld: uppercase' => [self::url('EXAMPLE.COM'), true],
            'tld: mixed case' => [self::url('Example.Com'), true],
            'tld: subdomains' => [self::url('sub.domain.example.com'), true],
            'tld: unicode' => [self::url('україна.укр'), true],
            'tld: unicode uppercase' => [self::url('УКРАЇНА.УКР'), true],
            'tld: chinese' => [self::url('互联网.ch'), true],

            'tld: unknown' => [self::url('example.invalidtld'), false],
            'tld: file name' => [self::url('filename.txt'), false],
            'tld: version number' => [self::url('1.2.3'), false],
            'tld: punycode is not in the list' => [self::url('xn--80aikifvh.xn--j1amh'), false],

            'ip: address with port' => [self::url('127.0.0.1', port: 8000), true],
            'ip: address with path' => [self::url('127.0.0.1', path: '/health'), true],
            'ip: all zeros with port' => [self::url('0.0.0.0', port: 80), true],
            'ip: maximum octets with port' => [self::url('255.255.255.255', port: 80), true],
            'ip: address alone' => [self::url('127.0.0.1'), false],
            'ip: four part version number' => [self::url('1.2.3.4'), false],
            'ip: address with query only' => [self::url('127.0.0.1', query: '?a=1'), false],
            'ip: address with fragment only' => [self::url('127.0.0.1', fragment: '#top'), false],
            'ip: octet out of range with port' => [self::url('999.1.1.1', port: 80), false],
            'ip: octet with leading zeros with port' => [self::url('010.1.1.1', port: 80), false],
            'ip: five octets with port' => [self::url('1.2.3.4.5', port: 80), false],
            'ip: version 6 is not supported' => [self::url('::1', port: 80), false],

            'host: single label' => [self::url('localhost'), false],
            'host: single label with userinfo' => [self::url('localhost', userinfo: 'user'), false],
            'host: trailing dot' => [self::url('example.com.'), false],
            'host: dot only' => [self::url('.'), false],
            'host: empty' => [self::url(''), false],
        ];
    }

    private static function url(
        string $host,
        ?string $scheme = null,
        ?string $userinfo = null,
        ?int $port = null,
        ?string $path = null,
        ?string $query = null,
        ?string $fragment = null,
    ): Url {
        return new Url($host, $scheme, $userinfo, $host, $port, $path, $query, $fragment);
    }
}
