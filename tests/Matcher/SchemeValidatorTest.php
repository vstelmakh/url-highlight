<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests\Matcher;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Matcher\SchemeValidator;
use VStelmakh\UrlHighlight\Url;

class SchemeValidatorTest extends TestCase
{
    private SchemeValidator $schemeValidator;

    #[\Override]
    protected function setUp(): void
    {
        $this->schemeValidator = new SchemeValidator();
    }

    #[DataProvider('isValidDataProvider')]
    public function testIsValid(?string $scheme, bool $expected): void
    {
        $url = new Url('example.com', $scheme, null, 'example.com', null, null, null, null);
        $actual = $this->schemeValidator->isValid($url);
        self::assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{?string, bool}>
     */
    public static function isValidDataProvider(): array
    {
        return [
            'no scheme' => [null, true],
            'http' => ['http', true],
            'https' => ['https', true],
            'mailto' => ['mailto', true],
            'custom app scheme' => ['myapp', true],
            'scheme containing script name' => ['javascripts', true],

            'javascript' => ['javascript', false],
            'javascript uppercase' => ['JAVASCRIPT', false],
            'javascript mixed case' => ['JavaScript', false],
            'vbscript' => ['vbscript', false],
            'data' => ['data', false],
        ];
    }
}
