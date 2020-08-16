<?php

namespace VStelmakh\UrlHighlight\Tests\Filter;

use VStelmakh\UrlHighlight\Filter\BalancedFilterValidator;
use PHPUnit\Framework\TestCase;

class BalancedFilterValidatorTest extends TestCase
{
    /**
     * @dataProvider isValidCharDataProvider
     *
     * @param array&string[] $chars
     * @param array&bool[] $expected
     */
    public function testIsValidChar(array $chars, array $expected): void
    {
        $balancedFilterValidator = new BalancedFilterValidator('(', ')');

        $actual = [];
        foreach ($chars as $char) {
            $actual[] = $balancedFilterValidator->isValidChar($char);
        }

        self::assertSame($expected, $actual, 'Dataset: ' . json_encode($chars));
    }

    /**
     * @return array|array[]
     */
    public function isValidCharDataProvider(): array
    {
        return [
            [['a'], [true]],
            [['('], [true]],
            [['(', ')'], [true, true]],
            [['a', '('], [true, true]],
            [['a', '(', ')'], [true, true, true]],
            [['a', '(', ')', 'a'], [true, true, true, true]],
            [[')'], [false]],
            [['a', ')'], [true, false]],
            [['a', ')', 'a'], [true, false, true]],
            [['(', 'a', ')', 'a', ')'], [true, true, true, true, false]],
        ];
    }
}
