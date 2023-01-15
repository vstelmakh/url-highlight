<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Validator;

use VStelmakh\UrlHighlight\Matcher\UrlMatch;

interface ValidatorInterface
{
    /**
     * @param UrlMatch $match
     * @return bool
     */
    public function isValidMatch(UrlMatch $match): bool;
}
