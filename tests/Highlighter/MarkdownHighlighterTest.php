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
     * @param Match $match
     * @param string|null $expected
     */
    public function testGetHighlight(Match $match, ?string $expected): void
    {
        $markdownHighlighter = new MarkdownHighlighter('http');
        $actual = $markdownHighlighter->getHighlight($match);
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
                '[http://example.com](http://example.com)',
            ],
            [
                new Match('example.com', 0, 'example.com', null, null, 'example.com', 'com', null, null),
                '[example.com](http://example.com)',
            ],
            [
                new Match('mailto:user@example.com', 0, 'mailto:user@example.com', 'mailto', 'user', 'example.com', 'com', null, null),
                '[mailto:user@example.com](mailto:user@example.com)',
            ],
            [
                new Match('user@example.com', 0, 'user@example.com', null, 'user', 'example.com', 'com', null, null),
                '[user@example.com](mailto:user@example.com)',
            ],
            [
                new Match('http://example.com/brackets[is]here', 0, 'http://example.com/brackets[is]here', 'http', null, 'example.com', 'com', null, '/brackets[is]here'),
                '[http://example.com/brackets\[is\]here](http://example.com/brackets[is]here)',
            ],
            [
                new Match('http://example.com/brackets(is)here', 0, 'http://example.com/brackets(is)here', 'http', null, 'example.com', 'com', null, '/brackets(is)here'),
                '[http://example.com/brackets(is)here](http://example.com/brackets%28is%29here)',
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
        $htmlHighlighter = new MarkdownHighlighter('http');
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
