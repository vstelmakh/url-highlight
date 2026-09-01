<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests\Replacer\Strategy;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Matcher\Matcher;
use VStelmakh\UrlHighlight\Replacer\Decoder\Decoder;
use VStelmakh\UrlHighlight\Replacer\Extractor\Extractor;
use VStelmakh\UrlHighlight\Replacer\Extractor\Tokenizer;
use VStelmakh\UrlHighlight\Replacer\Replacement;
use VStelmakh\UrlHighlight\Replacer\Strategy\HtmlEncodedStrategy;

class HtmlEncodedStrategyTest extends TestCase
{
    private HtmlEncodedStrategy $strategy;

    #[\Override]
    protected function setUp(): void
    {
        $this->strategy = new HtmlEncodedStrategy(new Extractor(new Tokenizer()), new Decoder(), new Matcher());
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
            'unencoded url' => ['see http://example.com now', [
                [4, 'http://example.com', 'http://example.com'],
            ]],
            'entity in query is decoded and mapped back' => ['http://example.com/?a=1&amp;b=2', [
                [0, 'http://example.com/?a=1&amp;b=2', 'http://example.com/?a=1&b=2'],
            ]],
            'multiple entities in query' => ['http://example.com/?a=1&amp;b=2&amp;c=3', [
                [0, 'http://example.com/?a=1&amp;b=2&amp;c=3', 'http://example.com/?a=1&b=2&c=3'],
            ]],
            'entity in path' => ['see http://example.com/a&gt;b now', [
                [4, 'http://example.com/a&gt;b', 'http://example.com/a>b'],
            ]],
            'url in escaped attribute' => ['&lt;a href=&quot;http://example.com&quot;&gt;', [
                [17, 'http://example.com', 'http://example.com'],
            ]],
            'url in escaped angle brackets' => ['Visit &lt;example.com&gt; now', [
                [10, 'example.com', 'example.com'],
            ]],
            'url in escaped markup and in text after it' => [
                '&lt;a href=&quot;a.com&quot;&gt;b.org&lt;/a&gt;',
                [
                    [17, 'a.com', 'a.com'],
                    [32, 'b.org', 'b.org'],
                ],
            ],
            'offsets are bytes not characters' => ['Тест &lt;приклад.укр&gt;', [
                [13, 'приклад.укр', 'приклад.укр'],
            ]],

            'url in raw tag attribute is not matched' => ['<p data-url="http://example.com">x</p>', []],
            'url in raw link is not matched' => ['<a href="http://example.com">text</a>', []],
            'url in raw script is not matched' => ['<script>example.com</script>', []],
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
