<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests\Updater;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Updater\Domain;
use VStelmakh\UrlHighlight\Updater\DomainList;
use VStelmakh\UrlHighlight\Updater\Parser;

class ParserTest extends TestCase
{
    private Parser $parser;

    #[\Override]
    protected function setUp(): void
    {
        $this->parser = new Parser();
    }

    /**
     * @param list<string> $lines
     */
    #[DataProvider('parseValidDataProvider')]
    public function testParseValid(array $lines, DomainList $expected): void
    {
        $actual = $this->parser->parse($lines);
        self::assertEquals($expected, $actual);
    }

    /**
     * @return array<string, array{list<string>, DomainList}>
     */
    public static function parseValidDataProvider(): array
    {
        return [
            'iana list' => [
                [
                    '# Version 2026100400, Last Updated Sun Oct  4 07:07:01 2026 UTC',
                    'COM',
                    'NET',
                    'XN--J1AMH',
                ],
                new DomainList(2026100400, new \DateTimeImmutable('2026-10-04 07:07:01 UTC'), [
                    new Domain('com'),
                    new Domain('net'),
                    new Domain('укр'),
                ]),
            ],
            'two digit day' => [
                [
                    '# Version 2026101500, Last Updated Thu Oct 15 07:07:01 2026 UTC',
                    'COM',
                ],
                new DomainList(2026101500, new \DateTimeImmutable('2026-10-15 07:07:01 UTC'), [
                    new Domain('com'),
                ]),
            ],
            'whitespace around domains' => [
                [
                    '# Version 2026100400, Last Updated Sun Oct  4 07:07:01 2026 UTC',
                    ' COM ',
                    "XN--J1AMH\r",
                ],
                new DomainList(2026100400, new \DateTimeImmutable('2026-10-04 07:07:01 UTC'), [
                    new Domain('com'),
                    new Domain('укр'),
                ]),
            ],
        ];
    }

    /**
     * @param list<string> $lines
     * @param class-string<\Throwable> $expectedException
     */
    #[DataProvider('parseInvalidDataProvider')]
    public function testParseInvalid(array $lines, string $expectedException, string $expectedMessage): void
    {
        $this->expectException($expectedException);
        $this->expectExceptionMessageIs($expectedMessage);
        $this->parser->parse($lines);
    }

    /**
     * @return array<string, array{list<string>, class-string<\Throwable>, string}>
     */
    public static function parseInvalidDataProvider(): array
    {
        return [
            'no lines' => [
                [],
                \RuntimeException::class,
                'No header line found.',
            ],
            'header without version' => [
                ['# Last Updated Sun Oct  4 07:07:01 2026 UTC', 'COM'],
                \RuntimeException::class,
                'Could not parse version from header line "# Last Updated Sun Oct  4 07:07:01 2026 UTC".',
            ],
            'header without date' => [
                ['# Version 2026100400,', 'COM'],
                \RuntimeException::class,
                'Could not parse date from header line "# Version 2026100400,".',
            ],
            'header without last updated' => [
                ['# Version 2026100400, Updated Sun Oct  4 07:07:01 2026 UTC', 'COM'],
                \RuntimeException::class,
                'Could not parse date from header line "# Version 2026100400, Updated Sun Oct  4 07:07:01 2026 UTC".',
            ],
            'unexpected date format' => [
                ['# Version 2026100400, Last Updated 2026-10-04 07:07:01', 'COM'],
                \RuntimeException::class,
                'Could not parse date "2026-10-04 07:07:01". Unexpected format.',
            ],
            'header only' => [
                ['# Version 2026100400, Last Updated Sun Oct  4 07:07:01 2026 UTC'],
                \DomainException::class,
                'Domains list should not be empty.',
            ],
            'invalid domain' => [
                ['# Version 2026100400, Last Updated Sun Oct  4 07:07:01 2026 UTC', 'A_B'],
                \DomainException::class,
                'Domain value "A_B" could not be converted to unicode. IDNA errors: IDNA_ERROR_DISALLOWED.',
            ],
        ];
    }
}
