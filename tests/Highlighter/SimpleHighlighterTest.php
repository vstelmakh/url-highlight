<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests\Highlighter;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Highlighter\SimpleHighlighter;
use VStelmakh\UrlHighlight\Url;

class SimpleHighlighterTest extends TestCase
{
    private SimpleHighlighter $highlighter;

    #[\Override]
    protected function setUp(): void
    {
        $this->highlighter = new SimpleHighlighter();
    }

    #[DataProvider('renderDataProvider')]
    public function testRender(Url $url, string $expected): void
    {
        $actual = $this->highlighter->render($url);
        self::assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{Url, string}>
     */
    public static function renderDataProvider(): array
    {
        return [
            'url with scheme' => [
                self::url('http://example.com/path', scheme: 'http', path: '/path'),
                '<a href="http://example.com/path">http://example.com/path</a>',
            ],
            'url without scheme gets fallback scheme in href only' => [
                self::url('example.com'),
                '<a href="http://example.com">example.com</a>',
            ],
            'email gets mailto scheme in href only' => [
                self::url('user@example.com', userinfo: 'user'),
                '<a href="mailto:user@example.com">user@example.com</a>',
            ],
            'email with mailto scheme' => [
                self::url('mailto:user@example.com', scheme: 'mailto', userinfo: 'user'),
                '<a href="mailto:user@example.com">mailto:user@example.com</a>',
            ],
            'multibyte url' => [
                self::url('приклад.укр', host: 'приклад.укр'),
                '<a href="http://приклад.укр">приклад.укр</a>',
            ],
            'ampersand and quotes are escaped' => [
                self::url('http://example.com/?q="a"&b=1', scheme: 'http', path: '/', query: '?q="a"&b=1'),
                '<a href="http://example.com/?q=&quot;a&quot;&amp;b=1">http://example.com/?q=&quot;a&quot;&amp;b=1</a>',
            ],
            'angle brackets and single quote are escaped' => [
                self::url("http://example.com/<b>'x", scheme: 'http', path: "/<b>'x"),
                '<a href="http://example.com/&lt;b&gt;&apos;x">http://example.com/&lt;b&gt;&apos;x</a>',
            ],
        ];
    }

    private static function url(
        string $full,
        ?string $scheme = null,
        ?string $userinfo = null,
        string $host = 'example.com',
        ?int $port = null,
        ?string $path = null,
        ?string $query = null,
        ?string $fragment = null,
    ): Url {
        return new Url($full, $scheme, $userinfo, $host, $port, $path, $query, $fragment);
    }
}
