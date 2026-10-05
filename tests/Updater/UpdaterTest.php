<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests\Updater;

use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Updater\Filesystem;
use VStelmakh\UrlHighlight\Updater\Parser;
use VStelmakh\UrlHighlight\Updater\Renderer;
use VStelmakh\UrlHighlight\Updater\Result;
use VStelmakh\UrlHighlight\Updater\Updater;

class UpdaterTest extends TestCase
{
    private const string SOURCE_PATH = __DIR__ . '/tlds.txt';
    private string $directory;
    private string $targetPath;

    #[\Override]
    protected function setUp(): void
    {
        $this->directory = dirname(__DIR__, 2) . '/var/tests/updater';
        $this->targetPath = "{$this->directory}/tld_map.php";

        if (!is_dir($this->directory)) {
            mkdir($this->directory, recursive: true);
        }
    }

    #[\Override]
    protected function tearDown(): void
    {
        $files = glob("{$this->directory}/*");

        if ($files !== false) {
            array_map(unlink(...), $files);
        }

        rmdir($this->directory);
    }

    public function testUpdateCreatesTarget(): void
    {
        $updater = $this->createUpdater(self::SOURCE_PATH);

        $actual = $updater->update();

        $expected = $this->createResult(previousCount: 0, added: ['com', 'net', 'укр'], removed: []);
        self::assertEquals($expected, $actual);
        self::assertSame(['com' => true, 'net' => true, 'укр' => true], $this->loadTarget());
    }

    public function testUpdateReportsChanges(): void
    {
        $this->writeTarget("<?php return ['com' => true, 'obsolete' => true];");
        $updater = $this->createUpdater(self::SOURCE_PATH);

        $actual = $updater->update();

        $expected = $this->createResult(previousCount: 2, added: ['net', 'укр'], removed: ['obsolete']);
        self::assertEquals($expected, $actual);
        self::assertSame(['com' => true, 'net' => true, 'укр' => true], $this->loadTarget());
    }

    public function testUpdateReportsNoChangesOnRepeatedRun(): void
    {
        $updater = $this->createUpdater(self::SOURCE_PATH);
        $updater->update();

        $actual = $updater->update();

        $expected = $this->createResult(previousCount: 3, added: [], removed: []);
        self::assertEquals($expected, $actual);
        self::assertFalse($actual->hasChanges());
    }

    public function testUpdateThrowsOnInvalidTarget(): void
    {
        $content = "<?php return 'not a map';";
        $this->writeTarget($content);
        $updater = $this->createUpdater(self::SOURCE_PATH);

        try {
            $updater->update();
            self::fail('Expected exception was not thrown.');
        } catch (\RuntimeException $exception) {
            self::assertSame("File \"{$this->targetPath}\" should return an array.", $exception->getMessage());
        }

        self::assertSame($content, $this->readTarget(), 'Target should stay unchanged.');
    }

    public function testUpdateKeepsTargetOnSourceFailure(): void
    {
        $content = "<?php return ['com' => true];";
        $this->writeTarget($content);
        $updater = $this->createUpdater("{$this->directory}/missing.txt");

        try {
            $updater->update();
            self::fail('Expected exception was not thrown.');
        } catch (\RuntimeException) {
        }

        self::assertSame($content, $this->readTarget(), 'Target should stay unchanged.');
    }

    private function createUpdater(string $sourceUrl): Updater
    {
        return new Updater(
            sourceUrl: $sourceUrl,
            targetPath: $this->targetPath,
            parser: new Parser(),
            renderer: new Renderer(),
            filesystem: new Filesystem(),
        );
    }

    /**
     * @param list<string> $added
     * @param list<string> $removed
     */
    private function createResult(int $previousCount, array $added, array $removed): Result
    {
        return new Result(
            sourceUrl: self::SOURCE_PATH,
            targetPath: $this->targetPath,
            version: 2026101400,
            lastUpdated: new \DateTimeImmutable('2026-10-14 07:07:01 UTC'),
            count: 3,
            previousCount: $previousCount,
            added: $added,
            removed: $removed,
        );
    }

    private function writeTarget(string $content): void
    {
        file_put_contents($this->targetPath, $content);
    }

    private function readTarget(): string|false
    {
        return file_get_contents($this->targetPath);
    }

    private function loadTarget(): mixed
    {
        return require $this->targetPath;
    }
}
