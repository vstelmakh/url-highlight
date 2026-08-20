<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests\Replacer\Strategy;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Matcher\Matcher;
use VStelmakh\UrlHighlight\Replacer\Replacement;
use VStelmakh\UrlHighlight\Replacer\Strategy\PlainStrategy;

class PlainStrategyTest extends TestCase
{
    private PlainStrategy $strategy;

    #[\Override]
    protected function setUp(): void
    {
        $this->strategy = new PlainStrategy(new Matcher());
    }

    /**
     * @param list<array{int, string, string}> $expected
     */
    #[DataProvider('findReplacementsDataProvider')]
    public function testFindReplacements(string $text, array $expected): void
    {
        $replacements = $this->strategy->findReplacements($text);
        $actual = self::toRows($replacements, $text);
        self::assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{string, list<array{int, string, string}>}>
     */
    public static function findReplacementsDataProvider(): array
    {
        return [
            'no url' => ['just text', []],
            'empty input' => ['', []],
            'url in text' => ['see http://example.com now', [
                [4, 'http://example.com', 'http://example.com'],
            ]],
            'multiple urls' => ['a.com and b.org', [
                [0, 'a.com', 'a.com'],
                [10, 'b.org', 'b.org'],
            ]],
            'email' => ['mail user@example.com now', [
                [5, 'user@example.com', 'user@example.com'],
            ]],
            'trailing punctuation is not part of the url' => ['see example.com.', [
                [4, 'example.com', 'example.com'],
            ]],
            'markup is matched as text' => ['<a href="http://example.com">example.com</a>', [
                [9, 'http://example.com', 'http://example.com'],
                [29, 'example.com', 'example.com'],
            ]],
            'offsets are bytes not characters' => ['Тест приклад.укр', [
                [9, 'приклад.укр', 'приклад.укр'],
            ]],
        ];
    }

    /**
     * @param iterable<Replacement> $replacements
     *
     * @return list<array{int, string, string}> Start offset, replaced range of the input, matched url.
     */
    private static function toRows(iterable $replacements, string $text): array
    {
        $rows = [];

        foreach ($replacements as $replacement) {
            $length = $replacement->end - $replacement->start;
            $rows[] = [$replacement->start, substr($text, $replacement->start, $length), $replacement->url->full];
        }

        return $rows;
    }
}
