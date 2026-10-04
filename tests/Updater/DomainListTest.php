<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests\Updater;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Updater\Domain;
use VStelmakh\UrlHighlight\Updater\DomainList;

class DomainListTest extends TestCase
{
    /**
     * @param list<string> $values
     * @param list<string> $expectedDomains
     */
    #[DataProvider('constructValidDataProvider')]
    public function testConstructValid(
        int $version,
        \DateTimeImmutable $lastUpdated,
        array $values,
        array $expectedDomains,
    ): void {
        $domains = array_map(static fn (string $value) => new Domain($value), $values);

        $domainList = new DomainList($version, $lastUpdated, $domains);

        $actualDomains = array_map(static fn (Domain $domain) => $domain->unicode, $domainList->domains);
        self::assertSame($version, $domainList->version, 'Unexpected version.');
        self::assertEquals($lastUpdated, $domainList->lastUpdated, 'Unexpected last updated date.');
        self::assertSame(array_combine($expectedDomains, $expectedDomains), $actualDomains, 'Unexpected domains.');
        self::assertCount(count($expectedDomains), $domainList);
    }

    /**
     * @return array<string, array{int, \DateTimeImmutable, list<string>, list<string>}>
     */
    public static function constructValidDataProvider(): array
    {
        $date = new \DateTimeImmutable('2026-10-02 07:07:01 UTC');

        return [
            'single domain' => [2026100200, $date, ['com'], ['com']],
            'minimal version' => [1, $date, ['com'], ['com']],
            'internet birth date' => [1, new \DateTimeImmutable('1983-01-01 00:00:00 UTC'), ['com'], ['com']],
            'sorted' => [1, $date, ['укр', 'org', '中国', 'com'], ['com', 'org', 'укр', '中国']],
            'duplicates removed' => [1, $date, ['COM', 'com', 'xn--j1amh', 'укр'], ['com', 'укр']],
        ];
    }

    /**
     * @param list<string> $values
     */
    #[DataProvider('constructInvalidDataProvider')]
    public function testConstructInvalid(
        int $version,
        \DateTimeImmutable $lastUpdated,
        array $values,
        string $expectedMessage,
    ): void {
        $domains = array_map(static fn (string $value) => new Domain($value), $values);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageIs($expectedMessage);
        new DomainList($version, $lastUpdated, $domains);
    }

    /**
     * @return array<string, array{int, \DateTimeImmutable, list<string>, string}>
     */
    public static function constructInvalidDataProvider(): array
    {
        $date = new \DateTimeImmutable('2026-10-02 07:07:01 UTC');

        return [
            'zero version' => [
                0,
                $date,
                ['com'],
                'Version 0 should be greater than 0.',
            ],
            'negative version' => [
                -1,
                $date,
                ['com'],
                'Version -1 should be greater than 0.',
            ],
            'date before internet birth' => [
                1,
                new \DateTimeImmutable('1982-12-31 23:59:59 UTC'),
                ['com'],
                'Last updated date "1982-12-31T23:59:59+00:00" should be after the internet was born.',
            ],
            'empty domains' => [
                1,
                $date,
                [],
                'Domains list should not be empty.',
            ],
        ];
    }
}
