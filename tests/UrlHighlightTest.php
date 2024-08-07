<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests;

use VStelmakh\UrlHighlight\Encoder\EncoderInterface;
use VStelmakh\UrlHighlight\Encoder\HtmlSpecialcharsEncoder;
use VStelmakh\UrlHighlight\Highlighter\HighlighterInterface;
use VStelmakh\UrlHighlight\UrlHighlight;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Validator\ValidatorInterface;

class UrlHighlightTest extends TestCase
{
    /**
     * @dataProvider isUrlDataProvider
     *
     * @param string $string
     * @param bool $expected
     */
    public function testIsUrl(string $string, bool $expected): void
    {
        $urlHighlight = new UrlHighlight();
        $actual = $urlHighlight->isUrl($string);
        self::assertEquals($expected, $actual, 'Expected ' . ($expected ? '"true"' : '"false"') . ' for: ' . $string);
    }

    /**
     * @return mixed[]
     */
    public function isUrlDataProvider(): array
    {
        return [
            ['http://example.com', true],
            ['example.com', true],
            ['not url', false],
        ];
    }

    /**
     * @dataProvider getUrlsDataProvider
     *
     * @param string $string
     * @param array<string, string[]> $expected
     */
    public function testGetUrls(string $string, array $expected): void
    {
        $urlHighlight = new UrlHighlight();
        $actual = $urlHighlight->getUrls($string);
        self::assertEquals($expected, $actual, 'Input: ' . $string);
    }

    /**
     * @return mixed[]
     */
    public function getUrlsDataProvider(): array
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

    /**
     * @dataProvider highlightUrlsDataProvider
     *
     * @param EncoderInterface|null $encoder
     * @param string $string
     * @param string $expected
     */
    public function testHighlightUrls(?EncoderInterface $encoder, string $string, string $expected): void
    {
        $urlHighlight = new UrlHighlight(null, null, $encoder);

        $actual = $urlHighlight->highlightUrls($string);
        self::assertEquals($expected, $actual);
    }

    /**
     * @return mixed[]
     */
    public function highlightUrlsDataProvider(): array
    {
        return [
            [
                null,
                'Example text before http://example.com and after.',
                'Example text before <a href="http://example.com">http://example.com</a> and after.',
            ],
            [
                null,
                'With html <p>http://example.com</p>',
                'With html <p><a href="http://example.com">http://example.com</a></p>',
            ],
            [
                null,
                'Example text before example.com and after.',
                'Example text before <a href="http://example.com">example.com</a> and after.',
            ],
            [
                null,
                'With html <p>example.com</p>',
                'With html <p><a href="http://example.com">example.com</a></p>',
            ],
            [
                null,
                'With html <p>http://example.com and links <a href="http://example.com">http://example.com</a></p>',
                'With html <p><a href="http://example.com">http://example.com</a> and links <a href="http://example.com">http://example.com</a></p>',
            ],
            [
                null,
                'With email user@example.com.',
                'With email <a href="mailto:user@example.com">user@example.com</a>.',
            ],
            [
                null,
                '&lt;a href=&quot;http://example.com?q=query&quot;&gt;example.com?q=query&lt;/a&gt;',
                '&lt;a href=&quot;<a href="http://example.com?q=query&quot;&gt;example.com?q=query&lt;/a&gt">http://example.com?q=query&quot;&gt;example.com?q=query&lt;/a&gt</a>;',
            ],
            [
                new HtmlSpecialcharsEncoder(),
                '&lt;a href=&quot;http://example.com?q=query&quot;&gt;example.com?q=query&lt;/a&gt;',
                '&lt;a href=&quot;<a href="http://example.com?q=query">http://example.com?q=query</a>&quot;&gt;<a href="http://example.com?q=query">example.com?q=query</a>&lt;/a&gt;',
            ],
        ];
    }

    public function testDependencies(): void
    {
        $string = 'http://example.com';

        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->expects(self::atLeastOnce())
            ->method('isValidMatch')
            ->willReturn(true);

        $highlighter = $this->createMock(HighlighterInterface::class);
        $highlighter
            ->expects(self::once())
            ->method('highlight');

        $encoder = $this->createMock(EncoderInterface::class);
        $encoder
            ->expects(self::atLeastOnce())
            ->method('decode')
            ->willReturn($string);

        $urlHighlight = new UrlHighlight($validator, $highlighter, $encoder);
        $urlHighlight->isUrl($string);
        $urlHighlight->getUrls($string);
        $urlHighlight->highlightUrls($string);
    }
}
