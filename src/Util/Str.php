<?php

namespace VStelmakh\UrlHighlight\Util;

/**
 * @internal
 */
class Str
{
    /**
     * Split string into array of characters
     *
     * @param string $string
     * @return array&string[]
     */
    public static function getChars(string $string): array
    {
        return preg_split('//u', $string, null, PREG_SPLIT_NO_EMPTY) ?: [];
    }
}
