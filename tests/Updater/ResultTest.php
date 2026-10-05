<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests\Updater;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Updater\Result;

class ResultTest extends TestCase
{
    /**
     * @param list<string> $added
     * @param list<string> $removed
     */
    #[DataProvider('hasChangesDataProvider')]
    public function testHasChanges(array $added, array $removed, bool $expected): void
    {
        $result = new Result(
            sourceUrl: 'https://example.com/tlds.txt',
            targetPath: 'tld_map.php',
            version: 2026100400,
            lastUpdated: new \DateTimeImmutable('2026-10-04 07:07:01 UTC'),
            count: 2,
            previousCount: 2,
            added: $added,
            removed: $removed,
        );

        self::assertSame($expected, $result->hasChanges());
    }

    /**
     * @return array<string, array{list<string>, list<string>, bool}>
     */
    public static function hasChangesDataProvider(): array
    {
        return [
            'no changes' => [[], [], false],
            'added only' => [['укр'], [], true],
            'removed only' => [[], ['obsolete'], true],
            'added and removed' => [['укр'], ['obsolete'], true],
        ];
    }
}
