<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\UrlHighlight;

class UrlHighlightTest extends TestCase
{
    private readonly UrlHighlight $urlHighlight;

    protected function setUp(): void
    {
        $this->urlHighlight = new UrlHighlight();
    }

    #[DataProvider('isUrlDataProvider')]
    public function testIsUrl(string $string, bool $expected): void
    {
        $actual = $this->urlHighlight->isUrl($string);
        self::assertEquals($expected, $actual);
    }

    public static function isUrlDataProvider(): array
    {
        return [
            ['http://example.com', true],
            ['example.com', true],
            ['file.txt', false],
            ['random text', false],
        ];
    }

    #[DataProvider('getUrlsDataProvider')]
    public function testGetUrls(string $string, array $expected): void
    {
        $actual = $this->urlHighlight->getUrls($string);
        self::assertEquals($expected, $actual);
    }

    public static function getUrlsDataProvider(): array
    {
        return [
            [
                'Example text before http://example.com/app.php/some/path/index.html and after. Open filename.txt at 3:00pm. For more info see google.com.',
                ['http://example.com/app.php/some/path/index.html', 'google.com'],
            ],
            [
                '<a href="mailto:hello@example.com">Example</a>',
                ['mailto:hello@example.com'],
            ],
            [
                'not url',
                [],
            ],
        ];
    }

    #[DataProvider('highlightUrlsDataProvider')]
    public function testHighlightUrls(string $string, string $expected): void
    {
        $actual = $this->urlHighlight->highlightUrls($string);
        self::assertEquals($expected, $actual);
    }

    public static function highlightUrlsDataProvider(): array
    {
        return [
            [
                'Example text before http://example.com and after.',
                'Example text before <a href="http://example.com">http://example.com</a> and after.',
            ],
            [
                'With html <p>http://example.com</p>',
                'With html <p><a href="http://example.com">http://example.com</a></p>',
            ],
            [
                'Example text before example.com and after.',
                'Example text before <a href="http://example.com">example.com</a> and after.',
            ],
            [
                'With html <p>example.com</p>',
                'With html <p><a href="http://example.com">example.com</a></p>',
            ],
            [
                'With html <p>http://example.com and links <a href="http://example.com">http://example.com</a></p>',
                'With html <p><a href="http://example.com">http://example.com</a> and links <a href="http://example.com">http://example.com</a></p>',
            ],
            [
                'With email user@example.com.',
                'With email <a href="mailto:user@example.com">user@example.com</a>.',
            ],
            [
                '&lt;a href=&quot;http://example.com?q=query&quot;&gt;example.com?q=query&lt;/a&gt;',
                '&lt;a href=&quot;<a href="http://example.com?q=query&quot;&gt;example.com?q=query&lt;/a&gt">http://example.com?q=query&quot;&gt;example.com?q=query&lt;/a&gt</a>;',
            ],
            [
                '&lt;a href=&quot;http://example.com?q=query&quot;&gt;example.com?q=query&lt;/a&gt;',
                '&lt;a href=&quot;<a href="http://example.com?q=query">http://example.com?q=query</a>&quot;&gt;<a href="http://example.com?q=query">example.com?q=query</a>&lt;/a&gt;',
            ],
        ];
    }
}
