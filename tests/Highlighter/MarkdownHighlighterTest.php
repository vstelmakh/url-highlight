<?php

namespace VStelmakh\UrlHighlight\Tests\Highlighter;

use VStelmakh\UrlHighlight\Highlighter\MarkdownHighlighter;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Matcher\Match;

class MarkdownHighlighterTest extends TestCase
{
    /**
     * @dataProvider getHighlightDataProvider
     *
     * @param string $fullMatch
     * @param string $url
     * @param string|null $scheme
     * @param string $expected
     */
    public function testGetHighlight(
        string $fullMatch,
        string $url,
        ?string $scheme,
        ?string $expected
    ): void {
        $match = $this->createMock(Match::class);
        $match->method('getFullMatch')->willReturn($fullMatch);
        $match->method('getUrl')->willReturn($url);
        $match->method('getScheme')->willReturn($scheme);

        $markdownHighlighter = new MarkdownHighlighter('http');
        $actual = $markdownHighlighter->getHighlight($match);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return array&array[]
     */
    public function getHighlightDataProvider(): array
    {
        return [
            ['http://example.com', 'http://example.com', 'http', '[http://example.com](http://example.com)'],
            ['example.com', 'example.com', null, '[example.com](http://example.com)'],
            ['http://example.com/brackets[is]here', 'http://example.com/brackets[is]here', 'http', '[http://example.com/brackets\[is\]here](http://example.com/brackets[is]here)'],
            ['http://example.com/brackets(is)here', 'http://example.com/brackets(is)here', 'http', '[http://example.com/brackets(is)here](http://example.com/brackets%28is%29here)'],
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
        $htmlHighlighter = new MarkdownHighlighter('http');
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
                'Hello, [http://example.com](http://example.com).',
                'Hello, [http://example.com](http://example.com).',
            ],
            [
                'Hello, [[http://example.com](http://example.com)](http://example.com).',
                'Hello, [http://example.com](http://example.com).',
            ],
            [
                'Hello, [http://example.com]([http://example.com](http://example.com)).',
                'Hello, [http://example.com](http://example.com).',
            ],
            [
                'Hello, [[http://example.com](http://example.com)]([http://example.com](http://example.com)).',
                'Hello, [http://example.com](http://example.com).',
            ],
            [
                'Hello, [[http://example.com/brackets\[is\]here](http://example.com/brackets[is]here)]([http://example.com/brackets\[is\]here](http://example.com/brackets[is]here)).',
                'Hello, [http://example.com/brackets\[is\]here](http://example.com/brackets[is]here).',
            ],
        ];
    }
}
