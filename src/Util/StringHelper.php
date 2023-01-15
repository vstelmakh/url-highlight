<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Util;

/**
 * @internal
 */
class StringHelper
{
    /**
     * Split string into array of characters
     *
     * @param string $string
     * @return array&string[]
     */
    public static function getChars(string $string): array
    {
        return preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }
}
