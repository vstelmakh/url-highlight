<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Matcher\Filter;

use VStelmakh\UrlHighlight\Matcher\UrlMatch;

/**
 * Strips trailing unbalanced brackets, quotes, and punctuation from URL match.
 *
 * When a URL is extracted from surrounding text, trailing characters from the enclosing
 * context may be incorrectly included in the match, e.g. "(see example.com/path)."
 * matches "example.com/path).", this filter transforms such a match to "example.com/path".
 *
 * @internal
 */
final readonly class TrailingPunctuationFilter
{
    private const array PAIRED = [')' => '(', ']' => '[', '}' => '{', '»' => '«', '”' => '“', '’' => '‘'];
    private const array SYMMETRIC = ['"' => true, '\'' => true];
    private const array SINGLE = ['.' => true, ',' => true, '!' => true, '?' => true, ';' => true, ':' => true];

    public function filter(UrlMatch $match): UrlMatch
    {
        $component = $match->fragment ?? $match->query ?? $match->path;
        if ($component === null) {
            return $match;
        }

        $filtered = $this->filterTrailing($component);
        if ($filtered === $component) {
            return $match;
        }

        [$path, $query, $fragment] = [$match->path, $match->query, $match->fragment];
        if ($fragment !== null) {
            $fragment = $filtered;
        } elseif ($query !== null) {
            $query = $filtered;
        } else {
            $path = $filtered;
        }

        return new UrlMatch(
            substr($match->match, 0, -strlen($component)) . $filtered,
            $match->offset,
            $match->scheme,
            $match->userinfo,
            $match->host,
            $match->tld,
            $match->port,
            $path,
            $query,
            $fragment,
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
