<?php

namespace VStelmakh\UrlHighlight\Tests;

use VStelmakh\UrlHighlight\NormalizedCollection;
use PHPUnit\Framework\TestCase;

class NormalizedCollectionTest extends TestCase
{
    /**
     * @dataProvider isContainsDataProvider
     *
     * @param array|string[] $values
     * @param string $value
     * @param bool $expected
     */
    public function testIsContains(array $values, string $value, bool $expected): void
    {
        $normalizedCollection = new NormalizedCollection($values);
        $actual = $normalizedCollection->isContains($value);
        $this->assertEquals($expected, $actual, 'Dataset: ' . json_encode(func_get_args()));
    }

    /**
     * @return array|array[]
     */
    public function isContainsDataProvider(): array
    {
        return [
            [[], '', false],
            [[], 'value', false],
            [['value'], 'value', true],
            [['value_1'], 'value_2', false],
            [['value_1', 'value_2'], 'value_1', true],
            [['valUE'], 'value', true],
            [['value'], 'valUE', true],
            [['valUE'], 'VAlue', true],
            [['valUE', 'other value', 'VALUE'], 'value', true],
            [[' valUE '], 'value', true],
        ];
    }

    /**
     * @dataProvider isEmptyDataProvider
     *
     * @param array|string[] $values
     * @param bool $expected
     */
    public function testIsEmpty(array $values, bool $expected): void
    {
        $normalizedCollection = new NormalizedCollection($values);
        $actual = $normalizedCollection->isEmpty();
        $this->assertEquals($expected, $actual, 'Dataset: ' . json_encode(func_get_args()));
    }

    /**
     * @return array|array[]
     */
    public function isEmptyDataProvider(): array
    {
        return [
            [[], true],
            [['value'], false],
            [['value_1', 'value_2'], false],
        ];
    }
}
