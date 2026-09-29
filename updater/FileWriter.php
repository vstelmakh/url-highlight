<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Updater;

final readonly class FileWriter
{
    public function write(string $path, string $content): void
    {
        $result = file_put_contents($path, $content);

        if ($result === false) {
            $this->throwError($path);
        }
    }

    private function throwError(string $path): never
    {
        $error = error_get_last();
        throw new \RuntimeException(sprintf(
            'Error "%s" on writing to "%s".',
            $error['message'] ?? '',
            $path,
        ));
    }
}
