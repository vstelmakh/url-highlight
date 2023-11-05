<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests\Validator\DomainChecker;

use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Validator\DomainChecker\DomainChecker;

class DomainCheckerTest extends TestCase
{
    /**
     * @var DomainChecker
     */
    private $domainChecker;

    protected function setUp(): void
    {
        $this->domainChecker = new DomainChecker();
    }

    /**
     * @dataProvider isValidDomainDataProvider
     *
     * @param string $tld
     * @param bool $expected
     * @return void
     */
    public function testIsValidDomain(string $tld, bool $expected): void
    {
        $actual = $this->domainChecker->isValidDomain($tld);
        self::assertSame($expected, $actual, 'Dataset: ' . json_encode(func_get_args()));
    }

    /**
     * @return mixed[]
     */
    public function isValidDomainDataProvider(): array
    {
        return [
            ['com', true],
            ['COM', true],
            ['Com', true],
            ['cOm', true],
            ['', false],
            ['notexistent', false],
            ['random string value', false],
            ['1', false],
        ];
    }
}
