<?php

namespace VStelmakh\UrlHighlight\Tests\Validator;

use VStelmakh\UrlHighlight\Matcher\Match;
use VStelmakh\UrlHighlight\Validator\Validator;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    /**
     * @dataProvider isValidMatchDataProvider
     *
     * @param bool $matchByTLD
     * @param array|string[] $schemeBlacklist
     * @param array|string[] $schemeWhitelist
     * @param Match $match
     * @param bool $expected
     */
    public function testIsValidMatch(
        bool $matchByTLD,
        array $schemeBlacklist,
        array $schemeWhitelist,
        Match $match,
        bool $expected
    ): void {
        $validator = new Validator($matchByTLD, $schemeBlacklist, $schemeWhitelist);
        $actual = $validator->isValidMatch($match);
        self::assertEquals($expected, $actual, 'Dataset: ' . json_encode(func_get_args()));
    }

    /**
     * @return array&array[]
     */
    public function isValidMatchDataProvider(): array
    {
        return [
            [true, [], [], $this->getMatch(null, null, null, null), false],
            [true, [], [], $this->getMatch('http', null, null, null), true],
            [true, [], [], $this->getMatch(null, null, 'example.com', 'com'), true],
            [true, [], [], $this->getMatch(null, null, 'filename.txt', 'txt'), false],
            [true, [], [], $this->getMatch('http', null, 'example.com', 'com'), true],
            [true, [], [], $this->getMatch('http', null, 'filename.txt', 'txt'), true],
            [true, ['http'], [], $this->getMatch('http', null, null, null), false],
            [true, ['ftp'], [], $this->getMatch('http', null, null, null), true],
            [true, ['http'], [], $this->getMatch(null, null, 'example.com', 'com'), true],
            [true, [], ['http'], $this->getMatch('http', null, null, null), true],
            [true, [], ['ftp'], $this->getMatch('http', null, null, null), false],
            [true, [], ['http'], $this->getMatch(null, null, 'example.com', 'com'), true],
            [true, ['http'], ['http'], $this->getMatch('http', null, null, null), false],
            [true, ['ftp'], ['http'], $this->getMatch('http', null, null, null), true],
            [true, ['ftp'], ['ssh'], $this->getMatch('http', null, null, null), false],
            [true, ['ftp'], ['http'], $this->getMatch(null, null, 'example.com', 'com'), true],

            [false, [], [], $this->getMatch(null, null, null, null), false],
            [false, [], [], $this->getMatch('http', null, null, null), true],
            [false, [], [], $this->getMatch(null, null, 'example.com', 'com'), false],
            [false, [], [], $this->getMatch(null, null, 'filename.txt', 'txt'), false],
            [false, [], [], $this->getMatch('http', null, 'example.com', 'com'), true],
            [false, [], [], $this->getMatch('http', null, 'filename.txt', 'txt'), true],
            [false, ['http'], [], $this->getMatch('http', null, null, null), false],
            [false, ['ftp'], [], $this->getMatch('http', null, null, null), true],
            [false, ['http'], [], $this->getMatch(null, null, 'example.com', 'com'), false],
            [false, [], ['http'], $this->getMatch('http', null, null, null), true],
            [false, [], ['ftp'], $this->getMatch('http', null, null, null), false],
            [false, [], ['http'], $this->getMatch(null, null, 'example.com', 'com'), false],
            [false, ['http'], ['http'], $this->getMatch('http', null, null, null), false],
            [false, ['ftp'], ['http'], $this->getMatch('http', null, null, null), true],
            [false, ['ftp'], ['ssh'], $this->getMatch('http', null, null, null), false],
            [false, ['ftp'], ['http'], $this->getMatch(null, null, 'example.com', 'com'), false],

            [true, [], [], $this->getMatch(null, 'username', 'example.com', 'com'), false],
        ];
    }

    private function getMatch(?string $scheme, ?string $local, ?string $host, ?string $tld): Match
    {
        return new Match('', 0, '', $scheme, $local, $host, $tld, null, null);
    }
}
