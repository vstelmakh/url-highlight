<?php

namespace VStelmakh\UrlHighlight\Tests\Util;

use VStelmakh\UrlHighlight\Util\StringHelper;
use PHPUnit\Framework\TestCase;

class StringHelperTest extends TestCase
{
    /**
     * @dataProvider getCharsDataProvider
     *
     * @param string $string
     * @param array|string[] $expected
     */
    public function testGetChars(string $string, array $expected): void
    {
        $actual = StringHelper::getChars($string);
        self::assertSame($expected, $actual);
    }

    /**
     * @return array&array[]
     */
    public function getCharsDataProvider(): array
    {
        return [
            ['', []],
            ['hello', ['h', 'e', 'l', 'l', 'o']],
            ['привіт', ['п', 'р', 'и', 'в', 'і', 'т']],
            ['你好', ['你', '好']],
            ['hello ★ world', ['h', 'e', 'l', 'l', 'o', ' ', '★', ' ', 'w', 'o', 'r', 'l', 'd']],
        ];
    }
}
