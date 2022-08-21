<?php

namespace VStelmakh\UrlHighlight\Tests\Util;

use VStelmakh\UrlHighlight\Matcher\UrlMatch;
use VStelmakh\UrlHighlight\Util\LinkHelper;
use PHPUnit\Framework\TestCase;

class LinkHelperTest extends TestCase
{
    /**
     * @dataProvider getLinkDataProvider
     *
     * @param UrlMatch $match
     * @param string $defaultScheme
     * @param string $expected
     */
    public function testGetLink(UrlMatch $match, string $defaultScheme, string $expected): void
    {
        $actual = LinkHelper::getLink($match, $defaultScheme);
        self::assertSame($expected, $actual);
    }

    /**
     * @return mixed[]
     */
    public function getLinkDataProvider(): array
    {
        return [
            [
                new UrlMatch('http://example.com/path/to', 0, 'http://example.com/path/to', 'http', null, 'example.com', 'com', null, '/path/to'),
                'http',
                'http://example.com/path/to',
            ],
            [
                new UrlMatch('example.com/path/to', 0, 'example.com/path/to', null, null, 'example.com', 'com', null, '/path/to'),
                'http',
                'http://example.com/path/to',
            ],
            [
                new UrlMatch('user@example.com', 0, 'user@example.com', null, 'user', 'example.com', 'com', null, null),
                'http',
                'mailto:user@example.com',
            ],
            [
                new UrlMatch('mailto:user@example.com', 0, 'mailto:user@example.com', 'mailto', 'user', 'example.com', 'com', null, null),
                'http',
                'mailto:user@example.com',
            ],
        ];
    }
}
