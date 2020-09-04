<?php

namespace VStelmakh\UrlHighlight\Tests\Replacer;

use PHPUnit\Framework\MockObject\MockObject;
use VStelmakh\UrlHighlight\Matcher\Match;
use VStelmakh\UrlHighlight\Matcher\MatcherInterface;
use VStelmakh\UrlHighlight\Replacer\Replacer;
use PHPUnit\Framework\TestCase;

class ReplacerTest extends TestCase
{
    private const REPLACE = 'REPLACE';

    /**
     * @var MatcherInterface&MockObject
     */
    private $matcher;

    /**
     * @var Replacer
     */
    private $replacer;

    protected function setUp(): void
    {
        $this->matcher = $this->createMock(MatcherInterface::class);
        $this->replacer = new Replacer($this->matcher);
    }

    /**
     * @dataProvider replaceCallbackDataProvider
     *
     * @param string $string
     * @param array&Match[] $matches
     * @param string $expected
     */
    public function testReplaceCallback(string $string, array $matches, string $expected): void
    {
        $this->matcher
            ->expects(self::once())
            ->method('matchAll')
            ->willReturn($matches);

        $callback = static function (Match $match) {
            return self::REPLACE;
        };

        $actual = $this->replacer->replaceCallback($string, $callback);
        self::assertEquals($expected, $actual);
    }

    /**
     * @return array&array[]
     */
    public function replaceCallbackDataProvider(): array
    {
        return [
            [
                'Hello ★, follow the link: http://example.com.',
                [
                    new Match('http://example.com', 28, 'http://example.com', 'http', null, 'example.com', 'com', null, null),
                ],
                sprintf('Hello ★, follow the link: %s.', self::REPLACE)
            ],
            [
                'Hello ★, follow the link: http://example.com/互联网. Привіт світ (example.com).',
                [
                    new Match('http://example.com/互联网', 28, 'http://example.com/互联网', 'http', null, 'example.com', 'com', null, '/互联网'),
                    new Match('example.com', 81, 'example.com', null, null, 'example.com', 'com', null, null),
                ],
                sprintf('Hello ★, follow the link: %s. Привіт світ (%s).', self::REPLACE, self::REPLACE)
            ],
        ];
    }
}
