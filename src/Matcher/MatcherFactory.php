<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Matcher;

use VStelmakh\UrlHighlight\Encoder\EncoderInterface;
use VStelmakh\UrlHighlight\Validator\Validator;
use VStelmakh\UrlHighlight\Validator\ValidatorInterface;

class MatcherFactory
{
    /**
     * Create matcher or encoded matcher
     *
     * @param ValidatorInterface|null $validator
     * @param EncoderInterface|null $encoder
     * @return MatcherInterface
     */
    public static function createMatcher(
        ?ValidatorInterface $validator = null,
        ?EncoderInterface $encoder = null
    ): MatcherInterface {
        $validator = $validator ?? new Validator();
        $matcher = new Matcher($validator);
        return $encoder ? new EncodedMatcher($matcher, $encoder) : $matcher;
    }
}
