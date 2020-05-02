<?php

namespace VStelmakh\UrlHighlight\Tests\Highlighter;

use PHPUnit\Framework\MockObject\MockObject;
use VStelmakh\UrlHighlight\Highlighter\HtmlSpecialCharsHighlighter;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Match;
use VStelmakh\UrlHighlight\Matcher;

class HtmlEntityHighlighterTest extends TestCase
{
    /**
     * @var Matcher&MockObject
     */
    private $matcher;

    /**
     * @var HtmlSpecialCharsHighlighter
     */
    private $highlighter;

    public function setUp(): void
    {
        $this->matcher = $this->createMock(Matcher::class);
        $this->highlighter = new HtmlSpecialCharsHighlighter($this->matcher, 'http');
    }

    /**
     * @dataProvider highlightUrlsDataProvider
     *
     * @param string $string
     * @param array|Match[] $matches
     * @param string $expected
     */
    public function testHighlightUrls(string $string, array $matches, string $expected): void
    {
        $this->matcher
            ->expects($this->once())
            ->method('matchAll')
            ->willReturn($matches);

        $actual = $this->highlighter->highlightUrls($string);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return array|array[]
     */
    public function highlightUrlsDataProvider(): array
    {
        return [
            'decoded' => [
                'Hello ★, follow the link: http://example.com.',
                [new Match('http://example.com', 'http', null, null, null, 28)],
                'Hello ★, follow the link: <a href="http://example.com">http://example.com</a>.',
            ],
            'encoded' => [
                'Hello ★, follow the link: &lt;a id=&quot;example&quot; class=&quot;link&quot; href=&quot;http://example.com?a=1&amp;b=2#anchor&quot; title=&quot;Example&quot;&gt;example.com&lt;/a&gt;.',
                [
                    new Match('http://example.com?a=1&b=2#anchor', 'http', null, null, null, 63),
                    new Match('example.com', null, null, 'example.com', 'com', 114),
                ],
                'Hello ★, follow the link: &lt;a id=&quot;example&quot; class=&quot;link&quot; href=&quot;<a href="http://example.com?a=1&b=2#anchor">http://example.com?a=1&amp;b=2#anchor</a>&quot; title=&quot;Example&quot;&gt;<a href="http://example.com">example.com</a>&lt;/a&gt;.',
            ],
            'no char before' => [
                'http://example.com&lt;br&gt;text after',
                [new Match('http://example.com', 'http', null, null, null, 0)],
                '<a href="http://example.com">http://example.com</a>&lt;br&gt;text after',
            ],
            'no char after' => [
                'text before&lt;br&gt;http://example.com',
                [new Match('http://example.com', 'http', null, null, null, 15)],
                'text before&lt;br&gt;<a href="http://example.com">http://example.com</a>',
            ],
            'no char before and after' => [
                'http://example.com',
                [new Match('http://example.com', 'http', null, null, null, 0)],
                '<a href="http://example.com">http://example.com</a>',
            ],
            'no match' => [
                'Just a text',
                [],
                'Just a text',
            ],
        ];
    }
}
