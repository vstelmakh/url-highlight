<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Matcher;

use VStelmakh\UrlHighlight\Url;

/**
 * Strips trailing unbalanced brackets, quotes, and punctuation from URL match.
 *
 * When a URL is extracted from surrounding text, trailing characters from the enclosing
 * context may be incorrectly included in the match, e.g. "(see example.com/path)."
 * matches "example.com/path).", this filter transforms such a match to "example.com/path".
 *
 * @internal
 */
final readonly class PunctuationFilter
{
    private const array PAIRED = [')' => '(', ']' => '[', '}' => '{', '»' => '«', '”' => '“', '’' => '‘'];
    private const array SYMMETRIC = ['"' => true, '\'' => true];
    private const array SINGLE = ['.' => true, ',' => true, '!' => true, '?' => true, ';' => true, ':' => true];

    public function filter(Url $url): Url
    {
        $component = $url->fragment ?? $url->query ?? $url->path;
        if ($component === null) {
            return $url;
        }

        $filtered = $this->filterTrailing($component);
        if ($filtered === $component) {
            return $url;
        }

        [$path, $query, $fragment] = [$url->path, $url->query, $url->fragment];
        if ($fragment !== null) {
            $fragment = $filtered;
        } elseif ($query !== null) {
            $query = $filtered;
        } else {
            $path = $filtered;
        }

        return new Url(
            full: substr($url->full, 0, -strlen($component)) . $filtered,
            scheme: $url->scheme,
            userinfo: $url->userinfo,
            host: $url->host,
            port: $url->port,
            path: $path,
            query: $query,
            fragment: $fragment,
        );
    }

    private function filterTrailing(string $value): string
    {
        while ($value !== '' && $this->hasToFilter($value)) {
            $value = mb_substr($value, 0, -1);
        }

        return $value;
    }

    private function hasToFilter(string $value): bool
    {
        $last = mb_substr($value, -1);

        if (isset(self::PAIRED[$last])) {
            return mb_substr_count($value, self::PAIRED[$last]) < mb_substr_count($value, $last);
        }

        if (isset(self::SYMMETRIC[$last])) {
            return mb_substr_count($value, $last) % 2 !== 0;
        }

        return isset(self::SINGLE[$last]);
    }
}
