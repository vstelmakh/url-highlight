<?php

namespace VStelmakh\UrlHighlight\Tests\Util;

use VStelmakh\UrlHighlight\Matcher\Match;
use VStelmakh\UrlHighlight\Util\LinkHelper;
use PHPUnit\Framework\TestCase;

class LinkHelperTest extends TestCase
{
    /**
     * @dataProvider getLinkDataProvider
     *
     * @param Match $match
     * @param string $defaultScheme
     * @param string $expected
     */
    public function testGetLink(Match $match, string $defaultScheme, string $expected): void
    {
        $actual = LinkHelper::getLink($match, $defaultScheme);
        self::assertSame($expected, $actual);
    }

    /**
     * @return array&array[]
     */
    public function getLinkDataProvider(): array
    {
        return [
            [
                new Match('http://example.com/path/to', 0, 'http://example.com/path/to', 'http', null, 'example.com', 'com', null, '/path/to'),
                'http',
                'http://example.com/path/to',
            ],
            [
                new Match('example.com/path/to', 0, 'example.com/path/to', null, null, 'example.com', 'com', null, '/path/to'),
                'http',
                'http://example.com/path/to',
            ],
            [
                new Match('user@example.com', 0, 'user@example.com', null, 'user', 'example.com', 'com', null, null),
                'http',
                'mailto:user@example.com',
            ],
            [
                new Match('mailto:user@example.com', 0, 'mailto:user@example.com', 'mailto', 'user', 'example.com', 'com', null, null),
                'http',
                'mailto:user@example.com',
            ],
        ];
    }
}
