<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests\Replacer\Extractor;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Replacer\Extractor\Extractor;
use VStelmakh\UrlHighlight\Replacer\Extractor\Tokenizer;

class ExtractorTest extends TestCase
{
    private Extractor $extractor;

    #[\Override]
    protected function setUp(): void
    {
        $this->extractor = new Extractor(new Tokenizer());
    }

    /**
     * @param array<int, string> $expected
     */
    #[DataProvider('extractDataProvider')]
    public function testExtract(string $html, array $expected): void
    {
        $actual = iterator_to_array($this->extractor->extract($html));
        self::assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{string, array<int, string>}>
     */
    public static function extractDataProvider(): array
    {
        return [
            'empty input' => ['', []],
            'plain text only' => ['no tags here', [0 => 'no tags here']],
            'text around tags' => ['a <b>c</b> d', [0 => 'a ', 5 => 'c', 10 => ' d']],
            'adjacent tags' => ['<b>a</b><i>b</i>', [3 => 'a', 11 => 'b']],
            'text inside non skip tag' => ['<p>text</p>', [3 => 'text']],

            'skip tag content' => ['x <a href="#">link.com</a> y', [0 => 'x ', 26 => ' y']],
            'skip tag uppercase' => ['<SCRIPT>a</SCRIPT>b', [18 => 'b']],
            'skip tag nested in itself' => ['<script>a<script>b</script>c</script>d', [37 => 'd']],
            'skip tag with nested other tag' => ['<a><b>x</b></a>y', [15 => 'y']],
            'skip tag self closing keeps depth' => ['<svg/>text.com', [6 => 'text.com']],
            'skip tag closing without opening' => ['a</a>b', [0 => 'a', 5 => 'b']],
            'skip tag not closed' => ['a<script>b', [0 => 'a']],

            'comment content' => ['a<!-- b.com -->c', [0 => 'a', 15 => 'c']],
            'offsets are bytes not characters' => ['<b>Тест</b> a.com', [3 => 'Тест', 15 => ' a.com']],
        ];
    }

    #[DataProvider('skipTagDataProvider')]
    public function testExtractSkipsContentOfSkipTag(string $tag): void
    {
        $html = "a<{$tag}>skipped.com</{$tag}>b";
        $expected = [0 => 'a', strlen($html) - 1 => 'b'];

        $actual = iterator_to_array($this->extractor->extract($html));

        self::assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function skipTagDataProvider(): array
    {
        return [
            'a' => ['a'],
            'button' => ['button'],
            'datalist' => ['datalist'],
            'math' => ['math'],
            'script' => ['script'],
            'select' => ['select'],
            'style' => ['style'],
            'svg' => ['svg'],
            'textarea' => ['textarea'],
            'title' => ['title'],
        ];
    }
}
