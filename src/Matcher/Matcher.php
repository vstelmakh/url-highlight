<?php

namespace VStelmakh\UrlHighlight\Matcher;

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
     * @internal
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
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
        $match = $this->createMatchOffset($rawMatch);
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
            $match = $this->createMatchOffset($rawMatch);
            if ($this->validator->isValidMatch($match)) {
                $result[] = $match;
            }
        }
        return $result;
    }

    /**
     * Replace all valid url matches by callback
     *
     * @param string $string
     * @param callable $callback
     * @return string
     */
    public function replaceCallback(string $string, callable $callback): string
    {
        $urlRegex = $this->getUrlRegex(false);

        $searchOffset = 0;
        $rawMatchCallback = function (array $rawMatch) use ($string, $callback, &$searchOffset) {
            $offset = strpos($string, $rawMatch[0], $searchOffset) ?: 0;
            $searchOffset = $offset + strlen($rawMatch[0]);
            $match = $this->createMatch($rawMatch, $offset);
            return $this->validator->isValidMatch($match) ? $callback($match) : $match->getFullMatch();
        };
        return preg_replace_callback($urlRegex, $rawMatchCallback, $string) ?? $string;
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
                (?=[^\s`~!@#$%^&*()_=+\[\]{};\'",<>?«»“”‘’\/\\\|:\.\-])    # followed by valid host character
                |                                                          # or
                (?:                                                        # possible local part (email)
                    (?=[^:\.\-])                                               # start with not :-.
                    (?<local>[^\s`~!@#$%^&*()_=+\[\]{};\'",<>?«»“”‘’\/\\\|]{1,64})
                    (?<=[^:\.\-])                                              # end with not :-.
                    @                                                          # at
                )?
                (?<host>                                                   # host (captured only if scheme missing)
                    (?=[^\-])                                                  # label start, not -
                    [^\s`~!@#$%^&*()_=+\[\]{};\'",<>?«»“”‘’\/\\\|:\.]+         # label not allowed chars (most common)
                    (?<=[^\-])                                                 # label end, not -
                    (?:                                                        # sub domain (one or more)
                        \.
                        (?=[^\-])                                                  # sub-domain start, not -
                        [^\s`~!@#$%^&*()_=+\[\]{};\'",<>?«»“”‘’\/\\\|:\.]+         # sub-domain, not allowed chars (most common)
                        (?<=[^\-])                                                 # sub-domain end, not -
                    )*                                                             
                    \.(?<tld>\w{2,63})                                         # tld length (captured only if match by host) 
                )
                (?:\/|:\d)?                                                # end with slash or port
            )  
            (?:                                                        # port, path, query, fragment (one or none)
                (?<=[\/:\d])                                               # prefixed with slash or port
                (?:                                                        # one or more:
                    [^\s()<>]+                                                 # run of non-space, non-()<>
                    |                                                          # or
                    \((?:[^\s()<>]+|(?:\([^\s()<>]+\)))*\)                         # balanced brackets (up to 2 levels)
                )*           
                (?:                                                        # end with:
                    \((?:[^\s()<>]+|(?:\([^\s()<>]+\)))*\)                         # balanced brackets (up to 2 levels)
                    |                                                          # or
                    [^\s`!()\[\]{};:\'".,<>?«»“”‘’]                            # not a space or punctuation chars
                )
            )?
        ' . $suffix . '/ixuJ';
    }

    /**
     * @param array|mixed[] $rawMatch
     * @return Match
     */
    private function createMatchOffset(array $rawMatch): Match
    {
        return new Match(
            $rawMatch[0][0],
            $rawMatch[0][1],
            $rawMatch[0][0],
            $rawMatch['scheme'][0] ?? null,
            $rawMatch['local'][0] ?? null,
            $rawMatch['host'][0] ?? null,
            $rawMatch['tld'][0] ?? null
        );
    }

    /**
     * @param array|mixed[] $rawMatch
     * @param int $offset
     * @return Match
     */
    private function createMatch(array $rawMatch, int $offset): Match
    {
        return new Match(
            $rawMatch[0],
            $offset,
            $rawMatch[0],
            $rawMatch['scheme'] ?? null,
            $rawMatch['local'] ?? null,
            $rawMatch['host'] ?? null,
            $rawMatch['tld'] ?? null
        );
    }
}
