<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests\Replacer\Strategy;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Matcher\Matcher;
use VStelmakh\UrlHighlight\Replacer\Extractor\Extractor;
use VStelmakh\UrlHighlight\Replacer\Extractor\Tokenizer;
use VStelmakh\UrlHighlight\Replacer\Replacement;
use VStelmakh\UrlHighlight\Replacer\Strategy\HtmlStrategy;

class HtmlStrategyTest extends TestCase
{
    private HtmlStrategy $strategy;

    #[\Override]
    protected function setUp(): void
    {
        $this->strategy = new HtmlStrategy(new Extractor(new Tokenizer()), new Matcher());
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
            'url without markup' => ['see http://example.com now', [
                [4, 'http://example.com', 'http://example.com'],
            ]],
            'url in element text' => ['a <b>example.com</b> b', [
                [5, 'example.com', 'example.com'],
            ]],
            'url in each of multiple text nodes' => ['a.com <b>b.org</b>', [
                [0, 'a.com', 'a.com'],
                [9, 'b.org', 'b.org'],
            ]],
            'email in element text' => ['<p>mail user@example.com now</p>', [
                [8, 'user@example.com', 'user@example.com'],
            ]],
            'offsets are bytes not characters' => ['<b>Тест</b> приклад.укр', [
                [16, 'приклад.укр', 'приклад.укр'],
            ]],

            'url in tag attribute is not matched' => ['<p data-url="http://example.com">x</p>', []],
            'url in link is not matched' => ['<a href="http://example.com">text</a>', []],
            'url in script is not matched' => ['<script>example.com</script>', []],
            'url after skipped element' => ['<script>example.com</script>text.com', [
                [28, 'text.com', 'text.com'],
            ]],
            'url in comment is not matched' => ['a<!-- example.com -->b', []],
            'each text node is matched on its own' => ['exa<b>mple.com', [
                [6, 'mple.com', 'mple.com'],
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
