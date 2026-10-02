<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Updater;

final readonly class Filesystem
{
    /**
     * @return list<string>
     */
    public function readLines(string $path, float $timeoutSeconds = 10.0): array
    {
        $context = stream_context_create([
            'http' => ['timeout' => $timeoutSeconds],
        ]);

        return $this->execute(
            static fn () => file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES, $context),
            sprintf('reading from "%s"', $path),
        );
    }

    public function write(string $path, string $content): void
    {
        $this->execute(
            static fn () => file_put_contents($path, $content),
            sprintf('writing to "%s"', $path),
        );
    }

    /**
     * Runs the operation with warnings captured and converted to exception.
     *
     * @template T
     *
     * @param \Closure(): (T|false) $operation
     *
     * @return T
     */
    private function execute(\Closure $operation, string $action): mixed
    {
        $error = '';

        set_error_handler(static function (int $severity, string $message) use (&$error): bool {
            $error = $message;
            return true;
        });

        try {
            $result = $operation();
        } finally {
            restore_error_handler();
        }

        if ($result === false) {
            throw new \RuntimeException(sprintf('Error "%s" on %s.', $error, $action));
        }

        return $result;
    }
}
