<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests\Replacer;

use PHPUnit\Framework\MockObject\MockObject;
use VStelmakh\UrlHighlight\Matcher\MatcherInterface;
use VStelmakh\UrlHighlight\Matcher\UrlMatch;
use VStelmakh\UrlHighlight\Replacer\ReplacerFactory;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Replacer\ReplacerInterface;

class ReplacerFactoryTest extends TestCase
{
    /**
     * @dataProvider createReplacerDataProvider
     *
     * @param (MatcherInterface&MockObject)|null $matcher
     */
    public function testCreateReplacer(?MatcherInterface $matcher): void
    {
        if ($matcher !== null) {
            $matcher->expects(self::atLeastOnce())->method('matchAll');
        }

        $replacer = ReplacerFactory::createReplacer($matcher);
        $replacer->replaceCallback('', function (UrlMatch $match) {
            return '';
        });

        self::assertInstanceOf(ReplacerInterface::class, $replacer);
    }

    /**
     * @return mixed[]
     */
    public function createReplacerDataProvider(): array
    {
        return [
            [null],
            [$this->createMock(MatcherInterface::class)],
        ];
    }
}
