<?php

namespace VStelmakh\UrlHighlight\Validator;

use VStelmakh\UrlHighlight\Matcher\Match;

interface ValidatorInterface
{
    /**
     * @param Match $match
     * @return bool
     */
    public function isValidMatch(Match $match): bool;
}
