<?php

namespace VStelmakh\UrlHighlight\Matcher;

use VStelmakh\UrlHighlight\Filter\BalancedFilter;
use VStelmakh\UrlHighlight\Validator\ValidatorInterface;

/**
 * @internal
 */
class Matcher implements MatcherInterface
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var BalancedFilter
     */
    private $balancedFilter;

    /**
     * @internal
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
        $this->balancedFilter = new BalancedFilter();
    }

    /**
     * Match string by url regex
     *
     * @param string $string
     * @return Match|null
     */
    public function match(string $string): ?Match
    {
        $urlRegex = $this->getUrlRegex(true);
        preg_match($urlRegex, $string, $rawMatch, PREG_OFFSET_CAPTURE);
        if (empty($rawMatch)) {
            return null;
        }
        $match = $this->createMatch($rawMatch);
        return $this->validator->isValidMatch($match) ? $match : null;
    }

    /**
     * Get all valid url regex matches from string
     *
     * @param string $string
     * @return array&Match[]
     */
    public function matchAll(string $string): array
    {
        $result = [];
        $urlRegex = $this->getUrlRegex(false);
        preg_match_all($urlRegex, $string, $rawMatches, PREG_SET_ORDER + PREG_OFFSET_CAPTURE);
        foreach ($rawMatches as $rawMatch) {
            $match = $this->createMatch($rawMatch);
            if ($this->validator->isValidMatch($match)) {
                $result[] = $match;
            }
        }
        return $result;
    }

    /**
     * @param bool $strict
     * @return string
     */
    private function getUrlRegex(bool $strict): string
    {
        $prefix = $strict ? '^' : '';
        $suffix = $strict ? '$' : '';

        return '/' . $prefix . '
            (?:                                                        # scheme or possible host
                (?:                                                        # scheme
                    (?<scheme>[a-z][\w-]+):\/{2}                               # scheme ending with :\/\/
                    |                                                          # or
                    (?<scheme>mailto):                                         # mailto
                )
                (?=[^\p{Z}\p{Sm}\p{Sc}\p{Sk}\p{C}\p{P}])                       # followed by valid host character
                |                                                          # or
                (?:                                                        # possible local part (email)
                    (?=[^\.])                                                  # start with not: .
                    (?<local>[a-z0-9~!#$%^&*\-_+=|?\.]{1,64})                  # email local, allowed chars 
                    (?<=[^\.])                                                 # end with not: .
                    @                                                          # at
                )?
                (?<host>                                                   # host (captured only if scheme missing)
                    (?=[^\-])                                                  # label start, not: -
                    (?:[^\p{Z}\p{Sm}\p{Sc}\p{Sk}\p{C}\p{P}]|-){1,63}           # label not: whitespace, mathematical, currency, modifier symbol, control point, punctuation | except -
                    (?<=[^\-])                                                 # label end, not: -
                    (?:                                                        # more label parts
                        \.
                        (?=[^\-])                                                  # label start, not: -
                        (?:[^\p{Z}\p{Sm}\p{Sc}\p{Sk}\p{C}\p{P}]|-){1,63}           # label not: whitespace, mathematical, currency, modifier symbol, control point, punctuation | except -
                        (?<=[^\-])                                                 # label end, not: -
                    )*                                                             
                    \.(?<tld>\w{2,63})                                         # tld length (captured only if match by host) 
                )
                (?:\/|:\d)?                                                # end with slash or port
            )
            (?:                                                        # port, path, query, fragment
                (?<=[\/:\d])                                           # prefixed with slash or port
                [^\s<>]*                                               # any chars except whitespace and <>
                [^\s<>({\[`!;:\'".,?«»“”‘’]                            # not a space or some punctuation chars
            )?
        ' . $suffix . '/ixuJ';
    }

    /**
     * @param array|mixed[] $rawMatch
     * @return Match
     */
    private function createMatch(array $rawMatch): Match
    {
        $fullMatch = $this->balancedFilter->filter($rawMatch[0][0]);

        return new Match(
            $fullMatch,
            $rawMatch[0][1],
            $fullMatch,
            $rawMatch['scheme'][0] ?? null,
            $rawMatch['local'][0] ?? null,
            $rawMatch['host'][0] ?? null,
            $rawMatch['tld'][0] ?? null
        );
    }
}
