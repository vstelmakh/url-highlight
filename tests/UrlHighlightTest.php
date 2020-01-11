<?php

namespace VStelmakh\UrlHighlight\Tests;

use VStelmakh\UrlHighlight\UrlHighlight;
use PHPUnit\Framework\TestCase;

class UrlHighlightTest extends TestCase
{
    /**
     * List of urls to test. First argument - url, second - protocol, third - indicates if is valid url.
     */
    private const URLS = [
        // Simple
        ['http://example.com', 'http', true],
        ['http://example.com/', 'http', true],
        ['http://example.com/path', 'http', true],
        ['http://example.com/path/', 'http', true],
        ['http://example.com/index.html', 'http', true],
        ['http://example.com/app.php/some/path', 'http', true],
        ['http://user:password@example.com/some/path?var1=1&var2=abc#anchor', 'http', true],
        ['https://user:password@example.com/some/path?var1=1&var2=abc#anchor', 'https', true],
        ['http://www.example.com', 'http', true],
        ['http://subdomain.example.com', 'http', true],
        ['http://example.com/with,commas,in,url', 'http', true],

        // Brackets
        ['http://example.com/path_with_(brackets)', 'http', true],
        ['http://example.com/path_with_(brackets)_another_(brackets_2)', 'http', true],
        ['http://example.com/path_with_(brackets)/another_(brackets_2)', 'http', true],
        ['http://example.com/path_with_(brackets)/another_(another(inside))', 'http', true],
        ['http://example.com/path_with_(brackets)#anchor-1', 'http', true],
        ['http://example.com/path_with_(brackets)_continue#anchor-1', 'http', true],
        ['http://example.com/unicode_(★)_in_brackets', 'http', true],
        ['http://example.com/(brackets)?var=value', 'http', true],

        // Unicode
        ['http://★unicode.com/path', 'http', true],
        ['http://➡★.com/互联网', 'http', true],
        ['www.a.tk/互联网', '', true],
        ['http://互联网.ch', 'http', true],
        ['http://互联网.ch/互联网', 'http', true],
        ['http://україна.укр/привіт/світ', 'http', true],

        // Other protocol
        ['mailto:name@example.com', 'mailto', true],
        ['ftp://localhost', 'ftp', true],
        ['custom://example-CUSTOM', 'custom', true],
        ['message://%3d330e4f340905078926r6a4ba78dkf3fd71420c1af6fj@mail.example.com%3e', 'message', true],

        // No protocol
        ['bit.ly/path', '', true],
        ['www.example.com', '', true],
        ['WWW.EXAMPLE.COM', '', true],
        ['★hello.tk/path', '', true],
        ['example.com', '', true],
        ['example.com/', '', true],
        ['subdomain.example.com', '', true],
        ['2.example.com', '', true],
        ['example.name', '', true],
        ['example.xxx', '', true],

        // Combined
        ['http://user:password@example.com:80/with_(brackets)-and-(another(inside))/here-(too+44)/index.php?var1=1+2&var2=abc:@xyz&var3[1]=1&var3[2]=value%202#anchor', 'http', true],

        // Not url
        ['6:00am', '', false],
        ['filename.txt', '', false],
        ['/home/user/', '', false],
        ['/home/user/filename.txt', '', false],
        ['D:/path/to/', '', false],
        ['D:/path/to/filename.txt', '', false],
        ['self::CONSTANT', '', false],

        // Known not match
//      ['http://example.com/quotes-are-"part"', '', true],
    ];

    /**
     * @var UrlHighlight
     */
    private $urlHighlight;

    public function setUp(): void
    {
        $this->urlHighlight = new UrlHighlight();
    }

    /**
     * @dataProvider isUrlDataProvider
     * @param string $string
     * @param bool $expected
     */
    public function testIsUrl(string $string, bool $expected): void
    {
        $actual = $this->urlHighlight->isUrl($string);
        $this->assertEquals($expected, $actual, 'Expected ' . ($expected ? '"true"' : '"false"') . ' for: ' . $string);
    }

    /**
     * @return array|array[]
     */
    public function isUrlDataProvider(): array
    {
        $result = [];
        foreach (self::URLS as [$url, $protocol, $isValid]) {
            $result[] = [$url, $isValid];
        }
        return $result;
    }

    /**
     * @dataProvider getUrlsDataProvider
     * @param string $string
     * @param array|array[] $expected
     */
    public function testGetUrls(string $string, array $expected): void
    {
        $actual = $this->urlHighlight->getUrls($string);
        $this->assertEquals($expected, $actual, 'Input: ' . $string);
    }

    /**
     * @return array|array[]
     */
    public function getUrlsDataProvider(): array
    {
        $result = [];
        foreach (self::URLS as [$url, $protocol, $isValid]) {
            $output = $isValid ? [$url] : [];
            $result[] = [sprintf('Example text before %s and after.', $url), $output];
            $result[] = [sprintf('%s.', $url), $output];
            $result[] = [sprintf('%s,', $url), $output];
            $result[] = [sprintf('<%s>', $url), $output];
            $result[] = [sprintf('<b>%s</b>', $url), $output];
            $result[] = [sprintf('"%s"', $url), $output];
            $result[] = [sprintf('“%s”', $url), $output];
            $result[] = [sprintf('Text with <%s> (including brackets).', $url), $output];
            $result[] = [sprintf('Example text before %s and after. Open filename.txt at 3:00pm. For more info see http://google.com.', $url), array_merge($output, ['http://google.com'])];
        }
        return $result;
    }

    /**
     * @dataProvider highlightUrlsDataProvider
     * @param string $string
     * @param string $expected
     */
    public function testHighlightUrls(string $string, string $expected): void
    {
        $actual = $this->urlHighlight->highlightUrls($string);
        $this->assertEquals($expected, $actual, 'Input: ' . $string);
    }

    /**
     * @return array|array[]
     */
    public function highlightUrlsDataProvider(): array
    {
        $result = [];
        foreach (self::URLS as [$url, $protocol, $isValid]) {
            $href = empty($protocol) ? 'http://' . $url : $url;

            $output = $isValid ? sprintf('Example text before <a href="%s">%s</a> and after.', $href, $url) : sprintf('Example text before %s and after.', $url);
            $result[] = [sprintf('Example text before %s and after.', $url), $output];

            $output = $isValid ? sprintf('With html <p><a href="%s">%s</a></p>', $href, $url) : sprintf('With html <p>%s</p>', $url);
            $result[] = [sprintf('With html <p>%s</p>', $url), $output];

            $output = sprintf('Inside tag attributes <div><img src="%s"></div>', $url);
            $result[] = [sprintf('Inside tag attributes <div><img src="%s"></div>', $url), $output];

            $output = sprintf('Inside link <p><a href="%s">%s</a></p>', $url, $url);
            $result[] = [sprintf('Inside link <p><a href="%s">%s</a></p>', $url, $url), $output];
        }
        return $result;
    }

    /**
     * @dataProvider optionsMatchByTldDataProvider
     * @param bool $matchByTld
     * @param string $input
     * @param array $expectations
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
     * @param string $defaultScheme
     * @param string $input
     * @param array $expectations
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
     * @param array $schemeBlacklist
     * @param string $input
     * @param array $expectations
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
     * @param array $schemeWhitelist
     * @param string $input
     * @param array $expectations
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
