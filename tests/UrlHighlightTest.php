<?php

namespace VStelmakh\UrlHighlight\Tests;

use VStelmakh\UrlHighlight\UrlHighlight;
use PHPUnit\Framework\TestCase;

class UrlHighlightTest extends TestCase
{
    /**
     * Generic test. For full test cases see MatcherTest::testMatch
     *
     * @dataProvider isUrlDataProvider
     *
     * @param string $string
     * @param bool $expected
     */
    public function testIsUrl(string $string, bool $expected): void
    {
        $urlHighlight = new UrlHighlight();
        $actual = $urlHighlight->isUrl($string);
        $this->assertEquals($expected, $actual, 'Expected ' . ($expected ? '"true"' : '"false"') . ' for: ' . $string);
    }

    /**
     * @return array|array[]
     */
    public function isUrlDataProvider(): array
    {
        return [
            ['http://example.com', true],
            ['not url', false],
        ];
    }

    /**
     * Generic test. For full test cases see MatcherTest::testMatchAll
     *
     * @dataProvider getUrlsDataProvider
     *
     * @param string $string
     * @param array|array[] $expected
     */
    public function testGetUrls(string $string, array $expected): void
    {
        $urlHighlight = new UrlHighlight();
        $actual = $urlHighlight->getUrls($string);
        $this->assertEquals($expected, $actual, 'Input: ' . $string);
    }

    /**
     * @return array|array[]
     */
    public function getUrlsDataProvider(): array
    {
        return [
            [
                'Example text before http://example.com/app.php/some/path/index.html and after. Open filename.txt at 3:00pm. For more info see https://google.com.',
                ['http://example.com/app.php/some/path/index.html', 'https://google.com'],
            ],
            [
                'not url',
                [],
            ],
        ];
    }

    /**
     * Generic test. For full test cases see HighlighterTest::testHighlightUrls
     *
     * @dataProvider highlightUrlsDataProvider
     *
     * @param string $highlightType
     * @param string $string
     * @param string $expected
     */
    public function testHighlightUrls(?string $highlightType, string $string, string $expected): void
    {
        $urlHighlight = new UrlHighlight();

        if ($highlightType === 'unsupported_type') {
            $this->expectException(\InvalidArgumentException::class);
        }

        $actual = $highlightType
            ? $urlHighlight->highlightUrls($string, $highlightType)
            : $urlHighlight->highlightUrls($string);

        $this->assertEquals($expected, $actual, 'Input: ' . $string);
    }

    /**
     * @return array|array[]
     */
    public function highlightUrlsDataProvider(): array
    {
        return [
            [
                null,
                'Example text before http://example.com and after.',
                'Example text before <a href="http://example.com">http://example.com</a> and after.',
            ],
            [
                UrlHighlight::HIGHLIGHT_TYPE_PLAIN_TEXT,
                'With html <p>http://example.com</p>',
                'With html <p><a href="http://example.com">http://example.com</a></p>',
            ],
            [
                UrlHighlight::HIGHLIGHT_TYPE_PLAIN_TEXT,
                'Example text before example.com and after.',
                'Example text before <a href="http://example.com">example.com</a> and after.',
            ],
            [
                UrlHighlight::HIGHLIGHT_TYPE_PLAIN_TEXT,
                'With html <p>example.com</p>',
                'With html <p><a href="http://example.com">example.com</a></p>',
            ],
            [
                UrlHighlight::HIGHLIGHT_TYPE_PLAIN_TEXT,
                '&lt;a href=&quot;http://example.com&quot;&gt;example.com&lt;/a&gt;',
                '&lt;a href=&quot;<a href="http://example.com&quot;&gt;example.com&lt;/a&gt">http://example.com&quot;&gt;example.com&lt;/a&gt</a>;',
            ],
            [
                UrlHighlight::HIGHLIGHT_TYPE_HTML_SPECIAL_CHARS,
                '&lt;a href=&quot;http://example.com&quot;&gt;example.com&lt;/a&gt;',
                '&lt;a href=&quot;<a href="http://example.com">http://example.com</a>&quot;&gt;<a href="http://example.com">example.com</a>&lt;/a&gt;',
            ],
            [
                'unsupported_type',
                '',
                '',
            ],
        ];
    }

    /**
     * @dataProvider optionsMatchByTldDataProvider
     *
     * @param bool $matchByTld
     * @param string $input
     * @param array|mixed[] $expectations
     */
    public function testOptionsMatchByTld(bool $matchByTld, string $input, array $expectations): void
    {
        $options = ['match_by_tld' => $matchByTld];
        $urlHighlight = new UrlHighlight($options);

        $isUrl = $urlHighlight->isUrl($input);
        $this->assertEquals($expectations['isUrl'], $isUrl, 'Options: ' . json_encode($options));

        $urls = $urlHighlight->getUrls($input);
        $this->assertEquals($expectations['getUrls'], $urls, 'Options: ' . json_encode($options));

        $highlight = $urlHighlight->highlightUrls($input);
        $this->assertEquals($expectations['highlightUrls'], $highlight, 'Options: ' . json_encode($options));
    }

    /**
     * @return array|array[]
     */
    public function optionsMatchByTldDataProvider(): array
    {
        return [
            [
                true,
                'example.com',
                [
                    'isUrl' => true,
                    'getUrls' => ['example.com'],
                    'highlightUrls' => '<a href="http://example.com">example.com</a>',
                ]
            ],
            [
                false,
                'example.com',
                [
                    'isUrl' => false,
                    'getUrls' => [],
                    'highlightUrls' => 'example.com',
                ]
            ],
        ];
    }

    /**
     * @dataProvider optionsDefaultSchemeDataProvider
     *
     * @param string $defaultScheme
     * @param string $input
     * @param array|mixed[] $expectations
     */
    public function testOptionsDefaultScheme(string $defaultScheme, string $input, array $expectations): void
    {
        $options = ['default_scheme' => $defaultScheme];
        $urlHighlight = new UrlHighlight($options);

        $highlight = $urlHighlight->highlightUrls($input);
        $this->assertEquals($expectations['highlightUrls'], $highlight, 'Options: ' . json_encode($options));
    }

    /**
     * @return array|array[]
     */
    public function optionsDefaultSchemeDataProvider(): array
    {
        return [
            [
                'http',
                'example.com',
                [
                    'highlightUrls' => '<a href="http://example.com">example.com</a>',
                ]
            ],
            [
                'https',
                'example.com',
                [
                    'highlightUrls' => '<a href="https://example.com">example.com</a>',
                ]
            ],
            [
                'ftp',
                'example.com',
                [
                    'highlightUrls' => '<a href="ftp://example.com">example.com</a>',
                ]
            ],
        ];
    }

    /**
     * @dataProvider optionsSchemeBlacklistDataProvider
     *
     * @param array|string[] $schemeBlacklist
     * @param string $input
     * @param array|mixed[] $expectations
     */
    public function testOptionsSchemeBlacklist(array $schemeBlacklist, string $input, array $expectations): void
    {
        $options = ['scheme_blacklist' => $schemeBlacklist];
        $urlHighlight = new UrlHighlight($options);

        $isUrl = $urlHighlight->isUrl($input);
        $this->assertEquals($expectations['isUrl'], $isUrl, 'Options: ' . json_encode($options));

        $urls = $urlHighlight->getUrls($input);
        $this->assertEquals($expectations['getUrls'], $urls, 'Options: ' . json_encode($options));

        $highlight = $urlHighlight->highlightUrls($input);
        $this->assertEquals($expectations['highlightUrls'], $highlight, 'Options: ' . json_encode($options));
    }

    /**
     * @return array|array[]
     */
    public function optionsSchemeBlacklistDataProvider(): array
    {
        return [
            [
                [],
                'http://example.com',
                [
                    'isUrl' => true,
                    'getUrls' => ['http://example.com'],
                    'highlightUrls' => '<a href="http://example.com">http://example.com</a>',
                ]
            ],
            [
                ['http'],
                'http://example.com',
                [
                    'isUrl' => false,
                    'getUrls' => [],
                    'highlightUrls' => 'http://example.com',
                ]
            ],
        ];
    }

    /**
     * @dataProvider optionsSchemeWhitelistDataProvider
     *
     * @param array|string[] $schemeWhitelist
     * @param string $input
     * @param array|mixed[] $expectations
     */
    public function testOptionsSchemeWhitelist(array $schemeWhitelist, string $input, array $expectations): void
    {
        $options = ['scheme_whitelist' => $schemeWhitelist];
        $urlHighlight = new UrlHighlight($options);

        $isUrl = $urlHighlight->isUrl($input);
        $this->assertEquals($expectations['isUrl'], $isUrl, 'Options: ' . json_encode($options));

        $urls = $urlHighlight->getUrls($input);
        $this->assertEquals($expectations['getUrls'], $urls, 'Options: ' . json_encode($options));

        $highlight = $urlHighlight->highlightUrls($input);
        $this->assertEquals($expectations['highlightUrls'], $highlight, 'Options: ' . json_encode($options));
    }

    /**
     * @return array|array[]
     */
    public function optionsSchemeWhitelistDataProvider(): array
    {
        return [
            [
                [],
                'http://example.com',
                [
                    'isUrl' => true,
                    'getUrls' => ['http://example.com'],
                    'highlightUrls' => '<a href="http://example.com">http://example.com</a>',
                ]
            ],
            [
                ['http'],
                'http://example.com',
                [
                    'isUrl' => true,
                    'getUrls' => ['http://example.com'],
                    'highlightUrls' => '<a href="http://example.com">http://example.com</a>',
                ]
            ],
            [
                ['https'],
                'http://example.com',
                [
                    'isUrl' => false,
                    'getUrls' => [],
                    'highlightUrls' => 'http://example.com',
                ]
            ],
        ];
    }
}
