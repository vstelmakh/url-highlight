<?php

namespace VStelmakh\UrlHighlight\Tests\Matcher;

use PHPUnit\Framework\MockObject\MockObject;
use VStelmakh\UrlHighlight\Encoder\EncoderInterface;
use VStelmakh\UrlHighlight\Highlighter\HtmlHighlighter;
use VStelmakh\UrlHighlight\Matcher\EncodedMatcher;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Matcher\Match;
use VStelmakh\UrlHighlight\Matcher\Matcher;

class EncodedMatcherTest extends TestCase
{
    /**
     * @var Matcher&MockObject
     */
    private $matcher;

    /**
     * @var EncoderInterface&MockObject
     */
    private $encoder;

    /**
     * @var EncodedMatcher
     */
    private $encodedMatcher;

    protected function setUp(): void
    {
        $this->matcher = $this->createMock(Matcher::class);
        $this->encoder = $this->createMock(EncoderInterface::class);
        $this->encodedMatcher = new EncodedMatcher($this->matcher, $this->encoder);
    }

    /**
     * @dataProvider matchDataProvider
     *
     * @param string $string
     * @param string $decoded
     * @param Match|null $match
     * @param Match|null $expected
     */
    public function testMatch(string $string, string $decoded, ?Match $match, ?Match $expected): void
    {
        $this->encoder
            ->expects($this->once())
            ->method('decode')
            ->with($this->identicalTo($string))
            ->willReturn($decoded);

        $this->matcher
            ->expects($this->once())
            ->method('match')
            ->with($this->identicalTo($decoded))
            ->willReturn($match);

        $actual = $this->encodedMatcher->match($string);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array&array[]
     */
    public function matchDataProvider(): array
    {
        return [
            [
                'http://example&period;com?a=1&amp;b=2',
                'http://example.com?a=1&b=2',
                new Match('http://example.com?a=1&b=2', 0, 'http://example.com?a=1&b=2', 'http', null, null, null),
                new Match('http://example&period;com?a=1&amp;b=2', 0, 'http://example.com?a=1&b=2', 'http', null, null, null),
            ],
            [
                '&lgt;http://example&period;com?a=1&amp;b=2',
                '<http://example.com?a=1&b=2',
                null,
                null,
            ],
        ];
    }

    /**
     * @dataProvider matchAllDataProvider
     *
     * @param string $string
     * @param string $decoded
     * @param string[]|null $supportedChars
     * @param array&Match[] $matches
     * @param array&Match[] $expected
     */
    public function testMatchAll(
        string $string,
        string $decoded,
        ?array $supportedChars,
        array $matches,
        array $expected
    ): void {
        $this->encoder
            ->expects($this->once())
            ->method('decode')
            ->with($this->identicalTo($string))
            ->willReturn($decoded);

        $this->encoder
            ->method('getSupportedChars')
            ->willReturn($supportedChars);

        $this->encoder
            ->method('getEncodedCharRegex')
            ->willReturnMap([
                ['h', '/', 'h'],
                ['t', '/', 't'],
                ['p', '/', 'p'],
                [':', '/', ':'],
                ['/', '/', '\/'],
                ['e', '/', 'e'],
                ['x', '/', 'x'],
                ['a', '/', 'a'],
                ['m', '/', 'm'],
                ['l', '/', 'l'],
                ['.', '/', '\.|&period;|&#x2e;'],
                ['c', '/', 'c'],
                ['o', '/', 'o'],
                ['&', '/', '&|&amp;'],
                ['<', '/', '<|&lt;'],
                ['>', '/', '>|&gt;'],
                ['"', '/', '"|&quot;'],
            ]);

        $this->matcher
            ->expects($this->once())
            ->method('matchAll')
            ->with($this->identicalTo($decoded))
            ->willReturn($matches);

        $actual = $this->encodedMatcher->matchAll($string);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array&array[]
     */
    public function matchAllDataProvider(): array
    {
        return [
            [
                '&lt;a href&equals;&quot;http://example&period;com&quot;&gt;example&#x2E;com&lt;&sol;a&gt;',
                '<a href="http://example.com">example.com</a>',
                null,
                [
                    new Match('http://example.com', 9, 'http://example.com', 'http', null, null, null),
                    new Match('example.com', 29, 'example.com', null, null, 'example.com', 'com'),
                ],
                [
                    new Match('http://example&period;com', 24, 'http://example.com', 'http', null, null, null),
                    new Match('example&#x2E;com', 59, 'example.com', null, null, 'example.com', 'com'),
                ],
            ],
            [
                '&lt;a href=&quot;http://example.com?a=1&amp;b=2&quot;&gt;Example&lt;/a&gt;',
                '<a href="http://example.com?a=1&b=2">Example</a>',
                ['<', '>', '"', '&'],
                [
                    new Match('http://example.com?a=1&b=2', 9, 'http://example.com?a=1&b=2', 'http', null, null, null),
                ],
                [
                    new Match('http://example.com?a=1&amp;b=2', 17, 'http://example.com?a=1&b=2', 'http', null, null, null),
                ],
            ],
        ];
    }

    /**
     * @dataProvider replaceCallbackDataProvider
     *
     * @param string $string
     * @param string $decoded
     * @param array&Match[] $matches
     * @param array&string[] $highlights
     * @param string $expected
     */
    public function testReplaceCallback(string $string, string $decoded, array $matches, array $highlights, string $expected): void
    {
        $this->encoder
            ->expects($this->once())
            ->method('decode')
            ->with($this->identicalTo($string))
            ->willReturn($decoded);

        $this->encoder
            ->method('getSupportedChars')
            ->willReturn(null);

        $this->encoder
            ->method('getEncodedCharRegex')
            ->willReturnMap([
                ['h', '/', 'h'],
                ['t', '/', 't'],
                ['p', '/', 'p'],
                [':', '/', ':'],
                ['/', '/', '\/'],
                ['e', '/', 'e'],
                ['x', '/', 'x'],
                ['a', '/', 'a'],
                ['m', '/', 'm'],
                ['l', '/', 'l'],
                ['.', '/', '\.|&period;|&#x2e;'],
                ['c', '/', 'c'],
                ['o', '/', 'o'],
                ['&', '/', '&|&amp;'],
                ['<', '/', '<|&lt;'],
                ['>', '/', '>|&gt;'],
                ['"', '/', '"|&quot;'],
            ]);

        $this->matcher
            ->expects($this->once())
            ->method('matchAll')
            ->with($this->identicalTo($decoded))
            ->willReturn($matches);

        $highlighter = $this->createMock(HtmlHighlighter::class);
        $highlighter
            ->expects($this->exactly(count($highlights)))
            ->method('getHighlight')
            ->willReturnOnConsecutiveCalls(...$highlights);

        $actual = $this->encodedMatcher->replaceCallback($string, [$highlighter, 'getHighlight']);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array&array[]
     */
    public function replaceCallbackDataProvider(): array
    {
        return [
            [
                'Hello ★, follow the link: http://example.com.',
                'Hello ★, follow the link: http://example.com.',
                [
                    new Match('http://example.com', 28, 'http://example.com', 'http', null, null, null),
                ],
                [
                    '<a href="http://example.com">http://example.com</a>',
                ],
                'Hello ★, follow the link: <a href="http://example.com">http://example.com</a>.'
            ],
            [
                'Hello ★, follow the link: &lt;a href=&quot;http://example.com&quot;&gt;example.com&lt;/a&gt;.',
                'Hello ★, follow the link: <a href="http://example.com">example.com</a>.',
                [
                    new Match('http://example.com', 45, 'http://example.com', 'http', null, null, null),
                    new Match('example.com', 73, 'example.com', null, null, 'example.com', 'com'),
                ],
                [
                    '<a href="http://example.com">http://example.com</a>',
                    '<a href="http://example.com">example.com</a>',
                ],
                'Hello ★, follow the link: &lt;a href=&quot;<a href="http://example.com">http://example.com</a>&quot;&gt;<a href="http://example.com">example.com</a>&lt;/a&gt;.'
            ],
        ];
    }
}
