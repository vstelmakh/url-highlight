<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Format;
use VStelmakh\UrlHighlight\Highlighter\CallbackHighlighter;
use VStelmakh\UrlHighlight\Url;
use VStelmakh\UrlHighlight\UrlHighlight;

class UrlHighlightTest extends TestCase
{
    private UrlHighlight $urlHighlight;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlHighlight = new UrlHighlight();
    }

    public function testHighlightUsesSimpleHighlighterByDefault(): void
    {
        $input = 'Check the example.com website.';
        $actual = $this->urlHighlight->highlight($input);
        self::assertSame('Check the <a href="http://example.com">example.com</a> website.', $actual);
    }

    public function testHighlightUsesHtmlFormatByDefault(): void
    {
        $input = 'Skip <a href="http://example.com">example.com</a> link.';
        $actual = $this->urlHighlight->highlight($input);
        self::assertSame($input, $actual);
    }

    public function testHighlightUsesGivenHighlighter(): void
    {
        $input = 'Visit example.com now.';
        $highlighter = new CallbackHighlighter(static fn (Url $url): string => "[{$url->full}]");
        $actual = $this->urlHighlight->highlight($input, $highlighter);
        self::assertSame('Visit [example.com] now.', $actual);
    }

    public function testHighlightIsIdempotent(): void
    {
        $input = 'See example.com now.';
        $once = $this->urlHighlight->highlight($input);
        $twice = $this->urlHighlight->highlight($once);
        self::assertSame($once, $twice);
    }

    #[DataProvider('highlightUrlDataProvider')]
    public function testHighlightUrl(string $input, string $expected): void
    {
        $actual = $this->urlHighlight->highlight($input);
        self::assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function highlightUrlDataProvider(): array
    {
        return [
            'bare host' => [
                'Visit example.com now.',
                'Visit <a href="http://example.com">example.com</a> now.',
            ],
            'www host' => [
                'Visit www.example.com now.',
                'Visit <a href="http://www.example.com">www.example.com</a> now.',
            ],
            'subdomain with path' => [
                'Visit docs.example.com/guide now.',
                'Visit <a href="http://docs.example.com/guide">docs.example.com/guide</a> now.',
            ],
            'http scheme' => [
                'Visit http://example.com now.',
                'Visit <a href="http://example.com">http://example.com</a> now.',
            ],
            'https with path' => [
                'Visit https://example.com/path/page.html now.',
                'Visit <a href="https://example.com/path/page.html">https://example.com/path/page.html</a> now.',
            ],
            'query and fragment' => [
                'Visit https://example.com/search?q=test#top now.',
                'Visit <a href="https://example.com/search?q=test#top">https://example.com/search?q=test#top</a> now.',
            ],
            'host with port' => [
                'Visit http://example.com:8080/status now.',
                'Visit <a href="http://example.com:8080/status">http://example.com:8080/status</a> now.',
            ],
            'ip address with port' => [
                'Visit http://127.0.0.1:8080/health now.',
                'Visit <a href="http://127.0.0.1:8080/health">http://127.0.0.1:8080/health</a> now.',
            ],
            'unicode host and multibyte text around it' => [
                'Тест приклад.укр кінець.',
                'Тест <a href="http://приклад.укр">приклад.укр</a> кінець.',
            ],
            'unicode path' => [
                'Visit example.com/тест now.',
                'Visit <a href="http://example.com/тест">example.com/тест</a> now.',
            ],
            'email' => [
                'Mail user@example.com now.',
                'Mail <a href="mailto:user@example.com">user@example.com</a> now.',
            ],
            'email with mailto scheme' => [
                'Mail mailto:user@example.com now.',
                'Mail <a href="mailto:user@example.com">mailto:user@example.com</a> now.',
            ],
            'unicode email' => [
                'Mail користувач@приклад.укр now.',
                'Mail <a href="mailto:користувач@приклад.укр">користувач@приклад.укр</a> now.',
            ],

            'trailing period' => [
                'See example.com.',
                'See <a href="http://example.com">example.com</a>.',
            ],
            'trailing comma' => [
                'See example.com, then leave.',
                'See <a href="http://example.com">example.com</a>, then leave.',
            ],
            'trailing question mark' => [
                'Is it example.com?',
                'Is it <a href="http://example.com">example.com</a>?',
            ],
            'trailing exclamation mark' => [
                'Wow example.com!',
                'Wow <a href="http://example.com">example.com</a>!',
            ],
            'period after path' => [
                'Open example.com/path/page.html.',
                'Open <a href="http://example.com/path/page.html">example.com/path/page.html</a>.',
            ],
            'enclosed in parentheses' => [
                'See (example.com) now.',
                'See (<a href="http://example.com">example.com</a>) now.',
            ],
            'enclosed in quotes' => [
                'See "example.com" now.',
                'See "<a href="http://example.com">example.com</a>" now.',
            ],
            'parentheses inside the path are kept' => [
                'Read example.com/a_(b) now.',
                'Read <a href="http://example.com/a_(b)">example.com/a_(b)</a> now.',
            ],

            'several urls in one text' => [
                'See a.com and b.org, or mail user@c.net.',
                'See <a href="http://a.com">a.com</a> and <a href="http://b.org">b.org</a>,'
                    . ' or mail <a href="mailto:user@c.net">user@c.net</a>.',
            ],
            'text without url is unchanged' => [
                'Open filename.txt at 3:00pm. Version 1.2.3 released.',
                'Open filename.txt at 3:00pm. Version 1.2.3 released.',
            ],
            'special characters of the url are escaped' => [
                'Visit http://example.com/?a=1&b=2 now.',
                'Visit <a href="http://example.com/?a=1&amp;b=2">http://example.com/?a=1&amp;b=2</a> now.',
            ],
        ];
    }

    #[DataProvider('highlightPlainDataProvider')]
    public function testHighlightPlain(string $input, string $expected): void
    {
        $actual = $this->urlHighlight->highlight($input, format: Format::Plain);
        self::assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function highlightPlainDataProvider(): array
    {
        return [
            'url in text' => [
                'Example text before example.com and after.',
                'Example text before <a href="http://example.com">example.com</a> and after.',
            ],
            'markup is not interpreted' => [
                '<img src="http://example.com/logo.png" alt="Logo">',
                '<img src="<a href="http://example.com/logo.png">http://example.com/logo.png</a>" alt="Logo">',
            ],
            'content of a skipped element is highlighted' => [
                'Skip <script>var u = "http://example.com";</script> end.',
                'Skip <script>var u = "<a href="http://example.com">http://example.com</a>";</script> end.',
            ],
            'angle brackets are ordinary characters' => [
                'Visit <https://example.com> now.',
                'Visit <<a href="https://example.com">https://example.com</a>> now.',
            ],
        ];
    }

    #[DataProvider('highlightHtmlDataProvider')]
    public function testHighlightHtml(string $input, string $expected): void
    {
        $actual = $this->urlHighlight->highlight($input, format: Format::Html);
        self::assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function highlightHtmlDataProvider(): array
    {
        return [
            'element text is highlighted, markup is not' => [
                '<p>Visit example.com</p><img src="http://example.com/logo.png" alt="Logo">',
                '<p>Visit <a href="http://example.com">example.com</a></p>'
                    . '<img src="http://example.com/logo.png" alt="Logo">',
            ],
            'content of a skipped element is not highlighted' => [
                'Skip <script>var u = "http://example.com";</script> end.',
                'Skip <script>var u = "http://example.com";</script> end.',
            ],
            'angle brackets are markup' => [
                'Visit <https://example.com> now.',
                'Visit <https://example.com> now.',
            ],
        ];
    }

    #[DataProvider('highlightHtmlEncodedDataProvider')]
    public function testHighlightHtmlEncoded(string $input, string $expected): void
    {
        $actual = $this->urlHighlight->highlight($input, format: Format::HtmlEncoded);
        self::assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function highlightHtmlEncodedDataProvider(): array
    {
        return [
            'text without entities' => [
                'Plain text http://example.com here.',
                'Plain text <a href="http://example.com">http://example.com</a> here.',
            ],
            'url in escaped angle brackets' => [
                'Visit &lt;example.com&gt; now.',
                'Visit &lt;<a href="http://example.com">example.com</a>&gt; now.',
            ],
            'entity inside the url is preserved' => [
                'Encoded amp http://example.com?a=1&amp;b=2 done.',
                'Encoded amp <a href="http://example.com?a=1&amp;b=2">http://example.com?a=1&amp;b=2</a> done.',
            ],
            'url inside encoded attribute and link text' => [
                '&lt;a href=&quot;http://example.com?q=query&quot;&gt;example.com?q=query&lt;/a&gt;',
                '&lt;a href=&quot;<a href="http://example.com?q=query">http://example.com?q=query</a>&quot;&gt;'
                    . '<a href="http://example.com?q=query">example.com?q=query</a>&lt;/a&gt;',
            ],
            'encoded markup mixed with raw html' => [
                '&lt;a href=&quot;http://example.com?q=query&quot;&gt;example.com?q=query&lt;/a&gt;'
                    . '<a href="http://example.com">example.com</a>',
                '&lt;a href=&quot;<a href="http://example.com?q=query">http://example.com?q=query</a>&quot;&gt;'
                    . '<a href="http://example.com?q=query">example.com?q=query</a>&lt;/a&gt;'
                    . '<a href="http://example.com">example.com</a>',
            ],
        ];
    }

    /**
     * @param array<string> $expected
     */
    #[DataProvider('findDataProvider')]
    public function testFind(string $input, array $expected): void
    {
        $matches = $this->urlHighlight->find($input);
        $actual = array_map(static fn (Url $url): string => $url->full, $matches);
        self::assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{string, array<string>}>
     */
    public static function findDataProvider(): array
    {
        return [
            'urls among non urls' => [
                'Example text before http://example.com/app/some/path/index.html and after.'
                    . ' Open filename.txt at 3:00pm. For more info see example.com.',
                ['http://example.com/app/some/path/index.html', 'example.com'],
            ],
            'url inside markup is found' => [
                '<a href="mailto:hello@example.com">Example</a>',
                ['mailto:hello@example.com'],
            ],
            'no url' => [
                'not url',
                [],
            ],
        ];
    }

    public function testFindReturnsUrlWithParsedComponents(): void
    {
        $input = 'Go to https://user@example.com:8080/path?a=1#top now.';

        $actual = $this->urlHighlight->find($input);

        $expected = [new Url(
            full: 'https://user@example.com:8080/path?a=1#top',
            scheme: 'https',
            userinfo: 'user',
            host: 'example.com',
            port: 8080,
            path: '/path',
            query: '?a=1',
            fragment: '#top',
        )];

        self::assertEquals($expected, $actual);
    }
}
