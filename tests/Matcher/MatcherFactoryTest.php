<?php

namespace VStelmakh\UrlHighlight\Tests\Matcher;

use PHPUnit\Framework\MockObject\MockObject;
use VStelmakh\UrlHighlight\Encoder\EncoderInterface;
use VStelmakh\UrlHighlight\Matcher\EncodedMatcher;
use VStelmakh\UrlHighlight\Matcher\Matcher;
use VStelmakh\UrlHighlight\Matcher\MatcherFactory;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Validator\ValidatorInterface;

class MatcherFactoryTest extends TestCase
{
    /**
     * @dataProvider createMatcherDataProvider
     *
     * @param (ValidatorInterface&MockObject)|null $validator
     * @param EncoderInterface|null $encoder
     * @param class-string $expectedClass
     */
    public function testCreateMatcher(
        ?ValidatorInterface $validator,
        ?EncoderInterface $encoder,
        string $expectedClass
    ): void {
        if ($validator !== null) {
            $validator->expects(self::atLeastOnce())->method('isValidMatch');
        }

        $matcher = MatcherFactory::createMatcher($validator, $encoder);
        $matcher->match('');
        self::assertInstanceOf($expectedClass, $matcher);
    }

    /**
     * @return mixed[]
     */
    public function createMatcherDataProvider(): array
    {
        return [
            [
                null,
                null,
                Matcher::class,
            ],
            [
                $this->createMock(ValidatorInterface::class),
                null,
                Matcher::class,
            ],
            [
                null,
                $this->createMock(EncoderInterface::class),
                EncodedMatcher::class,
            ],
            [
                $this->createMock(ValidatorInterface::class),
                $this->createMock(EncoderInterface::class),
                EncodedMatcher::class,
            ],
        ];
    }
}
