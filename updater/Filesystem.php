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
            "Could not read \"{$path}\"",
        );
    }

    public function write(string $path, string $content): void
    {
        $this->execute(
            static fn () => file_put_contents($path, $content),
            "Could not write \"{$path}\"",
        );
    }

    /**
     * Runs the operation with warnings captured. A warning counts as a failure, even if the operation returned a value.
     *
     * @template T
     *
     * @param \Closure(): (T|false) $operation
     *
     * @return T
     */
    private function execute(\Closure $operation, string $failureMessage): mixed
    {
        $warning = null;

        set_error_handler(static function (int $severity, string $message) use (&$warning): bool {
            $warning ??= $message;
            return true;
        });

        try {
            $result = $operation();
        } finally {
            restore_error_handler();
        }

        if ($warning !== null) {
            $reason = $this->resolveReason($warning);
            throw new \RuntimeException("{$failureMessage}: {$reason}.");
        }

        if ($result === false) {
            throw new \RuntimeException("{$failureMessage}.");
        }

        return $result;
    }

    /**
     * Removes the "function(arguments): " prefix PHP adds to warnings, as the caller already names the path.
     */
    private function resolveReason(string $warning): string
    {
        $reason = preg_replace('/^\w+\([^)]*\): /', '', $warning);

        return $reason ?? $warning;
    }
}
