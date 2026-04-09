<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight;

class UrlHighlight
{
    /**
     * Check if string is valid url.
     * If encoder provided - string will be decoded, than check performed.
     *
     * @param string $string
     * @return bool
     */
    public function isUrl(string $string): bool
    {
        return false; // TODO: implement
    }

    /**
     * Parse string and return array of urls found.
     * If encoder provided - will return decoded urls.
     *
     * @param string $string
     * @return array|string[]
     */
    public function getUrls(string $string): array
    {
        return []; // TODO: implement
    }

    /**
     * Parse string and replace urls with highlighted links
     * e.g. http://example.com -> <a href="http://example.com">http://example.com</a>
     *
     * @param string $string
     * @return string
     */
    public function highlightUrls(string $string): string
    {
        return ''; // TODO: implement
    }
}
