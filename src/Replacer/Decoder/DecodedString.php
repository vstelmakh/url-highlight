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
        private int $absoluteStart = 0,
    ) {}

    public function withAbsoluteStart(int $absoluteStart): static
    {
        return new static($this->value, $this->shifts, $absoluteStart);
    }

    public function toAbsoluteOffset(int $decodedOffset): int
    {
        $shift = 0;
        foreach ($this->shifts as $boundary => $cumulativeShift) {
            if ($boundary > $decodedOffset) {
                break;
            }
            $shift = $cumulativeShift;
        }
        return $this->absoluteStart + $decodedOffset + $shift;
    }
}
