<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests\Replacer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Format;
use VStelmakh\UrlHighlight\Matcher\Matcher;
use VStelmakh\UrlHighlight\Replacer\Strategy\HtmlEncodedStrategy;
use VStelmakh\UrlHighlight\Replacer\Strategy\HtmlStrategy;
use VStelmakh\UrlHighlight\Replacer\Strategy\PlainStrategy;
use VStelmakh\UrlHighlight\Replacer\Strategy\Strategy;
use VStelmakh\UrlHighlight\Replacer\StrategyFactory;

class StrategyFactoryTest extends TestCase
{
    private StrategyFactory $strategyFactory;

    #[\Override]
    protected function setUp(): void
    {
        $this->strategyFactory = StrategyFactory::create(new Matcher());
    }

    /**
     * @param class-string<Strategy> $expected
     */
    #[DataProvider('createStrategyDataProvider')]
    public function testCreateStrategy(Format $format, string $expected): void
    {
        $actual = $this->strategyFactory->createStrategy($format);
        self::assertInstanceOf($expected, $actual);
    }

    /**
     * @return array<string, array{Format, class-string<Strategy>}>
     */
    public static function createStrategyDataProvider(): array
    {
        return [
            'plain' => [Format::Plain, PlainStrategy::class],
            'html' => [Format::Html, HtmlStrategy::class],
            'html encoded' => [Format::HtmlEncoded, HtmlEncodedStrategy::class],
        ];
    }

    public function testCreateStrategyCoversEveryFormat(): void
    {
        foreach (Format::cases() as $format) {
            $actual = $this->strategyFactory->createStrategy($format);
            self::assertInstanceOf(Strategy::class, $actual);
        }
    }
}
