<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests\Replacer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Highlighter\CallbackHighlighter;
use VStelmakh\UrlHighlight\Highlighter\Highlighter;
use VStelmakh\UrlHighlight\Replacer\Replacement;
use VStelmakh\UrlHighlight\Replacer\Replacer;
use VStelmakh\UrlHighlight\Replacer\Strategy\Strategy;
use VStelmakh\UrlHighlight\Url;

class ReplacerTest extends TestCase
{
    private Replacer $replacer;
    private Highlighter $highlighter;

    #[\Override]
    protected function setUp(): void
    {
        $this->replacer = new Replacer();
        $this->highlighter = new CallbackHighlighter(static fn (Url $url): string => "[{$url->full}]");
    }

    /**
     * @param array<Replacement> $replacements
     */
    #[DataProvider('replaceDataProvider')]
    public function testReplace(string $text, array $replacements, string $expected): void
    {
        $strategy = self::createStub(Strategy::class);
        $strategy->method('findReplacements')->willReturn($replacements);

        $actual = $this->replacer->replace($text, $this->highlighter, $strategy);

        self::assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{string, array<Replacement>, string}>
     */
    public static function replaceDataProvider(): array
    {
        return [
            'empty input' => [
                '',
                [],
                '',
            ],
            'no replacements leaves text unchanged' => [
                'Nothing here',
                [],
                'Nothing here',
            ],
            'replacement surrounded by text' => [
                'Visit example.com today',
                [self::replacement(6, 17, 'example.com')],
                'Visit [example.com] today',
            ],
            'replacement at the start' => [
                'example.com is worth a visit',
                [self::replacement(0, 11, 'example.com')],
                '[example.com] is worth a visit',
            ],
            'replacement at the end' => [
                'Today visit example.com',
                [self::replacement(12, 23, 'example.com')],
                'Today visit [example.com]',
            ],
            'adjacent replacements' => [
                'Visit a.comb.com today',
                [
                    self::replacement(6, 11, 'a.com'),
                    self::replacement(11, 16, 'b.com'),
                ],
                'Visit [a.com][b.com] today',
            ],
            'source span shorter than the url' => [
                'Visit example.com today',
                [self::replacement(6, 17, 'example.com/hello/world')],
                'Visit [example.com/hello/world] today',
            ],
            'source span longer than the url' => [
                'Visit example.com/hello/world today',
                [self::replacement(6, 29, 'example.com')],
                'Visit [example.com] today',
            ],
        ];
    }

    private static function replacement(int $start, int $end, string $output): Replacement
    {
        $url = new Url($output, null, null, 'irrelevant', null, null, null, null);
        return new Replacement($start, $end, $url);
    }
}
