<?php

namespace VStelmakh\UrlHighlight;

/**
 * @internal
 */
class Matcher
{
    /**
     * @var MatchValidator
     */
    private $matchValidator;

    public function __construct(MatchValidator $matchValidator)
    {
        $this->matchValidator = $matchValidator;
    }

    /**
     * Match string by url regex
     *
     * @param string $string
     * @return array|null
     */
    public function match(string $string): ?array
    {
        $urlRegex = $this->getUrlRegex(true);
        preg_match($urlRegex, $string, $match);
        return $this->matchValidator->isValidMatch($match) ? $match : null;
    }

    /**
     * Get all valid url regex matches from string
     *
     * @param string $string
     * @return array
     */
    public function matchAll(string $string): array
    {
        $result = [];
        $urlRegex = $this->getUrlRegex(false);
        preg_match_all($urlRegex, $string, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $result[] = $this->matchValidator->isValidMatch($match);
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
        $callbackWithMatchValidator = function ($match) use ($callback) {
            return $this->matchValidator->isValidMatch($match) ? $callback($match) : $match[0];
        };
        return preg_replace_callback($urlRegex, $callbackWithMatchValidator, $string) ?? $string;
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
                [\/:]?                                                     # end with \/ or : 
            )  
            (?:                                                        # port, path, query, fragment (one or none)
                (?<=[\/:])                                                 # prefixed with \/ or :
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
}