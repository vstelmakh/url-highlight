<?php

namespace VStelmakh\UrlHighlight\Util;

use VStelmakh\UrlHighlight\Matcher\Match;

class LinkHelper
{
    /**
     * Return match link with scheme depends on context
     *
     * @param Match $match
     * @param string $defaultScheme
     * @return string
     */
    public static function getLink(Match $match, string $defaultScheme): string
    {
        $scheme = '';

        if (empty($match->getScheme())) {
            $isEmail = !empty($match->getUserinfo());
            $scheme = $isEmail ? 'mailto:' : $defaultScheme . '://';
        }

        return $scheme . $match->getUrl();
    }
}
