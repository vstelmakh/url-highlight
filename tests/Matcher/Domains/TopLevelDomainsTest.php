<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests\Matcher\Domains;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Matcher\Domains\TopLevelDomains;

class TopLevelDomainsTest extends TestCase
{
    #[DataProvider('containsDataProvider')]
    public function testContains(string $domain, bool $expected): void
    {
        $actual = TopLevelDomains::contains($domain);
        self::assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public static function containsDataProvider(): array
    {
        return [
            'known' => ['com', true],
            'known uppercase' => ['COM', true],
            'known mixed case' => ['Com', true],
            'known unicode' => ['укр', true],
            'known unicode uppercase' => ['УКР', true],
            'unknown' => ['invalidtld', false],
            'leading dot' => ['.com', false],
            'empty' => ['', false],
        ];
    }
}
