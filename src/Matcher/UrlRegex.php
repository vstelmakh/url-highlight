<?php

declare(strict_types=1);

// @php-cs-fixer-ignore array_indentation

namespace VStelmakh\UrlHighlight\Matcher;

use VStelmakh\UrlHighlight\Url;

/**
 * Matches everything URL-shaped, which may be wider than the URLs actually are. May include trailing punctuation
 * carried over from the surrounding text or matches that look like URLs (for example, file names).
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
        $cursor = 0;
        $flags = PREG_OFFSET_CAPTURE | PREG_UNMATCHED_AS_NULL;

        // Matching one URL at a time from a moving cursor, rather than collecting every match up front, keeps only
        // the current match in memory. This significantly saves memory for URL-dense inputs.
        while (preg_match($this->pattern, $text, $rawMatch, $flags, $cursor) === 1) {
            [$value, $offset] = $rawMatch[0];

            yield new UrlMatch($offset, $this->toUrl($rawMatch));

            // The host is required, so a match is never empty and the cursor always moves forward.
            $cursor = $offset + strlen($value ?? '');
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
                // Length is capped, because an unbounded run is rescanned on every match attempt, which makes
                // matching quadratic, e.g. for a base64 blob. 64 chars fit any registered scheme (longest is 36). // TODO: check
                '(?<scheme>[a-z][a-z0-9+\-.]{0,63})', // start with letter, consists of: letter, number, "+", "-", "."
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
                // Length is capped for the same reason as the scheme, 64 chars is the max email local part length. // TODO: check
                ']{1,64})',              // close group
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
            '(?:',                      // non-capturing group
                ':',                        // prefixed with: ":"
                '(?<port>',                 // capturing group, 0-65535:
                    '0*',                       // leading zeros, "00080" is port 80
                    '(?:',                      // non-capturing group
                        '6553[0-5]',                // 65530-65535
                        '|655[0-2]\d',              // 65500-65529
                        '|65[0-4]\d{2}',            // 65000-65499
                        '|6[0-4]\d{3}',             // 60000-64999
                        '|[1-5]\d{4}',              // 10000-59999
                        '|\d{1,4}',                 // 0-9999
                    ')',                        // close group
                ')',                        // close group
                '(?!\d)',                   // not followed by a digit, a longer number is not a port
            ')?',                       // close group, optional
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
