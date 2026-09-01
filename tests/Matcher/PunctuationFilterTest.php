<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests\Matcher;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Matcher\PunctuationFilter;
use VStelmakh\UrlHighlight\Url;

class PunctuationFilterTest extends TestCase
{
    private PunctuationFilter $punctuationFilter;

    #[\Override]
    protected function setUp(): void
    {
        $this->punctuationFilter = new PunctuationFilter();
    }

    #[DataProvider('filterDataProvider')]
    public function testFilter(Url $url, Url $expected): void
    {
        $actual = $this->punctuationFilter->filter($url);
        self::assertEquals($expected, $actual);
    }

    /**
     * @return array<string, array{Url, Url}>
     */
    public static function filterDataProvider(): array
    {
        return [
            'host only' => [
                self::url('example.com'),
                self::url('example.com'),
            ],
            'nothing to filter' => [
                self::url('example.com/path', path: '/path'),
                self::url('example.com/path', path: '/path'),
            ],
            'single trailing dot' => [
                self::url('example.com/path.', path: '/path.'),
                self::url('example.com/path', path: '/path'),
            ],
            'multiple trailing dots' => [
                self::url('example.com/path...', path: '/path...'),
                self::url('example.com/path', path: '/path'),
            ],
            'trailing comma' => [
                self::url('example.com/path,', path: '/path,'),
                self::url('example.com/path', path: '/path'),
            ],
            'trailing exclamation' => [
                self::url('example.com/path!', path: '/path!'),
                self::url('example.com/path', path: '/path'),
            ],
            'trailing semicolon' => [
                self::url('example.com/path;', path: '/path;'),
                self::url('example.com/path', path: '/path'),
            ],
            'trailing colon' => [
                self::url('example.com/path:', path: '/path:'),
                self::url('example.com/path', path: '/path'),
            ],
            'trailing question mark' => [
                self::url('example.com/path?a=1?', path: '/path', query: '?a=1?'),
                self::url('example.com/path?a=1', path: '/path', query: '?a=1'),
            ],
            'unbalanced parenthesis' => [
                self::url('example.com/path)', path: '/path)'),
                self::url('example.com/path', path: '/path'),
            ],
            'unbalanced square bracket' => [
                self::url('example.com/path]', path: '/path]'),
                self::url('example.com/path', path: '/path'),
            ],
            'unbalanced curly bracket' => [
                self::url('example.com/path}', path: '/path}'),
                self::url('example.com/path', path: '/path'),
            ],
            'unbalanced guillemet' => [
                self::url('example.com/path»', path: '/path»'),
                self::url('example.com/path', path: '/path'),
            ],
            'unbalanced typographic double quote' => [
                self::url('example.com/path”', path: '/path”'),
                self::url('example.com/path', path: '/path'),
            ],
            'unbalanced typographic single quote' => [
                self::url('example.com/path’', path: '/path’'),
                self::url('example.com/path', path: '/path'),
            ],
            'extra closing parenthesis' => [
                self::url('example.com/path_(a))', path: '/path_(a))'),
                self::url('example.com/path_(a)', path: '/path_(a)'),
            ],
            'trailing opening parenthesis is kept' => [
                self::url('example.com/path_(', path: '/path_('),
                self::url('example.com/path_(', path: '/path_('),
            ],
            'balanced parentheses are kept' => [
                self::url('example.com/path_(a)', path: '/path_(a)'),
                self::url('example.com/path_(a)', path: '/path_(a)'),
            ],
            'balanced guillemets are kept' => [
                self::url('example.com/«path»', path: '/«path»'),
                self::url('example.com/«path»', path: '/«path»'),
            ],
            'balanced typographic single quotes are kept' => [
                self::url('example.com/‘path’', path: '/‘path’'),
                self::url('example.com/‘path’', path: '/‘path’'),
            ],
            'odd double quote' => [
                self::url('example.com/path"', path: '/path"'),
                self::url('example.com/path', path: '/path'),
            ],
            'even double quotes are kept' => [
                self::url('example.com/"path"', path: '/"path"'),
                self::url('example.com/"path"', path: '/"path"'),
            ],
            'odd single quote' => [
                self::url("example.com/path'", path: "/path'"),
                self::url('example.com/path', path: '/path'),
            ],
            'even single quotes are kept' => [
                self::url("example.com/'path'", path: "/'path'"),
                self::url("example.com/'path'", path: "/'path'"),
            ],
            'mixed trailing characters' => [
                self::url('example.com/path).', path: '/path).'),
                self::url('example.com/path', path: '/path'),
            ],
            'trailing characters in query' => [
                self::url('example.com/path?a=1.', path: '/path', query: '?a=1.'),
                self::url('example.com/path?a=1', path: '/path', query: '?a=1'),
            ],
            'trailing characters in fragment' => [
                self::url('example.com/path#top.', path: '/path', fragment: '#top.'),
                self::url('example.com/path#top', path: '/path', fragment: '#top'),
            ],
            'only the last component is filtered' => [
                self::url('example.com/path.?a=1.', path: '/path.', query: '?a=1.'),
                self::url('example.com/path.?a=1', path: '/path.', query: '?a=1'),
            ],
            'fragment takes precedence over query' => [
                self::url('example.com/path?a=1.#top', path: '/path', query: '?a=1.', fragment: '#top'),
                self::url('example.com/path?a=1.#top', path: '/path', query: '?a=1.', fragment: '#top'),
            ],
            'empty fragment is dropped keeping query' => [
                self::url('example.com/path?a=1#.', path: '/path', query: '?a=1', fragment: '#.'),
                self::url('example.com/path?a=1', path: '/path', query: '?a=1'),
            ],
            'empty query is dropped' => [
                self::url('example.com?.', query: '?.'),
                self::url('example.com'),
            ],
            'empty fragment is dropped' => [
                self::url('example.com#.', fragment: '#.'),
                self::url('example.com'),
            ],
            'query delimiter alone is dropped' => [
                self::url('example.com?', query: '?'),
                self::url('example.com'),
            ],
            'fragment delimiter alone is dropped' => [
                self::url('example.com#', fragment: '#'),
                self::url('example.com'),
            ],
            'root path is kept' => [
                self::url('example.com/...', path: '/...'),
                self::url('example.com/', path: '/'),
            ],
            'other components are preserved' => [
                self::url(
                    'http://user@example.com:8080/path.',
                    scheme: 'http',
                    userinfo: 'user',
                    port: 8080,
                    path: '/path.',
                ),
                self::url(
                    'http://user@example.com:8080/path',
                    scheme: 'http',
                    userinfo: 'user',
                    port: 8080,
                    path: '/path',
                ),
            ],
        ];
    }

    private static function url(
        string $full,
        string $host = 'example.com',
        ?string $scheme = null,
        ?string $userinfo = null,
        ?int $port = null,
        ?string $path = null,
        ?string $query = null,
        ?string $fragment = null,
    ): Url {
        return new Url($full, $scheme, $userinfo, $host, $port, $path, $query, $fragment);
    }
}
