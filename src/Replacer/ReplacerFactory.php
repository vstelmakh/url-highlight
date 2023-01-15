<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer;

use VStelmakh\UrlHighlight\Matcher\MatcherFactory;
use VStelmakh\UrlHighlight\Matcher\MatcherInterface;

class ReplacerFactory
{
    /**
     * Create replacer using provided or default matcher.
     *
     * @param MatcherInterface|null $matcher
     * @return Replacer
     */
    public static function createReplacer(?MatcherInterface $matcher = null): Replacer
    {
        $matcher = $matcher ?? MatcherFactory::createMatcher();
        return new Replacer($matcher);
    }
}
