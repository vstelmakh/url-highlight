<?php

namespace VStelmakh\UrlHighlight\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Highlighter;
use VStelmakh\UrlHighlight\Match;
use VStelmakh\UrlHighlight\Matcher;

class HighlighterTest extends TestCase
{
    /**
     * @var Matcher|MockObject
     */
    private $matcher;

    public function setUp(): void
    {
        $this->matcher = $this->createMock(Matcher::class);
    }

    /**
     * @dataProvider highlightUrlsDataProvider
     */
    public function testHighlightUrls(string $input, string $matcherResult, string $expected): void
    {
        $highlighter = new Highlighter($this->matcher, 'http');

        $this->matcher
            ->expects($this->once())
            ->method('replaceCallback')
            ->with($this->equalTo($input), $this->identicalTo([$highlighter, 'getMatchAsHighlight']))
            ->willReturn($matcherResult);

        $actual = $highlighter->highlightUrls($input);
        $this->assertSame($expected, $actual);
    }

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
     */
    public function testGetMatchAsHighlight(array $matchData, string $expected): void
    {
        $match = $this->createMock(Match::class);
        foreach ($matchData as $methodName => $returnValue) {
            $match->method($methodName)->willReturn($returnValue);
        }

        $highlighter = new Highlighter($this->matcher, 'http');
        $actual = $highlighter->getMatchAsHighlight($match);
        $this->assertSame($expected, $actual);
    }

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
