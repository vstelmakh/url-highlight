<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests\Replacer\Decoder;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Replacer\Decoder\Decoder;

class DecoderTest extends TestCase
{
    private Decoder $decoder;

    #[\Override]
    protected function setUp(): void
    {
        $this->decoder = new Decoder();
    }

    #[DataProvider('decodeValueDataProvider')]
    public function testDecodeValue(string $encoded, string $expected): void
    {
        self::assertSame($expected, $this->decoder->decode($encoded)->value);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function decodeValueDataProvider(): array
    {
        return [
            'empty string' => ['', ''],
            'no entities' => ['plain text', 'plain text'],
            'bare ampersand' => ['a & b', 'a & b'],
            'named entity' => ['a&amp;b', 'a&b'],
            'adjacent entities' => ['&amp;&amp;', '&&'],
            'decimal entity' => ['&#66;', 'B'],
            'hex entity' => ['&#x41;', 'A'],
            'mixed numeric entities' => ['&#x41;&#66;', 'AB'],
            'uppercase named entity' => ['&AMP;', '&'],
            'multibyte entity' => ['&hellip;', '…'],
            'unknown entity left as is' => ['&notanentity;', '&notanentity;'],
            'encoded tag' => ['&lt;p&gt;', '<p>'],
            'entity inside url' => ['example.com/a?b=1&amp;c=2', 'example.com/a?b=1&c=2'],
        ];
    }

    /**
     * @param array<int, int> $expected Decoded offset mapped to the expected encoded offset.
     */
    #[DataProvider('decodeOffsetDataProvider')]
    public function testDecodeOffset(string $encoded, array $expected): void
    {
        $decoded = $this->decoder->decode($encoded);

        $actual = [];
        foreach (array_keys($expected) as $decodedOffset) {
            $actual[$decodedOffset] = $decoded->toEncodedOffset($decodedOffset);
        }

        self::assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{string, array<int, int>}>
     */
    public static function decodeOffsetDataProvider(): array
    {
        return [
            // Nothing is decoded, so decoded and encoded offsets stay in step.
            'no entities maps one to one' => [
                'plain',
                [0 => 0, 3 => 3, 5 => 5],
            ],
            // 'a' at 0, then '&amp;' spans [1, 6], then 'b' at 6.
            'single entity' => [
                'a&amp;b',
                [0 => 0, 1 => 1, 2 => 6, 3 => 7],
            ],
            // '&lt;' spans [0, 4], 'p' at 4, '&gt;' spans [5, 9].
            'entities either side of a literal' => [
                '&lt;p&gt;',
                [0 => 0, 1 => 4, 2 => 5, 3 => 9],
            ],
            // Back to back entities: each decoded byte maps to the start of its own encoded entity.
            'adjacent entities' => [
                '&amp;&amp;',
                [0 => 0, 1 => 5, 2 => 10],
            ],
            // '…' occupies three decoded bytes, only its start and its end map onto entity boundaries.
            'multibyte entity' => [
                '&hellip;',
                [0 => 0, 3 => 8],
            ],
            // Decoding is a no-op, so the map has a boundary with a zero shift and offsets are unchanged.
            'unknown entity' => [
                '&notanentity;',
                [0 => 0, 5 => 5, 13 => 13],
            ],
        ];
    }
}
