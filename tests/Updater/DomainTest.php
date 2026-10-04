<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests\Updater;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Updater\Domain;

class DomainTest extends TestCase
{
    #[DataProvider('constructValidDataProvider')]
    public function testConstructValid(string $value, string $expectedUnicode, string $expectedPunycode): void
    {
        $domain = new Domain($value);
        self::assertSame($expectedUnicode, $domain->unicode, 'Unexpected unicode value.');
        self::assertSame($expectedPunycode, $domain->punycode, 'Unexpected punycode value.');
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function constructValidDataProvider(): array
    {
        return [
            'ascii' => ['com', 'com', 'com'],
            'ascii uppercase' => ['COM', 'com', 'com'],
            'ascii with digit' => ['a1', 'a1', 'a1'],
            'unicode' => ['укр', 'укр', 'xn--j1amh'],
            'unicode uppercase' => ['УКР', 'укр', 'xn--j1amh'],
            'unicode chinese' => ['中国', '中国', 'xn--fiqs8s'],
            'unicode sharp s is kept' => ['straße', 'straße', 'xn--strae-oqa'],
            'punycode' => ['xn--j1amh', 'укр', 'xn--j1amh'],
            'punycode uppercase' => ['XN--J1AMH', 'укр', 'xn--j1amh'],
        ];
    }

    #[DataProvider('constructInvalidDataProvider')]
    public function testConstructInvalid(string $value, string $expectedMessage): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageIs($expectedMessage);
        new Domain($value);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function constructInvalidDataProvider(): array
    {
        $tooLongUnicode = str_repeat('ж', 60);
        $tooLongAscii = str_repeat('a', 64);

        return [
            'empty' => [
                '',
                'Domain value should not be empty.',
            ],
            'dot' => [
                'a.b',
                'Domain value "a.b" should be a single label.',
            ],
            'ideographic full stop' => [
                'a。b',
                'Domain value "a.b" should be a single label.',
            ],
            'space' => [
                'a b',
                'Domain value "a b" could not be converted to unicode. IDNA errors: IDNA_ERROR_DISALLOWED.',
            ],
            'underscore' => [
                'a_b',
                'Domain value "a_b" could not be converted to unicode. IDNA errors: IDNA_ERROR_DISALLOWED.',
            ],
            'leading hyphen' => [
                '-a',
                'Domain value "-a" could not be converted to unicode. IDNA errors: IDNA_ERROR_LEADING_HYPHEN.',
            ],
            'trailing hyphen' => [
                'a-',
                'Domain value "a-" could not be converted to unicode. IDNA errors: IDNA_ERROR_TRAILING_HYPHEN.',
            ],
            'leading and trailing hyphen' => [
                '-a-',
                'Domain value "-a-" could not be converted to unicode. '
                . 'IDNA errors: IDNA_ERROR_LEADING_HYPHEN, IDNA_ERROR_TRAILING_HYPHEN.',
            ],
            'hyphens in 3rd and 4th position' => [
                'ab--cd',
                'Domain value "ab--cd" could not be converted to unicode. IDNA errors: IDNA_ERROR_HYPHEN_3_4.',
            ],
            'invalid punycode' => [
                'xn--a',
                'Domain value "xn--a" could not be converted to unicode. IDNA errors: IDNA_ERROR_INVALID_ACE_LABEL.',
            ],
            'zero width joiner without context' => [
                "a\u{200D}b",
                "Domain value \"a\u{200D}b\" could not be converted to unicode. IDNA errors: IDNA_ERROR_CONTEXTJ.",
            ],
            'right-to-left digits only' => [
                '١٢٣',
                'Domain value "١٢٣" could not be converted to unicode. IDNA errors: IDNA_ERROR_BIDI.',
            ],
            'unicode too long as punycode' => [
                $tooLongUnicode,
                "Domain value \"{$tooLongUnicode}\" could not be converted to punycode. "
                . 'IDNA errors: IDNA_ERROR_LABEL_TOO_LONG.',
            ],
            'ascii too long' => [
                $tooLongAscii,
                "Domain value \"{$tooLongAscii}\" could not be converted to punycode. "
                . 'IDNA errors: IDNA_ERROR_LABEL_TOO_LONG.',
            ],
        ];
    }

    #[DataProvider('isIdnDataProvider')]
    public function testIsIdn(string $value, bool $expected): void
    {
        $domain = new Domain($value);
        self::assertSame($expected, $domain->isIdn());
    }

    /**
     * @return array<string, array{string, bool}>
     */
    public static function isIdnDataProvider(): array
    {
        return [
            'ascii' => ['com', false],
            'unicode' => ['укр', true],
            'punycode' => ['xn--j1amh', true],
        ];
    }

    public function testToStringReturnsUnicode(): void
    {
        $domain = new Domain('XN--J1AMH');
        self::assertSame('укр', (string) $domain);
    }
}
