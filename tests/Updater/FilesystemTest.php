<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests\Updater;

use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Updater\Filesystem;

class FilesystemTest extends TestCase
{
    private Filesystem $filesystem;
    private string $directory;

    #[\Override]
    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();
        $this->directory = dirname(__DIR__, 2) . '/var/tests/filesystem';

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

    public function testReadLines(): void
    {
        $actual = $this->filesystem->readLines(__DIR__ . '/lines.txt');

        self::assertSame(['first', 'second', 'third'], $actual);
    }

    public function testReadLinesThrowsOnMissingFile(): void
    {
        $path = "{$this->directory}/missing.txt";

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageIs("Could not read \"{$path}\": Failed to open stream: No such file or directory.");
        $this->filesystem->readLines($path);
    }

    public function testReadLinesThrowsOnDirectory(): void
    {
        $path = $this->directory;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageIs(
            "Could not read \"{$path}\": Read of 8192 bytes failed with errno=21 Is a directory.",
        );
        $this->filesystem->readLines($path);
    }

    public function testWrite(): void
    {
        $path = "{$this->directory}/output.txt";
        file_put_contents($path, 'old content');

        $this->filesystem->write($path, 'new content');

        self::assertSame('new content', file_get_contents($path));
    }

    public function testWriteThrowsOnMissingDirectory(): void
    {
        $path = "{$this->directory}/missing/output.txt";

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageIs("Could not write \"{$path}\": Failed to open stream: No such file or directory.");
        $this->filesystem->write($path, 'content');
    }

    public function testErrorHandlerIsRestoredAfterFailure(): void
    {
        $handlerBefore = $this->getErrorHandler();

        try {
            $this->filesystem->readLines("{$this->directory}/missing.txt");
        } catch (\RuntimeException) {
        }

        self::assertSame($handlerBefore, $this->getErrorHandler());
    }

    private function getErrorHandler(): ?callable
    {
        $handler = set_error_handler(static fn () => false);
        restore_error_handler();
        return $handler;
    }
}
