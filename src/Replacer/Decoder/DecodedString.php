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
     * @param array<int, int> $shifts Map of decoded byte offset (just after an entity) to the cumulative
     *                                difference between encoded and decoded byte length up to that point.
     *                                Empty when the source contained no entities.
     */
    public function __construct(
        public string $value,
        private array $shifts,
    ) {}

    /**
     * Translate a byte offset within the decoded string back to its position in the original encoded string.
     */
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
