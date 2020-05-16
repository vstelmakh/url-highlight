<?php

namespace VStelmakh\UrlHighlight\Tests\Util;

use VStelmakh\UrlHighlight\Util\Str;
use PHPUnit\Framework\TestCase;

class StrTest extends TestCase
{
    /**
     * @dataProvider getCharsDataProvider
     *
     * @param string $string
     * @param array|string[] $expected
     */
    public function testGetChars(string $string, array $expected): void
    {
        $actual = Str::getChars($string);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return array&array[]
     */
    public function getCharsDataProvider(): array
    {
        return [
            ['hello', ['h', 'e', 'l', 'l', 'o']],
            ['привіт', ['п', 'р', 'и', 'в', 'і', 'т']],
            ['你好', ['你', '好']],
            ['hello ★ world', ['h', 'e', 'l', 'l', 'o', ' ', '★', ' ', 'w', 'o', 'r', 'l', 'd']],
        ];
    }
}
