<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests\Matcher;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Matcher\UrlMatch;
use VStelmakh\UrlHighlight\Url;

class UrlMatchTest extends TestCase
{
    #[DataProvider('endDataProvider')]
    public function testEnd(int $start, string $full, int $expected): void
    {
        $urlMatch = new UrlMatch($start, self::url($full));
        self::assertSame($expected, $urlMatch->end);
    }

    /**
     * @return array<string, array{int, string, int}>
     */
    public static function endDataProvider(): array
    {
        return [
            'at the start of text' => [0, 'example.com', 11],
            'after an offset' => [6, 'example.com', 17],
            'multibyte url counts bytes' => [6, 'приклад.укр', 27],
            'empty url' => [6, '', 6],
        ];
    }

    private static function url(string $full): Url
    {
        // The host is irrelevant here, since we are checking only the offset.
        return new Url($full, null, null, 'irrelevant', null, null, null, null);
    }
}
