<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests\Replacer\Decoder;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Replacer\Decoder\DecodedString;

class DecodedStringTest extends TestCase
{
    /**
     * @param list<int> $decodedOffsets
     * @param list<int> $encodedOffsets
     */
    #[DataProvider('toEncodedOffsetDataProvider')]
    public function testToEncodedOffset(
        array $decodedOffsets,
        array $encodedOffsets,
        int $decodedOffset,
        int $expected,
    ): void {
        // The text itself is irrelevant here: the lookup is driven entirely by the offset lists.
        $decodedString = new DecodedString('irrelevant', $decodedOffsets, $encodedOffsets);
        self::assertSame($expected, $decodedString->toEncodedOffset($decodedOffset));
    }

    /**
     * @return array<string, array{list<int>, list<int>, int, int}>
     */
    public static function toEncodedOffsetDataProvider(): array
    {
        $decodedOffsets = [2, 5, 9];
        $encodedOffsets = [6, 12, 21];

        return [
            'no boundaries at offset zero' => [[], [], 0, 0],
            'no boundaries past the last offset' => [[], [], 7, 7],

            'ahead of the only boundary' => [[3], [8], 2, 2],
            'exactly on the only boundary' => [[3], [8], 3, 8],
            'past the only boundary' => [[3], [8], 4, 9],

            'ahead of every boundary' => [$decodedOffsets, $encodedOffsets, 0, 0],
            'just ahead of the first' => [$decodedOffsets, $encodedOffsets, 1, 1],
            'exactly on the first' => [$decodedOffsets, $encodedOffsets, 2, 6],
            'between first and second' => [$decodedOffsets, $encodedOffsets, 3, 7],
            'just ahead of the second' => [$decodedOffsets, $encodedOffsets, 4, 8],
            'exactly on the second' => [$decodedOffsets, $encodedOffsets, 5, 12],
            'between second and last' => [$decodedOffsets, $encodedOffsets, 8, 15],
            'exactly on the last' => [$decodedOffsets, $encodedOffsets, 9, 21],
            'past the last' => [$decodedOffsets, $encodedOffsets, 100, 112],
        ];
    }
}
