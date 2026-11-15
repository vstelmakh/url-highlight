<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests\Highlighter;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Highlighter\Anchor;
use VStelmakh\UrlHighlight\Url;

class AnchorTest extends TestCase
{
    #[DataProvider('renderDataProvider')]
    public function testRender(Anchor $anchor, string $expected): void
    {
        $actual = $anchor->render();
        self::assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{Anchor, string}>
     */
    public static function renderDataProvider(): array
    {
        $anchor = self::createAnchor(full: 'example.com', host: 'example.com');

        return [
            'url with scheme' => [
                self::createAnchor(full: 'https://example.com/path', host: 'example.com', scheme: 'https', path: '/path'),
                '<a href="https://example.com/path">https://example.com/path</a>',
            ],
            'no scheme uses fallback' => [
                self::createAnchor(full: 'example.com', host: 'example.com'),
                '<a href="http://example.com">example.com</a>',
            ],
            'custom fallback scheme' => [
                self::createAnchor(full: 'example.com', host: 'example.com', fallbackScheme: 'https'),
                '<a href="https://example.com">example.com</a>',
            ],
            'email gets mailto' => [
                self::createAnchor(full: 'user@example.com', host: 'example.com', userinfo: 'user'),
                '<a href="mailto:user@example.com">user@example.com</a>',
            ],
            'url is escaped' => [
                self::createAnchor(full: 'https://example.com/?q="a"&b=<c>', host: 'example.com', scheme: 'https', path: '/', query: '?q="a"&b=<c>'),
                '<a href="https://example.com/?q=&quot;a&quot;&amp;b=&lt;c&gt;">https://example.com/?q=&quot;a&quot;&amp;b=&lt;c&gt;</a>',
            ],
            'href replaced' => [
                $anchor->withAttribute('href', 'https://example.org'),
                '<a href="https://example.org">example.com</a>',
            ],
            'href uppercase name' => [
                $anchor->withAttribute('HREF', 'https://example.org'),
                '<a href="https://example.org">example.com</a>',
            ],
            'href stays first' => [
                $anchor->withAttribute('target', '_blank')->withAttribute('href', 'https://example.org'),
                '<a href="https://example.org" target="_blank">example.com</a>',
            ],
            'href without value' => [
                $anchor->withAttribute('href'),
                '<a href>example.com</a>',
            ],
            'text' => [
                $anchor->withText('Example'),
                '<a href="http://example.com">Example</a>',
            ],
            'text is escaped' => [
                $anchor->withText('<b>"Example" & \'more\'</b>'),
                '<a href="http://example.com">&lt;b&gt;&quot;Example&quot; &amp; &apos;more&apos;&lt;/b&gt;</a>',
            ],
            'text invalid utf-8' => [
                $anchor->withText("caf\xE9 menu"),
                "<a href=\"http://example.com\">caf\u{FFFD} menu</a>",
            ],
            'html not escaped' => [
                $anchor->withHtml('<svg class="icon"></svg> <b>Example</b>'),
                '<a href="http://example.com"><svg class="icon"></svg> <b>Example</b></a>',
            ],
            'text replaces html' => [
                $anchor->withHtml('<b>Old</b>')->withText('<b>New</b>'),
                '<a href="http://example.com">&lt;b&gt;New&lt;/b&gt;</a>',
            ],
            'html replaces text' => [
                $anchor->withText('Old')->withHtml('<b>New</b>'),
                '<a href="http://example.com"><b>New</b></a>',
            ],
            'attribute with value' => [
                $anchor->withAttribute('target', '_blank'),
                '<a href="http://example.com" target="_blank">example.com</a>',
            ],
            'attribute without value' => [
                $anchor->withAttribute('download'),
                '<a href="http://example.com" download>example.com</a>',
            ],
            'attribute order' => [
                $anchor->withAttribute('rel', 'nofollow')->withAttribute('data-x.y:z_1', '1'),
                '<a href="http://example.com" rel="nofollow" data-x.y:z_1="1">example.com</a>',
            ],
            'attribute replaced' => [
                $anchor->withAttribute('target', '_self')->withAttribute('target', '_blank'),
                '<a href="http://example.com" target="_blank">example.com</a>',
            ],
            'attribute name lowercased' => [
                $anchor->withAttribute('Data-ID', '1'),
                '<a href="http://example.com" data-id="1">example.com</a>',
            ],
            'attribute name case-insensitive' => [
                $anchor->withAttribute('class', 'a')->withAttribute('CLASS', 'b'),
                '<a href="http://example.com" class="b">example.com</a>',
            ],
            'attribute value escaped' => [
                $anchor->withAttribute('title', '"a" & \'b\' <c>'),
                '<a href="http://example.com" title="&quot;a&quot; &amp; &apos;b&apos; &lt;c&gt;">example.com</a>',
            ],
            'attribute invalid utf-8' => [
                $anchor->withAttribute('title', "caf\xE9 menu"),
                "<a href=\"http://example.com\" title=\"caf\u{FFFD} menu\">example.com</a>",
            ],
        ];
    }

    public function testToString(): void
    {
        $anchor = self::createAnchor(full: 'example.com', host: 'example.com')->withAttribute('rel', 'nofollow');
        $actual = (string) $anchor;
        self::assertSame($anchor->render(), $actual);
    }

    public function testImmutability(): void
    {
        $anchor = self::createAnchor(full: 'example.com', host: 'example.com');

        $anchor->withText('Example');
        $anchor->withHtml('<b>Example</b>');
        $anchor->withAttribute('target', '_blank');

        $actual = $anchor->render();
        self::assertSame('<a href="http://example.com">example.com</a>', $actual);
    }

    #[DataProvider('validAttributeNameDataProvider')]
    public function testWithAttributeValidName(string $name): void
    {
        $anchor = self::createAnchor(full: 'example.com', host: 'example.com');
        $actual = $anchor->withAttribute($name, 'value')->render();
        self::assertSame("<a href=\"http://example.com\" {$name}=\"value\">example.com</a>", $actual);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function validAttributeNameDataProvider(): array
    {
        return [
            'simple' => ['target'],
            'data attribute' => ['data-id'],
            'namespaced' => ['xlink:href'],
            'bind shorthand' => [':class'],
            'event shorthand' => ['@click'],
            'directive with modifiers' => ['x-on:click.prevent'],
            'starts with digit' => ['1abc'],
            'starts with dash' => ['-abc'],
            'cyrillic' => ['атрибут'],
            'c1 bytes in utf-8' => ['ім_я'],
            'chinese' => ['属性'],
            'emoji' => ['data-👾'],
            'noncharacter' => ["a\u{FFFE}b"],
        ];
    }

    #[DataProvider('invalidAttributeNameDataProvider')]
    public function testWithAttributeInvalidName(string $name): void
    {
        $anchor = self::createAnchor(full: 'example.com', host: 'example.com');
        $this->expectException(\InvalidArgumentException::class);
        $anchor->withAttribute($name, 'value');
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidAttributeNameDataProvider(): array
    {
        return [
            'empty' => [''],
            'space' => ['on click'],
            'tab' => ["on\tclick"],
            'non-breaking space' => ["on\u{A0}click"],
            'trailing newline' => ["target\n"],
            'null byte' => ["a\x00b"],
            'c0 control' => ["a\x01b"],
            'delete control' => ["a\x7Fb"],
            'c1 control first' => ["a\u{80}b"],
            'c1 control last' => ["a\u{9F}b"],
            'double quote injection' => ['x" onclick="alert(1)'],
            'single quote injection' => ["x' onclick='alert(1)"],
            'tag injection' => ['x><script>'],
            'less than sign' => ['a<b'],
            'equals sign' => ['a=b'],
            'slash' => ['a/b'],
            'invalid utf-8' => ["\xFF"],
        ];
    }

    private static function createAnchor(
        string $full,
        string $host,
        ?string $scheme = null,
        ?string $userinfo = null,
        ?int $port = null,
        ?string $path = null,
        ?string $query = null,
        ?string $fragment = null,
        string $fallbackScheme = 'http',
    ): Anchor {
        $url = new Url($full, $scheme, $userinfo, $host, $port, $path, $query, $fragment);
        return Anchor::fromUrl($url, $fallbackScheme);
    }
}
