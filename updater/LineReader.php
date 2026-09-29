<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Updater;

final readonly class LineReader
{
    /**
     * @return list<string>
     */
    public function readLines(string $url): array
    {
        $result = file($url, FILE_IGNORE_NEW_LINES + FILE_SKIP_EMPTY_LINES);

        if ($result === false) {
            $this->throwError($url);
        }

        return $result;
    }

    private function throwError(string $url): never
    {
        $error = error_get_last();
        throw new \RuntimeException(sprintf(
            'Error "%s" on reading from "%s".',
            $error['message'] ?? '',
            $url,
        ));
    }
}
