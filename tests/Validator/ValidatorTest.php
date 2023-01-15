<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests\Validator;

use VStelmakh\UrlHighlight\Matcher\UrlMatch;
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
     * @param bool $matchEmails
     * @param UrlMatch $match
     * @param bool $expected
     */
    public function testIsValidMatch(
        bool $matchByTLD,
        array $schemeBlacklist,
        array $schemeWhitelist,
        bool $matchEmails,
        UrlMatch $match,
        bool $expected
    ): void {
        $validator = new Validator($matchByTLD, $schemeBlacklist, $schemeWhitelist, $matchEmails);
        $actual = $validator->isValidMatch($match);
        self::assertEquals($expected, $actual, 'Dataset: ' . json_encode(func_get_args()));
    }

    /**
     * @return mixed[]
     */
    public function isValidMatchDataProvider(): array
    {
        return [
            [true, [], [], true, $this->getMatch(null, null, null, null), false],
            [true, [], [], true, $this->getMatch('http', null, null, null), true],
            [true, [], [], true, $this->getMatch(null, null, 'example.com', 'com'), true],
            [true, [], [], true, $this->getMatch(null, null, 'filename.txt', 'txt'), false],
            [true, [], [], true, $this->getMatch('http', null, 'example.com', 'com'), true],
            [true, [], [], true, $this->getMatch('http', null, 'filename.txt', 'txt'), true],
            [true, ['http'], [], true, $this->getMatch('http', null, null, null), false],
            [true, ['ftp'], [], true, $this->getMatch('http', null, null, null), true],
            [true, ['http'], [], true, $this->getMatch(null, null, 'example.com', 'com'), true],
            [true, [], ['http'], true, $this->getMatch('http', null, null, null), true],
            [true, [], ['ftp'], true, $this->getMatch('http', null, null, null), false],
            [true, [], ['http'], true, $this->getMatch(null, null, 'example.com', 'com'), true],
            [true, ['http'], ['http'], true, $this->getMatch('http', null, null, null), false],
            [true, ['ftp'], ['http'], true, $this->getMatch('http', null, null, null), true],
            [true, ['ftp'], ['ssh'], true, $this->getMatch('http', null, null, null), false],
            [true, ['ftp'], ['http'], true, $this->getMatch(null, null, 'example.com', 'com'), true],

            [false, [], [], true, $this->getMatch(null, null, null, null), false],
            [false, [], [], true, $this->getMatch('http', null, null, null), true],
            [false, [], [], true, $this->getMatch(null, null, 'example.com', 'com'), false],
            [false, [], [], true, $this->getMatch(null, null, 'filename.txt', 'txt'), false],
            [false, [], [], true, $this->getMatch('http', null, 'example.com', 'com'), true],
            [false, [], [], true, $this->getMatch('http', null, 'filename.txt', 'txt'), true],
            [false, ['http'], [], true, $this->getMatch('http', null, null, null), false],
            [false, ['ftp'], [], true, $this->getMatch('http', null, null, null), true],
            [false, ['http'], [], true, $this->getMatch(null, null, 'example.com', 'com'), false],
            [false, [], ['http'], true, $this->getMatch('http', null, null, null), true],
            [false, [], ['ftp'], true, $this->getMatch('http', null, null, null), false],
            [false, [], ['http'], true, $this->getMatch(null, null, 'example.com', 'com'), false],
            [false, ['http'], ['http'], true, $this->getMatch('http', null, null, null), false],
            [false, ['ftp'], ['http'], true, $this->getMatch('http', null, null, null), true],
            [false, ['ftp'], ['ssh'], true, $this->getMatch('http', null, null, null), false],
            [false, ['ftp'], ['http'], true, $this->getMatch(null, null, 'example.com', 'com'), false],

            [true, [], [], true, $this->getMatch(null, 'username', 'example.com', 'com'), true],
            [true, [], [], false, $this->getMatch(null, 'username', 'example.com', 'com'), false],
            [true, [], [], true, $this->getMatch('mailto', 'username', 'example.com', 'com'), true],
            [true, [], [], false, $this->getMatch('mailto', 'username', 'example.com', 'com'), false],
        ];
    }

    private function getMatch(?string $scheme, ?string $local, ?string $host, ?string $tld): UrlMatch
    {
        return new UrlMatch('', 0, '', $scheme, $local, $host, $tld, null, null);
    }
}
