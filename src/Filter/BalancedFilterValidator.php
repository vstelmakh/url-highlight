<?php

namespace VStelmakh\UrlHighlight\Filter;

/**
 * @internal
 */
class BalancedFilterValidator
{
    /**
     * @var int
     */
    private $count = 0;

    /**
     * @var string
     */
    private $openChar;

    /**
     * @var string
     */
    private $closeChar;

    /**
     * @internal
     * @param string $openChar Open character e.g. (
     * @param string $closeChar Close character e.g. )
     */
    public function __construct(string $openChar, string $closeChar)
    {
        $this->openChar = $openChar;
        $this->closeChar = $closeChar;
    }

    /**
     * Check if current char is balanced
     *
     * @param string $char
     * @return bool
     */
    public function isValidChar(string $char): bool
    {
        if ($char === $this->openChar) {
            $this->count++;
            return true;
        }

        if ($char === $this->closeChar) {
            $this->count--;
            return $this->count >= 0;
        }

        return true;
    }
}
