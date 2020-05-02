<?php

namespace VStelmakh\UrlHighlight\Util;

/**
 * Store unique string values. Case insensitive.
 *
 * @internal
 */
class NormalizedCollection
{
    /**
     * @var array&string[]
     */
    private $values;

    /**
     * @param array&string[] $values
     */
    public function __construct(array $values)
    {
        $this->values = $this->getNormalizedMap($values);
    }

    /**
     * @return array&string[]
     */
    public function toArray(): array
    {
        return array_values($this->values);
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->values);
    }

    /**
     * @param string $value
     * @return bool
     */
    public function contains(string $value): bool
    {
        $value = $this->normalize($value);
        return isset($this->values[$value]);
    }

    /**
     * @param array&string[] $array
     * @return array&string[]
     */
    private function getNormalizedMap(array $array): array
    {
        $result = [];
        foreach ($array as $value) {
            $value = $this->normalize($value);
            $result[$value] = $value;
        }
        return $result;
    }

    /**
     * @param string $string
     * @return string
     */
    private function normalize(string $string): string
    {
        return mb_strtolower(trim($string));
    }
}
