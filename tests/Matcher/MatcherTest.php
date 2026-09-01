<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests\Matcher;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Matcher\Matcher;
use VStelmakh\UrlHighlight\Matcher\UrlMatch;
use VStelmakh\UrlHighlight\Url;

class MatcherTest extends TestCase
{
    private Matcher $matcher;

    #[\Override]
    protected function setUp(): void
    {
        $this->matcher = new Matcher();
    }

    public function testMatchReturnsUrlWithAllComponents(): void
    {
        $text = 'Go to http://user:pass@example.com:8080/path/index.html?a=1&b=2#top now.';

        $actual = iterator_to_array($this->matcher->match($text), false);

        $expected = [self::urlMatch(
            start: 6,
            full: 'http://user:pass@example.com:8080/path/index.html?a=1&b=2#top',
            host: 'example.com',
            scheme: 'http',
            userinfo: 'user:pass',
            port: 8080,
            path: '/path/index.html',
            query: '?a=1&b=2',
            fragment: '#top',
        )];
        self::assertEquals($expected, $actual);
    }

    public function testMatchTrimsTrailingPunctuation(): void
    {
        $text = '(see example.com/path).';

        $actual = iterator_to_array($this->matcher->match($text), false);

        // The start is the one found by the regex, the end is recounted from the trimmed url.
        $expected = [self::urlMatch(start: 5, full: 'example.com/path', host: 'example.com', path: '/path')];
        self::assertEquals($expected, $actual);
    }

    public function testMatchKeepsUrlWithInvalidHostWhenSchemeIsPresent(): void
    {
        $text = 'Go http://localhost/path now.';

        $actual = iterator_to_array($this->matcher->match($text), false);

        $expected = [self::urlMatch(
            start: 3,
            full: 'http://localhost/path',
            host: 'localhost',
            scheme: 'http',
            path: '/path',
        )];
        self::assertEquals($expected, $actual);
    }

    #[DataProvider('invalidHostDataProvider')]
    public function testMatchSkipsUrlWithInvalidHost(string $text): void
    {
        $actual = iterator_to_array($this->matcher->match($text), false);
        self::assertSame([], $actual);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidHostDataProvider(): array
    {
        return [
            'unknown top level domain' => ['Open example.nonexistent now.'],
            'file name' => ['Open filename.txt please.'],
            'version number' => ['Version 1.2.3 released.'],
        ];
    }

    public function testMatchContinuesAfterSkippedUrl(): void
    {
        $text = 'Open filename.txt and example.com now.';

        $actual = iterator_to_array($this->matcher->match($text), false);

        $expected = [self::urlMatch(start: 22, full: 'example.com', host: 'example.com')];
        self::assertEquals($expected, $actual);
    }

    public function testMatchReturnsMatchesInOrderOfAppearance(): void
    {
        $text = 'First example.com then http://example.com/a) and last user@example.com.';

        $actual = iterator_to_array($this->matcher->match($text), false);

        $expected = [
            self::urlMatch(start: 6, full: 'example.com', host: 'example.com'),
            self::urlMatch(start: 23, full: 'http://example.com/a', host: 'example.com', scheme: 'http', path: '/a'),
            self::urlMatch(start: 54, full: 'user@example.com', host: 'example.com', userinfo: 'user'),
        ];
        self::assertEquals($expected, $actual);
    }

    private static function urlMatch(
        int $start,
        string $full,
        string $host,
        ?string $scheme = null,
        ?string $userinfo = null,
        ?int $port = null,
        ?string $path = null,
        ?string $query = null,
        ?string $fragment = null,
    ): UrlMatch {
        $url = new Url($full, $scheme, $userinfo, $host, $port, $path, $query, $fragment);
        return new UrlMatch($start, $url);
    }
}
