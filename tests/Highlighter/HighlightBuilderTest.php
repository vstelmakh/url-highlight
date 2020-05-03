<?php

namespace VStelmakh\UrlHighlight\Tests\Highlighter;

use VStelmakh\UrlHighlight\Highlighter\HighlightBuilder;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Matcher\Match;

class HighlightBuilderTest extends TestCase
{
    public function testGetHighlight(): void
    {
        $highlightBuilder = $this->getHighlightBuilder('https://example.com', 'https');
        $expected = '<a href="https://example.com">https://example.com</a>';
        $actual = $highlightBuilder->getHighlight();
        $this->assertSame($expected, $actual);

        $highlightBuilder = $this->getHighlightBuilder('example.com', null);
        $expected = '<a href="http://example.com">example.com</a>';
        $actual = $highlightBuilder->getHighlight();
        $this->assertSame($expected, $actual);
    }

    public function testSetHref(): void
    {
        $highlightBuilder = $this->getHighlightBuilder('https://example.com', 'https');
        $expected = '<a href="custom">https://example.com</a>';
        $actual = $highlightBuilder->setHref('custom')->getHighlight();
        $this->assertSame($expected, $actual);
    }

    public function testHrefEscapeQuotes(): void
    {
        $highlightBuilder = $this->getHighlightBuilder('https://example.com/some"quotes', 'https');

        $expected = '<a href="https://example.com/some%22quotes">https://example.com/some"quotes</a>';
        $actual = $highlightBuilder->getHighlight();
        $this->assertSame($expected, $actual);

        $expected = '<a href="another%22qoutes">https://example.com/some"quotes</a>';
        $actual = $highlightBuilder->setHref('another"qoutes')->getHighlight();
        $this->assertSame($expected, $actual);
    }

    public function testSetText(): void
    {
        $highlightBuilder = $this->getHighlightBuilder('https://example.com', 'https');
        $expected = '<a href="https://example.com">custom</a>';
        $actual = $highlightBuilder->setText('custom')->getHighlight();
        $this->assertSame($expected, $actual);
    }

    public function testSetPrefix(): void
    {
        $highlightBuilder = $this->getHighlightBuilder('https://example.com', 'https');
        $expected = 'custom<a href="https://example.com">https://example.com</a>';
        $actual = $highlightBuilder->setPrefix('custom')->getHighlight();
        $this->assertSame($expected, $actual);
    }

    public function testSetSuffix(): void
    {
        $highlightBuilder = $this->getHighlightBuilder('https://example.com', 'https');
        $expected = '<a href="https://example.com">https://example.com</a>custom';
        $actual = $highlightBuilder->setSuffix('custom')->getHighlight();
        $this->assertSame($expected, $actual);
    }

    /**
     * @param string $fullMatch
     * @param string|null $scheme
     * @param string $defaultScheme
     * @return HighlightBuilder
     */
    private function getHighlightBuilder(
        string $fullMatch,
        ?string $scheme,
        string $defaultScheme = 'http'
    ): HighlightBuilder {
        $match = $this->createMock(Match::class);
        $match->method('getFullMatch')->willReturn($fullMatch);
        $match->method('getScheme')->willReturn($scheme);

        return new HighlightBuilder($match, $defaultScheme);
    }
}
