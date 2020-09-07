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
     * @param Match $match
     * @param array&string[] $attributes
     * @param string|null $expected
     */
    public function testGetHighlight(Match $match, array $attributes, ?string $expected): void
    {
        if ($expected === null) {
            $this->expectException(\InvalidArgumentException::class);
        }

        $htmlHighlighter = new HtmlHighlighter('http', $attributes);
        $actual = $htmlHighlighter->getHighlight($match);
        self::assertSame($expected, $actual);
    }

    /**
     * @return array&array[]
     */
    public function getHighlightDataProvider(): array
    {
        return [
            [
                new Match('http://example.com', 0, 'http://example.com', 'http', null, 'example.com', 'com', null, null),
                [],
                '<a href="http://example.com">http://example.com</a>',
            ],
            [
                new Match('example.com', 0, 'example.com', null, null, 'example.com', 'com', null, null),
                [],
                '<a href="http://example.com">example.com</a>',
            ],
            [
                new Match('mailto:user@example.com', 0, 'mailto:user@example.com', 'mailto', 'user', 'example.com', 'com', null, null),
                [],
                '<a href="mailto:user@example.com">mailto:user@example.com</a>',
            ],
            [
                new Match('user@example.com', 0, 'user@example.com', null, 'user', 'example.com', 'com', null, null),
                [],
                '<a href="mailto:user@example.com">user@example.com</a>',
            ],
            [
                new Match('http://example.com?a=&quot;1&quot;&amp;b=2', 0, 'http://example.com?a="1"&b=2', 'http', null, 'example.com', 'com', null, '?a="1"&b=2'),
                [],
                '<a href="http://example.com?a=%221%22&b=2">http://example.com?a=&quot;1&quot;&amp;b=2</a>',
            ],
            [
                new Match('http://example.com', 0, 'http://example.com', 'http', null, 'example.com', 'com', null, null),
                ['rel' => 'nofollow', 'title' => '"quotes"'],
                '<a href="http://example.com" rel="nofollow" title="&quot;quotes&quot;">http://example.com</a>',
            ],
            [
                new Match('http://example.com', 0, 'http://example.com', 'http', null, 'example.com', 'com', null, null),
                ['"quotes"' => 'value'],
                null,
            ],
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
        self::assertSame($expected, $actual);
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
            [
                'Hello, <a href="mailto:user@example.com">mail us user@example.com</a>.',
                'Hello, <a href="mailto:user@example.com">mail us user@example.com</a>.',
            ],
        ];
    }
}
