<?php

namespace VStelmakh\UrlHighlight;

/**
 * @internal
 */
class NormalizedCollection
{
    /** @var array|string[] */
    private $values;

    /**
     * @param array|string[] $values
     */
    public function __construct(array $values)
    {
        $this->values = $this->getNormalizedMap($values);
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
    public function isContains(string $value): bool
    {
        return isset($this->values[$value]);
    }

    /**
     * @param array|string[] $array
     * @return array|string[]
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