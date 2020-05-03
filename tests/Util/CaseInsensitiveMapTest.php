<?php

namespace VStelmakh\UrlHighlight\Tests\Util;

use VStelmakh\UrlHighlight\Util\CaseInsensitiveMap;
use PHPUnit\Framework\TestCase;

class CaseInsensitiveMapTest extends TestCase
{
    /**
     * @dataProvider setDataProvider
     *
     * @param array&string[] $values
     * @param array&string[] $expected
     */
    public function testSet(array $values, array $expected): void
    {
        $normalizedMap = new CaseInsensitiveMap([]);
        foreach ($values as $key => $value) {
            $normalizedMap->set($key, $value);
        }
        $actual = $normalizedMap->toArray();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array|array[]
     */
    public function setDataProvider(): array
    {
        return [
            [[], []],
            [['VAlue_1' => 1, 'vAlue_2' => '2', 'vaLUE_1' => true], ['value_1' => 1, 'value_2' => '2']],
        ];
    }

    /**
     * @dataProvider getKeysDataProvider
     *
     * @param array&string[] $values
     * @param array&string[] $expected
     */
    public function testGetKeys(array $values, array $expected): void
    {
        $normalizedMap = new CaseInsensitiveMap($values);
        $actual = $normalizedMap->getKeys();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array|array[]
     */
    public function getKeysDataProvider(): array
    {
        return [
            [[], []],
            [['VAlue_1' => 1, 'vAlue_2' => '2', 'vaLUE_1' => true], ['value_1', 'value_2']],
        ];
    }

    /**
     * @dataProvider getValuesDataProvider
     *
     * @param array&string[] $values
     * @param array&string[] $expected
     */
    public function testGetValues(array $values, array $expected): void
    {
        $normalizedMap = new CaseInsensitiveMap($values);
        $actual = $normalizedMap->getValues();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array|array[]
     */
    public function getValuesDataProvider(): array
    {
        return [
            [[], []],
            [['VAlue_1' => 1, 'vAlue_2' => '2', 'vaLUE_1' => true], [1, '2']],
        ];
    }

    /**
     * @dataProvider toArrayDataProvider
     *
     * @param array&string[] $values
     * @param array&string[] $expected
     */
    public function testToArray(array $values, array $expected): void
    {
        $normalizedMap = new CaseInsensitiveMap($values);
        $actual = $normalizedMap->toArray();
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array|array[]
     */
    public function toArrayDataProvider(): array
    {
        return [
            [[], []],
            [['VAlue_1' => 1, 'vAlue_2' => '2', 'vaLUE_1' => true], ['value_1' => 1, 'value_2' => '2']],
        ];
    }
}
