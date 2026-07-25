<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer\Decoder;

/**
 * Result of HTML entity decoding: the decoded text and a mapping back to the original encoded positions.
 *
 * @internal
 */
final readonly class DecodedString
{
    /**
     * @param array<int, int> $shifts Maps a decoded byte offset (the position right after a decoded entity) to the
     *     number of bytes to add back to reach the original encoded offset. Keys are sorted ascending. Empty when the
     *     input had no entities.
     */
    public function __construct(
        public string $value,
        private array $shifts,
    ) {}

    public function toEncodedOffset(int $decodedOffset): int
    {
        $shift = 0;
        foreach ($this->shifts as $boundary => $cumulativeShift) {
            if ($boundary > $decodedOffset) {
                break;
            }
            $shift = $cumulativeShift;
        }
        return $decodedOffset + $shift;
    }
}
