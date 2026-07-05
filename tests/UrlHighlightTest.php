<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
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
        $actual = array_map(static fn (Url $url): string => $url->value, $matches);
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

    #[DataProvider('highlightDataProvider')]
    public function testHighlight(string $input, string $expected): void
    {
        $actual = $this->urlHighlight->highlight($input);
        self::assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function highlightDataProvider(): array
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
        ];
    }

    #[DataProvider('highlightEncodedDataProvider')]
    public function testHighlightEncoded(string $input, string $expected): void
    {
        $actual = $this->urlHighlight->highlight($input, isHtmlEncoded: true);
        self::assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function highlightEncodedDataProvider(): array
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
            'mixed encoded and raw html' => [
                '&lt;a href=&quot;http://example.com?q=query&quot;&gt;example.com?q=query&lt;/a&gt;'
                    . '<a href="http://example.com">example.com</a>',
                '&lt;a href=&quot;<a href="http://example.com?q=query">http://example.com?q=query</a>&quot;&gt;'
                    . '<a href="http://example.com?q=query">example.com?q=query</a>&lt;/a&gt;'
                    . '<a href="http://example.com">example.com</a>',
            ],
        ];
    }
}
