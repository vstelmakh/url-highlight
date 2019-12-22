<?php

namespace VStelmakh\UrlHighlight\Tests;

use VStelmakh\UrlHighlight\UrlHighlight;
use PHPUnit\Framework\TestCase;

class UrlHighlightTest extends TestCase
{
    /**
     * List of urls to test. First argument - url, second - indicates if is valid url.
     */
    private const URLS = [
        // Simple
        ['http://example.com', true],
        ['http://example.com/', true],
        ['http://example.com/path', true],
        ['http://example.com/path/', true],
        ['http://example.com/index.html', true],
        ['http://example.com/app.php/some/path', true],
        ['http://user:password@example.com/some/path?var1=1&var2=abc#anchor', true],
        ['https://user:password@example.com/some/path?var1=1&var2=abc#anchor', true],
        ['http://www.example.com', true],
        ['http://subdomain.example.com', true],
        ['http://example.com/with,commas,in,url', true],

        // Brackets
        ['http://example.com/path_with_(brackets)', true],
        ['http://example.com/path_with_(brackets)_another_(brackets_2)', true],
        ['http://example.com/path_with_(brackets)/another_(brackets_2)', true],
        ['http://example.com/path_with_(brackets)/another_(another(inside))', true],
        ['http://example.com/path_with_(brackets)#anchor-1', true],
        ['http://example.com/path_with_(brackets)_continue#anchor-1', true],
        ['http://example.com/unicode_(★)_in_brackets', true],
        ['http://example.com/(brackets)?var=value', true],

        // Unicode
        ['http://★unicode.com/path', true],
        ['http://➡★.com/互联网', true],
        ['www.a.tk/互联网', true],
        ['http://互联网.ch', true],
        ['http://互联网.ch/互联网', true],
        ['http://україна.укр/привіт/світ', true],

        // Other protocol
        ['mailto:name@example.com', true],
        ['ftp://localhost', true],
        ['custom://example-CUSTOM', true],
        ['message://%3d330e4f340905078926r6a4ba78dkf3fd71420c1af6fj@mail.example.com%3e', true],

        // No protocol
        ['bit.ly/path', true],
        ['www.example.com', true],
        ['WWW.EXAMPLE.COM', true],

        // Combined
        ['http://user:password@example.com/with_(brackets)-and-(another(inside))/here-(too+44)/index.php?var1=1+2&var2=abc:@xyz&var3[1]=1&var3[2]=value%202#anchor', true],

        // Not url
        ['6:00am', false],
        ['filename.txt', false],
        ['/home/user/', false],
        ['/home/user/filename.txt', false],
        ['D:/path/to/', false],
        ['D:/path/to/filename.txt', false],
        ['self::CONSTANT', false],

        // Known not match
//            ['http://example.com/quotes-are-"part"', true],
//            ['★hello.tk/path', true],
//            ['example.com', true],
//            ['example.com/', true],
//            ['subdomain.example.com', true],
//            ['2.example.com', true],
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
        return self::URLS;
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
        $urls = self::URLS;
        foreach ($urls as [$url, $isValid]) {
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
        $urls = self::URLS;
        foreach ($urls as [$url, $isValid]) {
            $output = $isValid ? sprintf('Example text before <a href="%s">%s</a> and after.', $url, $url) : sprintf('Example text before %s and after.', $url);
            $result[] = [sprintf('Example text before %s and after.', $url), $output];

            $output = $isValid ? sprintf('With html <p><a href="%s">%s</a></p>', $url, $url) : sprintf('With html <p>%s</p>', $url);
            $result[] = [sprintf('With html <p>%s</p>', $url), $output];

            $output = sprintf('Inside tag attributes <div><img src="%s"></div>', $url);
            $result[] = [sprintf('Inside tag attributes <div><img src="%s"></div>', $url), $output];

            $output = sprintf('Inside link <p><a href="%s">%s</a></p>', $url, $url);
            $result[] = [sprintf('Inside link <p><a href="%s">%s</a></p>', $url, $url), $output];
        }
        return $result;
    }
}
