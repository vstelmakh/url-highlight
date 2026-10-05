<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests\Updater;

use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Matcher\Domains\TopLevelDomains;
use VStelmakh\UrlHighlight\Updater\Domain;
use VStelmakh\UrlHighlight\Updater\DomainList;
use VStelmakh\UrlHighlight\Updater\Renderer;
use VStelmakh\UrlHighlight\Updater\Updater;

/**
 * Checks that the updater output matches what the library loads, so a change on one side fails the test.
 */
class ContractTest extends TestCase
{
    public function testUpdaterWritesFileLibraryReads(): void
    {
        $libraryPath = $this->getLibraryMapPath();
        $updaterPath = $this->getUpdaterProperty('targetPath');

        self::assertSame(realpath($libraryPath), realpath($updaterPath));
    }

    public function testLibraryMapMatchesRendererOutput(): void
    {
        $libraryPath = $this->getLibraryMapPath();
        $map = require $libraryPath;
        self::assertIsArray($map);

        $domains = array_map(static fn (int|string $key) => new Domain((string) $key), array_keys($map));
        $domainList = new DomainList(1, new \DateTimeImmutable('2026-01-01 00:00:00 UTC'), $domains);
        $sourceUrl = $this->getUpdaterProperty('sourceUrl');

        $expected = new Renderer()->render($domainList, $sourceUrl);

        self::assertSame($expected, file_get_contents($libraryPath));
    }

    private function getLibraryMapPath(): string
    {
        $libraryFile = new \ReflectionClass(TopLevelDomains::class)->getFileName();
        self::assertIsString($libraryFile);

        return dirname($libraryFile) . '/tld_map.php';
    }

    private function getUpdaterProperty(string $name): string
    {
        $value = new \ReflectionProperty(Updater::class, $name)->getValue(Updater::create());
        self::assertIsString($value);

        return $value;
    }
}
