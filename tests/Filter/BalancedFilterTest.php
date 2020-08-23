<?php

namespace VStelmakh\UrlHighlight\Tests\Filter;

use VStelmakh\UrlHighlight\Filter\BalancedFilter;
use PHPUnit\Framework\TestCase;

class BalancedFilterTest extends TestCase
{
    /**
     * @dataProvider filterDataProvider
     *
     * @param string $string
     * @param string $expected
     */
    public function testFilter(string $string, string $expected): void
    {
        $balancedFilter = new BalancedFilter();
        $actual = $balancedFilter->filter($string);

        self::assertSame($expected, $actual);
    }

    /**
     * @return array|array[]
     */
    public function filterDataProvider(): array
    {
        return [
            ['', ''],
            ['http://example.com/', 'http://example.com/'],

            ['http://example.com/path_with_(brackets)', 'http://example.com/path_with_(brackets)'],
            ['http://example.com/path_with_(brackets))', 'http://example.com/path_with_(brackets)'],
            ['http://example.com/)path_with_(brackets))', 'http://example.com/'],

            ['http://example.com/path_with_[brackets]', 'http://example.com/path_with_[brackets]'],
            ['http://example.com/path_with_[brackets]]', 'http://example.com/path_with_[brackets]'],
            ['http://example.com/]path_with_[brackets]]', 'http://example.com/'],

            ['http://example.com/path_with_{brackets}', 'http://example.com/path_with_{brackets}'],
            ['http://example.com/path_with_{brackets}}', 'http://example.com/path_with_{brackets}'],
            ['http://example.com/}path_with_{brackets}}', 'http://example.com/'],

            ['http://example.com/(path)_[with]_{brackets}', 'http://example.com/(path)_[with]_{brackets}'],
            ['http://example.com/(path)_[with)]_{brackets}', 'http://example.com/(path)_[with'],
        ];
    }
}
