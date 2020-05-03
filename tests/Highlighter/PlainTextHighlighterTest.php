<?php

namespace VStelmakh\UrlHighlight\Tests\Highlighter;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Highlighter\PlainTextHighlighter;
use VStelmakh\UrlHighlight\Matcher\Match;
use VStelmakh\UrlHighlight\Matcher\Matcher;

class PlainTextHighlighterTest extends TestCase
{
    /**
     * @var Matcher&MockObject
     */
    private $matcher;

    /**
     * @var PlainTextHighlighter
     */
    private $highlighter;

    public function setUp(): void
    {
        $this->matcher = $this->createMock(Matcher::class);
        $this->highlighter = new PlainTextHighlighter($this->matcher, 'http');
    }

    /**
     * @dataProvider highlightUrlsDataProvider
     */
    public function testHighlightUrls(string $input, string $matcherResult, string $expected): void
    {
        $this->matcher
            ->expects($this->once())
            ->method('replaceCallback')
            ->with($this->equalTo($input), $this->identicalTo([$this->highlighter, 'getMatchAsHighlight']))
            ->willReturn($matcherResult);

        $actual = $this->highlighter->highlightUrls($input);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return array|array[]
     */
    public function highlightUrlsDataProvider(): array
    {
        return [
            [
                'Hello, http://example.com.',
                'Hello, <a href="http://example.com">http://example.com</a>.',
                'Hello, <a href="http://example.com">http://example.com</a>.',
            ],
            [
                'Hello, <a href="http://example.com">http://example.com</a>.',
                'Hello, <a href="<a href="http://example.com">http://example.com</a>"><a href="http://example.com">http://example.com</a></a>.',
                'Hello, <a href="http://example.com">http://example.com</a>.',
            ],
            [
                'Hello, <a id="example" class="link" href="http://example.com" title="Example">http://example.com</a>.',
                'Hello, <a id="example" class="link" href="<a href="http://example.com">http://example.com</a>" title="Example"><a href="http://example.com">http://example.com</a></a>.',
                'Hello, <a id="example" class="link" href="http://example.com" title="Example">http://example.com</a>.',
            ],
            [
                'Hello, <a href="http://example.com">http://example.com</a> and https://google.com.',
                'Hello, <a href="<a href="http://example.com">http://example.com</a>"><a href="http://example.com">http://example.com</a></a> and <a href="https://google.com">https://google.com</a>.',
                'Hello, <a href="http://example.com">http://example.com</a> and <a href="https://google.com">https://google.com</a>.',
            ],
        ];
    }

    /**
     * @dataProvider getMatchAsHighlightDataProvider
     *
     * @param array|array[] $matchData
     * @param string $expected
     */
    public function testGetMatchAsHighlight(array $matchData, string $expected): void
    {
        $match = $this->createMock(Match::class);
        foreach ($matchData as $methodName => $returnValue) {
            $match->method($methodName)->willReturn($returnValue);
        }

        $actual = $this->highlighter->getMatchAsHighlight($match);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return array|array[]
     */
    public function getMatchAsHighlightDataProvider(): array
    {
        return [
            [
                ['getFullMatch' => 'example.com', 'getScheme' => null],
                '<a href="http://example.com">example.com</a>',
            ],
            [
                ['getFullMatch' => 'https://example.com', 'getScheme' => 'https'],
                '<a href="https://example.com">https://example.com</a>',
            ],
        ];
    }
}
