<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Matcher;

use VStelmakh\UrlHighlight\Matcher\Filter\TrailingPunctuationFilter;

/**
 * @internal
 */
final readonly class Matcher
{
    private TrailingPunctuationFilter $trailingPunctuationFilter;

    public function __construct()
    {
        $this->trailingPunctuationFilter = new TrailingPunctuationFilter();
    }

    /**
     * @return array<UrlMatch>
     */
    public function match(string $string): array
    {
        $result = [];
        $urlRegex = $this->regex(false);
        /** @var $matches array<array{0: ?string, 1: int}> */
        preg_match_all($urlRegex, $string, $matches, PREG_SET_ORDER + PREG_OFFSET_CAPTURE + PREG_UNMATCHED_AS_NULL);
        foreach ($matches as $match) {
            $result[] = $this->normalize($match);
        }
        return $result;
    }

    public function matchStrict(string $string): ?UrlMatch
    {
        $urlRegex = $this->regex(true);
        /** @var $match array<array{0: ?string, 1: int}> */
        preg_match($urlRegex, $string, $match, PREG_OFFSET_CAPTURE + PREG_UNMATCHED_AS_NULL);
        return $match === [] ? null : $this->normalize($match);
    }

    private function regex(bool $strict): string
    {
        return implode("\n", [
            '/',
            $strict ? '^' : '',     // start anchor, if strict
            $this->schemeRegex(),   // scheme, optional
            $this->userinfoRegex(), // userinfo, optional
            $this->hostRegex(),     // host, required
            $this->portRegex(),     // port, optional
            $this->pathRegex(),     // path, optional
            $this->queryRegex(),    // query, optional
            $this->fragmentRegex(), // fragment, optional
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
            '(?<path>',       // capturing group
                '\/',             // prefixed with "/"
                '[^\s\?\#]*',     // any chars except whitespace, "?", "#"
            ')?',             // close group, optional
        ]);
    }

    private function queryRegex(): string
    {
        return implode('', [
            '(?<query>',    // capturing group
                '\?',           // prefixed with "?"
                '[^\s\#]*',     // any chars except whitespace, "#"
            ')?',           // close group, optional
        ]);
    }

    private function fragmentRegex(): string
    {
        return implode('', [
            '(?<fragment>',  // capturing group
                '\#',            // prefixed with "#"
                '[^\s]*',        // any chars except whitespace
            ')?',            // close group, optional
        ]);
    }

    /**
     * @param array<array{0: ?string, 1: int}> $match [0 => (string) value, 1 => (int) offset]
     */
    private function normalize(array $match): UrlMatch
    {
        $host = $match['host'][0] ?? '';
        $lastLabel = strrchr($host, '.');
        $tld = $lastLabel !== false ? substr($lastLabel, 1) : null;

        $normalized = new UrlMatch(
            match: $match[0][0] ?? '',
            offset: $match[0][1],
            scheme: $match['scheme'][0],
            userinfo: $match['userinfo'][0],
            host: $host,
            tld: $tld,
            port: $match['port'][0],
            path: $match['path'][0],
            query: $match['query'][0],
            fragment: $match['fragment'][0],
        );

        return $this->trailingPunctuationFilter->filter($normalized);
    }
}
