<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests\Util;

use VStelmakh\UrlHighlight\Util\CaseInsensitiveSet;
use PHPUnit\Framework\TestCase;

class CaseInsensitiveSetTest extends TestCase
{
    /**
     * @dataProvider toArrayDataProvider
     *
     * @param array&string[] $values
     * @param array&string[] $expected
     */
    public function testToArray(array $values, array $expected): void
    {
        $normalizedCollection = new CaseInsensitiveSet($values);
        $actual = $normalizedCollection->toArray();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return mixed[]
     */
    public function toArrayDataProvider(): array
    {
        return [
            [[], []],
            [['value_1', 'value_2', 'vaLUE_1'], ['value_1', 'value_2']],
        ];
    }

    /**
     * @dataProvider addDataProvider
     *
     * @param array&string[] $values
     * @param array&string[] $expected
     */
    public function testAdd(array $values, array $expected): void
    {
        $normalizedCollection = new CaseInsensitiveSet([]);
        foreach ($values as $value) {
            $normalizedCollection->add($value);
        }
        $actual = $normalizedCollection->toArray();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return mixed[]
     */
    public function addDataProvider(): array
    {
        return [
            [[], []],
            [['value_1', 'value_2', 'vaLUE_1'], ['value_1', 'value_2']],
        ];
    }

    /**
     * @dataProvider containsDataProvider
     *
     * @param array|string[] $values
     * @param string $value
     * @param bool $expected
     */
    public function testContains(array $values, string $value, bool $expected): void
    {
        $normalizedCollection = new CaseInsensitiveSet($values);
        $actual = $normalizedCollection->contains($value);
        $this->assertEquals($expected, $actual, 'Dataset: ' . json_encode(func_get_args()));
    }

    /**
     * @return mixed[]
     */
    public function containsDataProvider(): array
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
        $normalizedCollection = new CaseInsensitiveSet($values);
        $actual = $normalizedCollection->isEmpty();
        $this->assertEquals($expected, $actual, 'Dataset: ' . json_encode(func_get_args()));
    }

    /**
     * @return mixed[]
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
