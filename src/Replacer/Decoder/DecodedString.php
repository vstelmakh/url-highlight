<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer\Decoder;

/**
 * Result of HTML entity decoding: the decoded text, plus a mapping from its byte offsets back onto the encoded text
 * it came from.
 *
 * @internal
 */
final readonly class DecodedString
{
    /**
     * @param list<int> $decodedOffsets
     * @param list<int> $encodedOffsets
     */
    public function __construct(
        public string $value,
        private array $decodedOffsets,
        private array $encodedOffsets,
    ) {}

    public function toEncodedOffset(int $decodedOffset): int
    {
        $low = 0;
        $high = count($this->decodedOffsets) - 1;
        $shift = 0;

        // Binary search rather than a scan, because it's much more performant for big offset lists.
        while ($low <= $high) {
            $middle = intdiv($low + $high, 2);

            if ($this->decodedOffsets[$middle] > $decodedOffset) {
                $high = $middle - 1;
                continue;
            }

            $shift = $this->encodedOffsets[$middle] - $this->decodedOffsets[$middle];
            $low = $middle + 1;
        }

        return $shift + $decodedOffset;
    }
}
