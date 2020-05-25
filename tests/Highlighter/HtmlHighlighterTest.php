<?php

namespace VStelmakh\UrlHighlight\Tests\Highlighter;

use VStelmakh\UrlHighlight\Highlighter\HtmlHighlighter;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Matcher\Match;

class HtmlHighlighterTest extends TestCase
{
    /**
     * @dataProvider getHighlightDataProvider
     *
     * @param string $fullMatch
     * @param string $url
     * @param string|null $scheme
     * @param array&string[] $attributes
     * @param string $expected
     */
    public function testGetHighlight(
        string $fullMatch,
        string $url,
        ?string $scheme,
        array $attributes,
        ?string $expected
    ): void {
        $match = $this->createMock(Match::class);
        $match->method('getFullMatch')->willReturn($fullMatch);
        $match->method('getUrl')->willReturn($url);
        $match->method('getScheme')->willReturn($scheme);

        if ($expected === null) {
            $this->expectException(\InvalidArgumentException::class);
        }

        $htmlHighlighter = new HtmlHighlighter('http', $attributes);
        $actual = $htmlHighlighter->getHighlight($match);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return array&array[]
     */
    public function getHighlightDataProvider(): array
    {
        return [
            ['http://example.com', 'http://example.com', 'http', [], '<a href="http://example.com">http://example.com</a>'],
            ['example.com', 'example.com', null, [], '<a href="http://example.com">example.com</a>'],
            ['http://example.com?a=&quot;1&quot;&amp;b=2', 'http://example.com?a="1"&b=2', 'http', [], '<a href="http://example.com?a=%221%22&b=2">http://example.com?a=&quot;1&quot;&amp;b=2</a>'],
            ['http://example.com', 'http://example.com', 'http', ['rel' => 'nofollow', 'title' => '"quotes"'], '<a href="http://example.com" rel="nofollow" title="&quot;quotes&quot;">http://example.com</a>'],
            ['http://example.com', 'http://example.com', 'http', ['"quotes"' => 'value'], null],
        ];
    }

    /**
     * @dataProvider filterOverhighlightDataProvider
     *
     * @param string $string
     * @param string $expected
     */
    public function testFilterOverhighlight(string $string, string $expected): void
    {
        $htmlHighlighter = new HtmlHighlighter('http');
        $actual = $htmlHighlighter->filterOverhighlight($string);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return array&array[]
     */
    public function filterOverhighlightDataProvider(): array
    {
        return [
            [
                'Hello, <a href="http://example.com">http://example.com</a>.',
                'Hello, <a href="http://example.com">http://example.com</a>.',
            ],
            [
                'Hello, <a href="<a href="http://example.com">http://example.com</a>"><a href="http://example.com">http://example.com</a></a>.',
                'Hello, <a href="http://example.com">http://example.com</a>.',
            ],
            [
                'Hello, <a id="example" class="link" href="<a href="http://example.com">http://example.com</a>" title="Example"><a href="http://example.com">http://example.com</a></a>.',
                'Hello, <a id="example" class="link" href="http://example.com" title="Example">http://example.com</a>.',
            ],
            [
                'Hello, <a href="<a href="http://example.com">http://example.com</a>"><a href="http://example.com">http://example.com</a></a> and <a href="https://google.com">https://google.com</a>.',
                'Hello, <a href="http://example.com">http://example.com</a> and <a href="https://google.com">https://google.com</a>.',
            ],
            [
                '<img src="<a href="http://example.com/image.png">http://example.com/image.png</a>" alt="example">',
                '<img src="http://example.com/image.png" alt="example">',
            ],
        ];
    }
}
