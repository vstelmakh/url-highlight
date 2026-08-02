<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Matcher;

use VStelmakh\UrlHighlight\Url;

/**
 * Matches everything URL-shaped, which may be wider than the URLs actually is. May include trailing punctuation
 * carried over from the surrounding text or matches that look like URLs (for example file names).
 *
 * @internal
 */
final readonly class UrlRegex
{
    private string $pattern;

    public function __construct()
    {
        $this->pattern = $this->build();
    }

    /**
     * @return \Generator<UrlMatch>
     */
    public function findAll(string $text): \Generator
    {
        preg_match_all(
            $this->pattern,
            $text,
            $matches,
            PREG_SET_ORDER | PREG_OFFSET_CAPTURE | PREG_UNMATCHED_AS_NULL,
        );

        foreach ($matches as $rawMatch) {
            yield new UrlMatch($rawMatch[0][1], $this->toUrl($rawMatch));
        }
    }

    /**
     * @param array<int|string, array{0: ?string, 1: int}> $rawMatch [0 => (string) value, 1 => (int) offset]
     */
    private function toUrl(array $rawMatch): Url
    {
        return new Url(
            full: $rawMatch[0][0] ?? '',
            scheme: $rawMatch['scheme'][0],
            userinfo: $rawMatch['userinfo'][0],
            host: $rawMatch['host'][0] ?? '',
            port: $rawMatch['port'][0] !== null ? (int) $rawMatch['port'][0] : null,
            path: $rawMatch['path'][0],
            query: $rawMatch['query'][0],
            fragment: $rawMatch['fragment'][0],
        );
    }

    private function build(): string
    {
        return implode("\n", [
            '/',
            $this->schemeRegex(),   // scheme, optional
            $this->userinfoRegex(), // userinfo, optional
            $this->hostRegex(),     // host, required
            $this->portRegex(),     // port, optional
            $this->pathRegex(),     // path, optional
            $this->queryRegex(),    // query, optional
            $this->fragmentRegex(), // fragment, optional
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
}
