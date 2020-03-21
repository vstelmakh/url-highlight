<?php

namespace VStelmakh\UrlHighlight\Tests;

use VStelmakh\UrlHighlight\MatchValidator;
use PHPUnit\Framework\TestCase;

class MatchValidatorTest extends TestCase
{
    /**
     * @dataProvider isValidUrlDataProvider
     * @param bool $matchByTLD
     * @param array|string[] $schemeBlacklist
     * @param array|string[] $schemeWhitelist
     * @param string|null $scheme
     * @param string|null $host
     * @param string|null $tld
     * @param bool $expected
     */
    public function testIsValidUrl(
        bool $matchByTLD,
        array $schemeBlacklist,
        array $schemeWhitelist,
        ?string $scheme = null,
        ?string $host = null,
        ?string $tld = null,
        bool $expected
    ): void {
        $matchValidator = new MatchValidator($matchByTLD, $schemeBlacklist, $schemeWhitelist);
        $actual = $matchValidator->isValidUrl($scheme, $host, $tld);
        $this->assertEquals($expected, $actual, 'Dataset: ' . json_encode(func_get_args()));
    }

    /**
     * @return array|array[]
     */
    public function isValidUrlDataProvider(): array
    {
        return [
            [true, [], [], null, null, null, false],
            [true, [], [], 'http', null, null, true],
            [true, [], [], null, 'example.com', 'com', true],
            [true, [], [], null, 'filename.txt', 'txt', false],
            [true, [], [], 'http', 'example.com', 'com', true],
            [true, [], [], 'http', 'filename.txt', 'txt', true],
            [true, ['http'], [], 'http', null, null, false],
            [true, ['ftp'], [], 'http', null, null, true],
            [true, ['http'], [], null, 'example.com', 'com', true],
            [true, [], ['http'], 'http', null, null, true],
            [true, [], ['ftp'], 'http', null, null, false],
            [true, [], ['http'], null, 'example.com', 'com', true],
            [true, ['http'], ['http'], 'http', null, null, false],
            [true, ['ftp'], ['http'], 'http', null, null, true],
            [true, ['ftp'], ['ssh'], 'http', null, null, false],
            [true, ['ftp'], ['http'], null, 'example.com', 'com', true],
            [false, [], [], null, null, null, false],
            [false, [], [], 'http', null, null, true],
            [false, [], [], null, 'example.com', 'com', false],
            [false, [], [], null, 'filename.txt', 'txt', false],
            [false, [], [], 'http', 'example.com', 'com', true],
            [false, [], [], 'http', 'filename.txt', 'txt', true],
            [false, ['http'], [], 'http', null, null, false],
            [false, ['ftp'], [], 'http', null, null, true],
            [false, ['http'], [], null, 'example.com', 'com', false],
            [false, [], ['http'], 'http', null, null, true],
            [false, [], ['ftp'], 'http', null, null, false],
            [false, [], ['http'], null, 'example.com', 'com', false],
            [false, ['http'], ['http'], 'http', null, null, false],
            [false, ['ftp'], ['http'], 'http', null, null, true],
            [false, ['ftp'], ['ssh'], 'http', null, null, false],
            [false, ['ftp'], ['http'], null, 'example.com', 'com', false],
        ];
    }
}
