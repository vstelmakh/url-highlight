<?php

namespace VStelmakh\UrlHighlight\Filter;

use VStelmakh\UrlHighlight\Util\Str;

/**
 * @internal
 */
class BalancedFilter implements FilterInterface
{
    /*
     * Map of openChar => closeChar
     */
    private const BALANCED_CHARS = [
        '(' => ')',
        '{' => '}',
        '[' => ']',
    ];

    /**
     * Regex pattern to check if string contains any close char
     *
     * @var string
     */
    private $closeCharsPattern;

    /**
     * @internal
     */
    public function __construct()
    {
        $this->closeCharsPattern = $this->getCloseCharsPattern();
    }

    /**
     * Cut the string on first non balanced bracket occurrence.
     * Keep in mind, there is no check for correct parenthesis.
     * Check only that close chars have same amount of open chars.
     *
     * @inheritdoc
     */
    public function filter(string $string): string
    {
        if (!$this->isApplicable($string)) {
            return $string;
        }

        $validators = $this->getValidators();
        return $this->applyValidators($string, $validators);
    }

    /**
     * @return string
     */
    private function getCloseCharsPattern(): string
    {
        $closeChars = implode('', self::BALANCED_CHARS);
        return sprintf(
            '/[%s]/iu',
            preg_quote($closeChars, '/')
        );
    }

    /**
     * Check if filter applicable to provided string
     *
     * @param string $string
     * @return bool
     */
    private function isApplicable(string $string): bool
    {
        return (bool) preg_match($this->closeCharsPattern, $string);
    }

    /**
     * @return array|BalancedFilterValidator[]
     */
    private function getValidators(): array
    {
        $validators = [];
        foreach (self::BALANCED_CHARS as $openChar => $closeChar) {
            $validators[] = new BalancedFilterValidator($openChar, $closeChar);
        }
        return $validators;
    }

    /**
     * Loop over all chars and check against all validators
     *
     * @param string $string
     * @param iterable|BalancedFilterValidator[] $filters
     * @return string
     */
    private function applyValidators(string $string, iterable $filters): string
    {
        $result = '';
        $chars = Str::getChars($string);

        foreach ($chars as $char) {
            foreach ($filters as $filter) {
                if (!$filter->isValidChar($char)) {
                    return $result;
                }
            }
            $result .= $char;
        }

        return $result;
    }
}
