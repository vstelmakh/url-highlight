<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Format;
use VStelmakh\UrlHighlight\Url;
use VStelmakh\UrlHighlight\UrlHighlight;

/**
 * End-to-end tests driving the public API to verify the whole library works together.
 * Per-component behavior is covered by the dedicated unit tests.
 */
class UrlHighlightTest extends TestCase
{
    private UrlHighlight $urlHighlight;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHighlight = new UrlHighlight();
    }

    /**
     * @param array<string> $expected
     */
    #[DataProvider('findDataProvider')]
    public function testFind(string $input, array $expected): void
    {
        $matches = $this->urlHighlight->find($input);
        $actual = array_map(static fn (Url $url): string => $url->full, $matches);
        self::assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{string, array<string>}>
     */
    public static function findDataProvider(): array
    {
        return [
            'multiple urls in text' => [
                'Example text before http://example.com/app.php/some/path/index.html and after.'
                    . ' Open filename.txt at 3:00pm. For more info see google.com.',
                ['http://example.com/app.php/some/path/index.html', 'google.com'],
            ],
            'email inside attribute' => [
                '<a href="mailto:hello@example.com">Example</a>',
                ['mailto:hello@example.com'],
            ],
            'bare email' => [
                'Contact user@example.com today.',
                ['user@example.com'],
            ],
            'no url' => [
                'not url',
                [],
            ],
        ];
    }

    public function testHighlightDefaultsToHtmlFormat(): void
    {
        $input = 'Skip <a href="http://example.com">example.com</a> link.';

        $actual = $this->urlHighlight->highlight($input);

        self::assertSame($input, $actual);
    }

    #[DataProvider('highlightPlainDataProvider')]
    public function testHighlightPlain(string $input, string $expected): void
    {
        $actual = $this->urlHighlight->highlight($input, format: Format::Plain);
        self::assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function highlightPlainDataProvider(): array
    {
        return [
            'scheme added to bare host' => [
                'Example text before example.com and after.',
                'Example text before <a href="http://example.com">example.com</a> and after.',
            ],
            'url enclosed in angle brackets' => [
                'Visit <https://example.com> now.',
                'Visit <<a href="https://example.com">https://example.com</a>> now.',
            ],
            'markup is not interpreted' => [
                '<my-widget data-url="http://example.com"></my-widget>',
                '<my-widget data-url="<a href="http://example.com">http://example.com</a>"></my-widget>',
            ],
            'script content is highlighted' => [
                'Skip <script>var u = "http://example.com";</script> end.',
                'Skip <script>var u = "<a href="http://example.com">http://example.com</a>";</script> end.',
            ],
        ];
    }

    #[DataProvider('highlightHtmlDataProvider')]
    public function testHighlightHtml(string $input, string $expected): void
    {
        $actual = $this->urlHighlight->highlight($input, format: Format::Html);
        self::assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function highlightHtmlDataProvider(): array
    {
        return [
            'absolute url' => [
                'Example text before http://example.com and after.',
                'Example text before <a href="http://example.com">http://example.com</a> and after.',
            ],
            'scheme added to bare host' => [
                'Example text before example.com and after.',
                'Example text before <a href="http://example.com">example.com</a> and after.',
            ],
            'url inside html element' => [
                'With html <p>example.com</p>',
                'With html <p><a href="http://example.com">example.com</a></p>',
            ],
            'email gets mailto scheme' => [
                'With email user@example.com.',
                'With email <a href="mailto:user@example.com">user@example.com</a>.',
            ],
            'existing link is left untouched' => [
                'Skip <a href="http://example.com">example.com</a> link.',
                'Skip <a href="http://example.com">example.com</a> link.',
            ],
            'script content is not highlighted' => [
                'Skip <script>var u = "http://example.com";</script> end.',
                'Skip <script>var u = "http://example.com";</script> end.',
            ],
            'url enclosed in angle brackets' => [
                'Visit <https://example.com> now.',
                'Visit <https://example.com> now.',
            ],
            'custom tag attribute is not highlighted' => [
                '<my-widget data-url="http://example.com"></my-widget>',
                '<my-widget data-url="http://example.com"></my-widget>',
            ],
        ];
    }

    #[DataProvider('highlightHtmlEncodedDataProvider')]
    public function testHighlightHtmlEncoded(string $input, string $expected): void
    {
        $actual = $this->urlHighlight->highlight($input, format: Format::HtmlEncoded);
        self::assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function highlightHtmlEncodedDataProvider(): array
    {
        return [
            'plain text in encoded mode' => [
                'Plain text http://example.com here.',
                'Plain text <a href="http://example.com">http://example.com</a> here.',
            ],
            'encoded entity preserved verbatim' => [
                'Encoded amp http://example.com?a=1&amp;b=2 done.',
                'Encoded amp <a href="http://example.com?a=1&amp;b=2">http://example.com?a=1&amp;b=2</a> done.',
            ],
            'url matched inside encoded attribute and link text' => [
                '&lt;a href=&quot;http://example.com?q=query&quot;&gt;example.com?q=query&lt;/a&gt;',
                '&lt;a href=&quot;<a href="http://example.com?q=query">http://example.com?q=query</a>&quot;&gt;'
                    . '<a href="http://example.com?q=query">example.com?q=query</a>&lt;/a&gt;',
            ],
            'url enclosed in escaped angle brackets' => [
                'Visit &lt;example.com&gt; now.',
                'Visit &lt;<a href="http://example.com">example.com</a>&gt; now.',
            ],
            'mixed encoded and raw html' => [
                '&lt;a href=&quot;http://example.com?q=query&quot;&gt;example.com?q=query&lt;/a&gt;'
                    . '<a href="http://example.com">example.com</a>',
                '&lt;a href=&quot;<a href="http://example.com?q=query">http://example.com?q=query</a>&quot;&gt;'
                    . '<a href="http://example.com?q=query">example.com?q=query</a>&lt;/a&gt;'
                    . '<a href="http://example.com">example.com</a>',
            ],

            'url enclosed in escaped angle brackets with quotes' => [
                'Visit &lt;example.com?q="hello+world"&gt; now.',
                'Visit &lt;<a href="http://example.com?q=&quot;hello+world&quot;">example.com?q=&quot;hello+world&quot;</a>&gt; now.',
            ],
            'url with angle bracket in path' => [
                'Visit http://example.com/a&gt;b now.',
                'Visit <a href="http://example.com/a&gt;b">http://example.com/a&gt;b</a> now.',
            ],
        ];
    }
}
