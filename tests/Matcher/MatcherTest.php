<?php

namespace VStelmakh\UrlHighlight\Tests\Matcher;

use PHPUnit\Framework\MockObject\MockObject;
use VStelmakh\UrlHighlight\Matcher\UrlMatch;
use VStelmakh\UrlHighlight\Matcher\Matcher;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Validator\Validator;

class MatcherTest extends TestCase
{
    /**
     * url, isValid, matchData: [fullMatch (null = url), scheme, local, host, tld, port, path] or null
     */
    private const URLS = [
        // Simple
        ['http://a', true, [null, 'http', null, 'a', null, null, null]],
        ['http://b.de', true, [null, 'http', null, 'b.de', 'de', null, null]],
        ['http://example.com', true, [null, 'http', null, 'example.com', 'com', null, null]],
        ['http://example.com/', true, [null, 'http', null, 'example.com', 'com', null, '/']],
        ['http://example.com/path', true, [null, 'http', null, 'example.com', 'com', null, '/path']],
        ['http://example.com/path/', true, [null, 'http', null, 'example.com', 'com', null, '/path/']],
        ['http://example.com/index.html', true, [null, 'http', null, 'example.com', 'com', null, '/index.html']],
        ['http://example.com/app.php/some/path', true, [null, 'http', null, 'example.com', 'com', null, '/app.php/some/path']],
        ['http://example.com/app.php/some/path/index.html', true, [null, 'http', null, 'example.com', 'com', null, '/app.php/some/path/index.html']],
        ['http://www.example.com', true, [null, 'http', null, 'www.example.com', 'com', null, null]],
        ['http://subdomain.example.com', true, [null, 'http', null, 'subdomain.example.com', 'com', null, null]],
        ['http://example-example.com', true, [null, 'http', null, 'example-example.com', 'com', null, null]],
        ['http://sub-domain.ex-ample.com', true, [null, 'http', null, 'sub-domain.ex-ample.com', 'com', null, null]],
        ['http://user:password@www.example.com/some/path?var1=1&var2=abc#anchor', true, [null, 'http', 'user:password', 'www.example.com', 'com', null, '/some/path?var1=1&var2=abc#anchor']],

        // Special chars
        ['http://example.com/with,commas,in,url', true, [null, 'http', null, 'example.com', 'com', null, '/with,commas,in,url']],
        ['http://example.com/with/%50,co_mm@$,in,url', true, [null, 'http', null, 'example.com', 'com', null, '/with/%50,co_mm@$,in,url']],

        // Brackets
        ['http://example.com/path_with_(brackets)', true, [null, 'http', null, 'example.com', 'com', null, '/path_with_(brackets)']],
        ['http://example.com/path_with_(brackets)_another_(brackets_2)', true, [null, 'http', null, 'example.com', 'com', null, '/path_with_(brackets)_another_(brackets_2)']],
        ['http://example.com/path_with_(brackets)/another_(brackets_2)', true, [null, 'http', null, 'example.com', 'com', null, '/path_with_(brackets)/another_(brackets_2)']],
        ['http://example.com/path_with_(brackets)/another_(another(inside))', true, [null, 'http', null, 'example.com', 'com', null, '/path_with_(brackets)/another_(another(inside))']],
        ['http://example.com/path_with_(brackets)#anchor-1', true, [null, 'http', null, 'example.com', 'com', null, '/path_with_(brackets)#anchor-1']],
        ['http://example.com/path_with_(brackets)_continue#anchor-1', true, [null, 'http', null, 'example.com', 'com', null, '/path_with_(brackets)_continue#anchor-1']],
        ['http://example.com/unicode_(★)_in_brackets', true, [null, 'http', null, 'example.com', 'com', null, '/unicode_(★)_in_brackets']],
        ['http://example.com/(brackets)?var=value', true, [null, 'http', null, 'example.com', 'com', null, '/(brackets)?var=value']],

        // Unicode
        ['http://★unicode.com/path', true, [null, 'http', null, '★unicode.com', 'com', null, '/path']],
        ['http://➡★.com/互联网', true, [null, 'http', null, '➡★.com', 'com', null, '/互联网']],
        ['http://➡-★.com/互联网', true, [null, 'http', null, '➡-★.com', 'com', null, '/互联网']],
        ['http://www.a.tk/互联网', true, [null, 'http', null, 'www.a.tk', 'tk', null, '/互联网']],
        ['http://互联网.ch', true, [null, 'http', null, '互联网.ch', 'ch', null, null]],
        ['http://互联网.ch/互联网', true, [null, 'http', null, '互联网.ch', 'ch', null, '/互联网']],
        ['http://україна.укр/привіт/світ', true, [null, 'http', null, 'україна.укр', 'укр', null, '/привіт/світ']],

        // Other scheme
        ['https://example.com', true, [null, 'https', null, 'example.com', 'com', null, null]],
        ['mailto:name@example.com', true, [null, 'mailto', 'name', 'example.com', 'com', null, null]],
        ['ftp://localhost', true, [null, 'ftp', null, 'localhost', null, null, null]],
        ['custom://example-CUSTOM', true, [null, 'custom', null, 'example-CUSTOM', null, null, null]],
        ['message://3d330e4f340905078926r6a4ba78dkf3fd71420c1af6fj@mail.example.com', true, [null, 'message', '3d330e4f340905078926r6a4ba78dkf3fd71420c1af6fj', 'mail.example.com', 'com', null, null]],

        // No scheme
        ['b.de', true, [null, null, null, 'b.de', 'de', null, null]],
        ['w.b.de', true, [null, null, null, 'w.b.de', 'de', null, null]],
        ['example.com', true, [null, null, null, 'example.com', 'com', null, null]],
        ['example.com/', true, [null, null, null, 'example.com', 'com', null, '/']],
        ['www.example.com', true, [null, null, null, 'www.example.com', 'com', null, null]],
        ['WWW.EXAMPLE.COM', true, [null, null, null, 'WWW.EXAMPLE.COM', 'COM', null, null]],
        ['www.MyExample.com', true, [null, null, null, 'www.MyExample.com', 'com', null, null]],
        ['bit.ly/path', true, [null, null, null, 'bit.ly', 'ly', null, '/path']],
        ['example.com/app.php/some/path/index.html', true, [null, null, null, 'example.com', 'com', null, '/app.php/some/path/index.html']],
        ['★hello.tk/path', true, [null, null, null, '★hello.tk', 'tk', null, '/path']],
        ['www.a.tk/互联网', true, [null, null, null, 'www.a.tk', 'tk', null, '/互联网']],
        ['example-example.com', true, [null, null, null, 'example-example.com', 'com', null, null]],
        ['subdomain.example.com', true, [null, null, null, 'subdomain.example.com', 'com', null, null]],
        ['sub-domain.example.com', true, [null, null, null, 'sub-domain.example.com', 'com', null, null]],
        ['sub-domain.ex-ample.com', true, [null, null, null, 'sub-domain.ex-ample.com', 'com', null, null]],
        ['2.example.com', true, [null, null, null, '2.example.com', 'com', null, null]],
        ['that.is.long.host.name.example-domain.com', true, [null, null, null, 'that.is.long.host.name.example-domain.com', 'com', null, null]],
        ['example.name', true, [null, null, null, 'example.name', 'name', null, null]],
        ['example.xxx', true, [null, null, null, 'example.xxx', 'xxx', null, null]],
        ['example.com/with/%50,co_mm@$,in,url', true, [null, null, null, 'example.com', 'com', null, '/with/%50,co_mm@$,in,url']],
        ['example.com:80', true, [null, null, null, 'example.com', 'com', '80', null]],

        // Combined
        [
            'http://user:password@subdomain.example-example.com:80/with_(brackets)-and-(another(inside))/here-(too+44)/index.php?var1=1+2&var2=abc:@xyz&var3[1]=1&var3[2]=value%202#anchor',
            true,
            [null, 'http', 'user:password', 'subdomain.example-example.com', 'com', '80', '/with_(brackets)-and-(another(inside))/here-(too+44)/index.php?var1=1+2&var2=abc:@xyz&var3[1]=1&var3[2]=value%202#anchor']
        ],
        [
            'user:password@subdomain.example-example.com:80/with_(brackets)-and-(another(inside))/here-(too+44)/index.php?var1=1+2&var2=abc:@xyz&var3[1]=1&var3[2]=value%202#anchor',
            true,
            [null, null, 'user:password', 'subdomain.example-example.com', 'com', '80', '/with_(brackets)-and-(another(inside))/here-(too+44)/index.php?var1=1+2&var2=abc:@xyz&var3[1]=1&var3[2]=value%202#anchor']
        ],
        [
            'http://elk.example.com/app/kibana#/discover?_g=()&_a=(columns:!(_source),index:\'deve-*\',interval:auto,query:(query_string:(analyze_wildcard:!t,query:\'*\')),sort:!(\'@timestamp\',desc))',
            true,
            [null, 'http', null, 'elk.example.com', 'com', null, '/app/kibana#/discover?_g=()&_a=(columns:!(_source),index:\'deve-*\',interval:auto,query:(query_string:(analyze_wildcard:!t,query:\'*\')),sort:!(\'@timestamp\',desc))']
        ],

        // Not url
        ['6:00am', false, null],
        ['filename.txt', false, [null, null, null, 'filename.txt', 'txt', null, null]],
        ['/home/user/', false, null],
        ['/home/user/filename.txt', false, ['filename.txt', null, null, 'filename.txt', 'txt', null, null]],
        ['D:/path/to/', false, null],
        ['D:/path/to/filename.txt', false, ['filename.txt', null, null, 'filename.txt', 'txt', null, null]],
        ['self::CONSTANT', false, null],
        ['user:admin', false, null],
        ['http://', false, null],
        ['http:://localhost', false, null],

        // Invalid hosts
        ['example-.com', false, null],
        ['example.-com', false, null],
        ['example..com', false, null],
        ['example.-.com', false, null],
        ['example.-.com', false, null],
        ['example.c-.com', false, null],
        ['example.c"om', false, null],

        // Known not match
//      ['http://example.com/quotes-are-"part"', true, [null, 'http', null, null, null]],
    ];

    /**
     * @var Validator&MockObject
     */
    private $validator;

    /**
     * @var Matcher
     */
    private $matcher;

    public function setUp(): void
    {
        $this->validator = $this->createMock(Validator::class);
        $this->matcher = new Matcher($this->validator);
    }

    /**
     * @dataProvider matchDataProvider
     *
     * @param string $string
     * @param bool $isValid
     * @param UrlMatch|null $match
     */
    public function testMatch(string $string, bool $isValid, ?UrlMatch $match): void
    {
        $matchValidatorInvokedCount = ($match === null) ? self::never() : self::once();
        $this->validator
            ->expects($matchValidatorInvokedCount)
            ->method('isValidMatch')
            ->willReturn($isValid);

        $expected = ($isValid && $match !== null) ? $match : null;
        $actual = $this->matcher->match($string);
        self::assertEquals($expected, $actual, 'Dataset: ' . json_encode(['string' => $string, 'isValid' => $isValid, 'match' => $match]));
    }

    /**
     * @return mixed[]
     */
    public function matchDataProvider(): array
    {
        $result = [];
        foreach (self::URLS as [$url, $isValid, $matchData]) {
            $match = $this->getMatchDataAsMatch($url, 0, $matchData, true);
            $result[] = [$url, $isValid, $match];
        }
        return $result;
    }

    /**
     * @dataProvider matchAllDataProvider
     *
     * @param string $string
     * @param array&bool[] $isValidMap
     * @param array|mixed[] $expected
     */
    public function testMatchAll(string $string, array $isValidMap, array $expected): void
    {
        $this->validator
            ->expects(self::exactly(count($isValidMap)))
            ->method('isValidMatch')
            ->willReturnOnConsecutiveCalls(...$isValidMap);

        $actual = $this->matcher->matchAll($string);
        self::assertEquals($expected, $actual, 'Dataset: ' . $string);
    }

    /**
     * @return mixed[]
     */
    public function matchAllDataProvider(): array
    {
        $enclosed = ['\'%s\'' => 1, '"%s"' => 1, '(%s)' => 1, '{%s}' => 1, '[%s]' => 1, '<%s>' => 1, 'Example text before %s and after.' => 20, 'Text with <%s> (including brackets).' => 11];
        $invalidPrefixChars = ['`', '~', '!', '#', '$', '%', '^', '&', '*', '(', ')', '_', '=', '+', '[', ']', '{', '}', ';', '\'', '"', ',', '<', '>', '?', '«', '»', '“', '”', '‘', '’', '/', '\\', '|', ':', '@', '-', '.'];
        $invalidSuffixChars = ['`', '!', '(', ')', '[', ']', '{', '}', ';', ':', '\'', '"', '.', ',', '<', '>', '?', '«', '»', '“', '”', '‘', '’'];

        $result = [];
        foreach (self::URLS as [$url, $isValid, $matchData]) {
            if ($isValid) {
                $isValidMap = ($matchData !== null) ? [$isValid] : [];

                foreach ($enclosed as $item => $byteOffset) {
                    $expected = [$this->getMatchDataAsMatch($url, $byteOffset, $matchData, false)];
                    $result[] = [sprintf($item, $url), $isValidMap, $expected];
                }

                $expected = [$this->getMatchDataAsMatch($url, 20, $matchData, false)];
                $secondMatchByteOffset = 79 + strlen($url);
                $result[] = [
                    sprintf('Example text before %s and after. Open filename.txt at 3:00pm. For more info see http://google.com.', $url),
                    array_merge($isValidMap, [false, true]),
                    array_merge($expected, [new UrlMatch('http://google.com', $secondMatchByteOffset, 'http://google.com', 'http', null, 'google.com', 'com', null, null)])
                ];

                foreach ($invalidPrefixChars as $prefix) {
                    $expected = [$this->getMatchDataAsMatch($url, strlen($prefix), $matchData, false)];
                    $result[] = [$prefix . $url, $isValidMap, $expected];
                }

                foreach ($invalidSuffixChars as $suffix) {
                    $expected = [$this->getMatchDataAsMatch($url, 0, $matchData, false)];
                    $result[] = [$url . $suffix, $isValidMap, $expected];
                }
            }
        }
        return $result;
    }

    /**
     * @param string $url
     * @param array<string|null>|null $matchData
     * @param bool $isStrict
     * @param int $byteOffset
     * @return UrlMatch|null
     */
    private function getMatchDataAsMatch(string $url, int $byteOffset, ?array $matchData, bool $isStrict): ?UrlMatch
    {
        if ($matchData === null) {
            return null;
        }

        if ($matchData[0] === null) {
            $matchData[0] = $url;
        }

        if ($isStrict && $matchData[0] !== $url) {
            return null;
        }

        return new UrlMatch($matchData[0], $byteOffset, ...$matchData); // @phpstan-ignore-line
    }
}
