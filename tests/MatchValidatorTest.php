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
     * @param bool $expected
     */
    public function testIsValidUrl(
        bool $matchByTLD,
        array $schemeBlacklist,
        array $schemeWhitelist,
        ?string $scheme = null,
        ?string $host = null,
        bool $expected
    ): void {
        $matchValidator = new MatchValidator($matchByTLD, $schemeBlacklist, $schemeWhitelist);
        $actual = $matchValidator->isValidUrl($scheme, $host);
        $this->assertEquals($expected, $actual, 'Dataset: ' . json_encode(func_get_args()));
    }

    /**
     * @return array|array[]
     */
    public function isValidUrlDataProvider(): array
    {
        return [
            [true, [], [], null, null, false],
            [true, [], [], 'http', null, true],
            [true, [], [], null, 'example.com', true],
            [true, [], [], null, 'filename.txt', false],
            [true, [], [], 'http', 'example.com', true],
            [true, [], [], 'http', 'filename.txt', true],
            [true, ['http'], [], 'http', null, false],
            [true, ['ftp'], [], 'http', null, true],
            [true, ['http'], [], null, 'example.com', true],
            [true, [], ['http'], 'http', null, true],
            [true, [], ['ftp'], 'http', null, false],
            [true, [], ['http'], null, 'example.com', true],
            [true, ['http'], ['http'], 'http', null, false],
            [true, ['ftp'], ['http'], 'http', null, true],
            [true, ['ftp'], ['ssh'], 'http', null, false],
            [true, ['ftp'], ['http'], null, 'example.com', true],
            [false, [], [], null, null, false],
            [false, [], [], 'http', null, true],
            [false, [], [], null, 'example.com', false],
            [false, [], [], null, 'filename.txt', false],
            [false, [], [], 'http', 'example.com', true],
            [false, [], [], 'http', 'filename.txt', true],
            [false, ['http'], [], 'http', null, false],
            [false, ['ftp'], [], 'http', null, true],
            [false, ['http'], [], null, 'example.com', false],
            [false, [], ['http'], 'http', null, true],
            [false, [], ['ftp'], 'http', null, false],
            [false, [], ['http'], null, 'example.com', false],
            [false, ['http'], ['http'], 'http', null, false],
            [false, ['ftp'], ['http'], 'http', null, true],
            [false, ['ftp'], ['ssh'], 'http', null, false],
            [false, ['ftp'], ['http'], null, 'example.com', false],
        ];
    }
}
