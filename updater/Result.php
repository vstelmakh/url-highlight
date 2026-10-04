<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Updater;

final readonly class Result
{
    /**
     * @param list<string> $added
     * @param list<string> $removed
     */
    public function __construct(
        public string $sourceUrl,
        public string $targetPath,
        public int $version,
        public \DateTimeImmutable $lastUpdated,
        public int $count,
        public int $previousCount,
        public array $added,
        public array $removed,
    ) {}

    public function hasChanges(): bool
    {
        return $this->added !== [] || $this->removed !== [];
    }
}
