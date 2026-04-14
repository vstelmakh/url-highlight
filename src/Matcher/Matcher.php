<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Matcher;

use VStelmakh\UrlHighlight\Filter\BalancedFilter;
use VStelmakh\UrlHighlight\Validator\ValidatorInterface;

/**
 * @internal
 */
class Matcher implements MatcherInterface
{
    /** @var ValidatorInterface */
    private $validator;

    /** @var BalancedFilter */
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
     * @return UrlMatch|null
     */
    public function match(string $string): ?UrlMatch
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
     * @return array&UrlMatch[]
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
        return implode("\n", [
            '/',
            $strict ? '^' : '',     // start anchor, if strict
            $this->schemeRegex(),   // scheme, optional
            $this->userinfoRegex(), // userinfo, optional
            $this->hostRegex(),     // host, required
            $this->portRegex(),     // port, optional
            $this->pathRegex(),     // path, optional
            $strict ? '$' : '',     // end anchor, if strict
            '/ixuJ',                // case-insensitive, extended, unicode, j-changed
        ]);
    }

    private function schemeRegex(): string
    {
        return implode('', [
            '(?|',                               // branch reset group
                '(?<scheme>[a-z][a-z0-9+\-.]*)',     // start with letter, consists of: letter, number, "+", "-", "."
                ':\/{2}',                            // followed by "://"
                '|',                                 // or
                '(?<scheme>mailto):',                // mailto, followed by ":"
            ')?',                                // close group, optional
        ]);
    }

    private function userinfoRegex(): string
    {
        return implode('', [
            '(?:',                   // non-capturing group
                '(?:',                   // non-capturing group
                    '(?<=\/{2})',            // prefixed with "//" (has scheme)
                    '|',                     // or
                    '(?=[^',                 // lookahead, not starting with:
                        '\p{Sm}',                // mathematical
                        '\p{Sc}',                // currency
                        '\p{Sk}',                // modifier symbol
                        '\p{P}',                 // punctuation
                    '])',                    // close lookahead
                ')',                     // close group
                '(?<userinfo>[',         // capturing group, only:
                    '\p{L}\d\-\._~',         // unreserved
                    '%',                     // percent encoded
                    '!$&\'()*+,;=',          // sub-delims
                    ':',                     // ":"
                ']+)',                   // one or more, close group
                '@',                     // suffixed with "@"
            ')?',                    // close group, optional
        ]);
    }

    private function hostRegex(): string
    {
        $label = implode('', [
            '(?=[^\-])',                 // not start with: "-"
            '(?:',                       // non-capturing group, consists of:
                '[^',                        // not (exclude):
                    '\p{Z}',                     // whitespace
                    '\p{Sm}',                    // mathematical
                    '\p{Sc}',                    // currency
                    '\p{Sk}',                    // combining character (mark)
                    '\p{C}',                     // control character (invisible)
                    '\p{P}',                     // punctuation
                ']',
                '|',
                '[',                         // except (include):
                    '\-',                        // "-"
                    '\x{200C}',                  // zero width non-joiner
                    '\x{200D}',                  // zero width joiner
                    '\x{00B7}',                  // middle dot
                    '\x{0375}',                  // greek lower numeral sign
                    '\x{05F3}',                  // hebrew punctuation geresh
                    '\x{05F4}',                  // hebrew punctuation gershayim
                    '\x{30FB}',                  // katakana middle dot
                    '\x{0660}-\x{0669}',         // arabic-indic digits
                    '\x{06F0}-\x{06F9}',         // extended arabic-indic digits
                ']',
            ')',                         // close group
            '{1,63}',                    // length: 1-63 chars
            '(?<=[^\-])',                // not end with: "-"
        ]);

        return implode('', [
            '(?<host>',                       // capturing group
                '(?<=\/{2}|@)',                   // prefixed with: "//" (has scheme) or "@" (email)
                "(?:{$label}\.){0,}{$label}",     // no subdomain requirement
                '|',                              // otherwise
                "(?:{$label}\.){1,}{$label}",     // at least 1 subdomain
            ')',                              // close group
        ]);
    }

    private function portRegex(): string
    {
        return implode('', [
            '(?:',              // non-capturing group
                ':',                // prefixed with: ":"
                '(?<port>\d+)',     // capturing group, at least 1 digit
            ')?',               // close group, optional
        ]);
    }

    private function pathRegex(): string
    {
        return implode('', [
            '(?<path> ',                             // capturing group, the rest of the URL: path, query, fragment
                '[\/?]',                                 // prefixed with "/" or "?"
                '[^\s<>]*',                              // any chars except whitespace and "<", ">"
                '(?<=[^\s<>({\[`!;:\'\".,?«»“”‘’])',     // end with not a space or some punctuation chars
            ')?',                                    // close group, optional
        ]);
    }

    /**
     * @param array<array{0: string, 1: int}> $rawMatch [0 => (string) match, 1 => (int) offset]
     * @return UrlMatch
     */
    private function createMatch(array $rawMatch): UrlMatch
    {
        $fullMatch = $this->balancedFilter->filter($rawMatch[0][0]);
        $path = $this->balancedFilter->filter($rawMatch['path'][0] ?? '');

        $lastLabel = strrchr($rawMatch['host'][0] ?? '', '.');
        /** @var string $tld */
        $tld = $lastLabel !== false ? substr($lastLabel, 1) : null;

        return new UrlMatch(
            $fullMatch,
            $rawMatch[0][1],
            $fullMatch,
            $rawMatch['scheme'][0] ?? null,
            $rawMatch['userinfo'][0] ?? null,
            $rawMatch['host'][0] ?? null,
            $tld,
            $rawMatch['port'][0] ?? null,
            $path
        );
    }
}
