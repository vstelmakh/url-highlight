<?php

namespace VStelmakh\UrlHighlight\Tests\Matcher;

use PHPUnit\Framework\MockObject\MockObject;
use VStelmakh\UrlHighlight\Matcher\Match;
use VStelmakh\UrlHighlight\Matcher\Matcher;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Validator\Validator;

class MatcherTest extends TestCase
{
    /**
     * url, isValid, matchData: [fullMatch (null = url), scheme, local, host, tld] or null
     */
    private const URLS = [
        // Simple
        ['http://a', true, [null, 'http', null, null, null]],
        ['http://b.de', true, [null, 'http', null, null, null]],
        ['http://example.com', true, [null, 'http', null, null, null]],
        ['http://example.com/', true, [null, 'http', null, null, null]],
        ['http://example.com/path', true, [null, 'http', null, null, null]],
        ['http://example.com/path/', true, [null, 'http', null, null, null]],
        ['http://example.com/index.html', true, [null, 'http', null, null, null]],
        ['http://example.com/app.php/some/path', true, [null, 'http', null, null, null]],
        ['http://example.com/app.php/some/path/index.html', true, [null, 'http', null, null, null]],
        ['http://www.example.com', true, [null, 'http', null, null, null]],
        ['http://subdomain.example.com', true, [null, 'http', null, null, null]],
        ['http://example-example.com', true, [null, 'http', null, null, null]],
        ['http://sub-domain.ex-ample.com', true, [null, 'http', null, null, null]],
        ['http://user:password@www.example.com/some/path?var1=1&var2=abc#anchor', true, [null, 'http', null, null, null]],

        // Special chars
        ['http://example.com/with,commas,in,url', true, [null, 'http', null, null, null]],
        ['http://example.com/with/%50,co_mm@$,in,url', true, [null, 'http', null, null, null]],

        // Brackets
        ['http://example.com/path_with_(brackets)', true, [null, 'http', null, null, null]],
        ['http://example.com/path_with_(brackets)_another_(brackets_2)', true, [null, 'http', null, null, null]],
        ['http://example.com/path_with_(brackets)/another_(brackets_2)', true, [null, 'http', null, null, null]],
        ['http://example.com/path_with_(brackets)/another_(another(inside))', true, [null, 'http', null, null, null]],
        ['http://example.com/path_with_(brackets)#anchor-1', true, [null, 'http', null, null, null]],
        ['http://example.com/path_with_(brackets)_continue#anchor-1', true, [null, 'http', null, null, null]],
        ['http://example.com/unicode_(★)_in_brackets', true, [null, 'http', null, null, null]],
        ['http://example.com/(brackets)?var=value', true, [null, 'http', null, null, null]],

        // Unicode
        ['http://★unicode.com/path', true, [null, 'http', null, null, null]],
        ['http://➡★.com/互联网', true, [null, 'http', null, null, null]],
        ['http://➡-★.com/互联网', true, [null, 'http', null, null, null]],
        ['http://www.a.tk/互联网', true, [null, 'http', null, null, null]],
        ['http://互联网.ch', true, [null, 'http', null, null, null]],
        ['http://互联网.ch/互联网', true, [null, 'http', null, null, null]],
        ['http://україна.укр/привіт/світ', true, [null, 'http', null, null, null]],

        // Other scheme
        ['https://example.com', true, [null, 'https', null, null, null]],
        ['mailto:name@example.com', true, [null, 'mailto', null, null, null]],
        ['ftp://localhost', true, [null, 'ftp', null, null, null]],
        ['custom://example-CUSTOM', true, [null, 'custom', null, null, null]],
        ['message://3d330e4f340905078926r6a4ba78dkf3fd71420c1af6fj@mail.example.com%3e', true, [null, 'message', null, null, null]],

        // No scheme
        ['b.de', true, [null, null, null, 'b.de', 'de']],
        ['w.b.de', true, [null, null, null, 'w.b.de', 'de']],
        ['example.com', true, [null, null, null, 'example.com', 'com']],
        ['example.com/', true, [null, null, null, 'example.com', 'com']],
        ['www.example.com', true, [null, null, null, 'www.example.com', 'com']],
        ['WWW.EXAMPLE.COM', true, [null, null, null, 'WWW.EXAMPLE.COM', 'COM']],
        ['www.MyExample.com', true, [null, null, null, 'www.MyExample.com', 'com']],
        ['bit.ly/path', true, [null, null, null, 'bit.ly', 'ly']],
        ['example.com/app.php/some/path/index.html', true, [null, null, null, 'example.com', 'com']],
        ['★hello.tk/path', true, [null, null, null, '★hello.tk', 'tk']],
        ['www.a.tk/互联网', true, [null, null, null, 'www.a.tk', 'tk']],
        ['example-example.com', true, [null, null, null, 'example-example.com', 'com']],
        ['subdomain.example.com', true, [null, null, null, 'subdomain.example.com', 'com']],
        ['sub-domain.example.com', true, [null, null, null, 'sub-domain.example.com', 'com']],
        ['sub-domain.ex-ample.com', true, [null, null, null, 'sub-domain.ex-ample.com', 'com']],
        ['2.example.com', true, [null, null, null, '2.example.com', 'com']],
        ['that.is.long.host.name.example-domain.com', true, [null, null, null, 'that.is.long.host.name.example-domain.com', 'com']],
        ['example.name', true, [null, null, null, 'example.name', 'name']],
        ['example.xxx', true, [null, null, null, 'example.xxx', 'xxx']],
        ['example.com/with/%50,co_mm@$,in,url', true, [null, null, null, 'example.com', 'com']],
        ['example.com:80', true, [null, null, null, 'example.com', 'com']],

        // Combined
        [
            'http://user:password@subdomain.example-example.com:80/with_(brackets)-and-(another(inside))/here-(too+44)/index.php?var1=1+2&var2=abc:@xyz&var3[1]=1&var3[2]=value%202#anchor',
            true,
            [null, 'http', null, null, null]
        ],
        [
            'user:password@subdomain.example-example.com:80/with_(brackets)-and-(another(inside))/here-(too+44)/index.php?var1=1+2&var2=abc:@xyz&var3[1]=1&var3[2]=value%202#anchor',
            true,
            [null, null, 'user:password', 'subdomain.example-example.com', 'com']
        ],

        // Not url
        ['6:00am', false, null],
        ['filename.txt', false, [null, null, null, 'filename.txt', 'txt']],
        ['/home/user/', false, null],
        ['/home/user/filename.txt', false, ['filename.txt', null, null, 'filename.txt', 'txt']],
        ['D:/path/to/', false, null],
        ['D:/path/to/filename.txt', false, ['filename.txt', null, null, 'filename.txt', 'txt']],
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
     * @param Match|null $match
     */
    public function testMatch(string $string, bool $isValid, ?Match $match): void
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
     * @return array|array[]
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
     * @return array&array[]
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
                    array_merge($expected, [new Match('http://google.com', $secondMatchByteOffset, 'http://google.com', 'http', null, null, null)])
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
     * @param array|mixed[]|null $matchData
     * @param bool $isStrict
     * @param int $byteOffset
     * @return Match|null
     */
    private function getMatchDataAsMatch(string $url, int $byteOffset, ?array $matchData, bool $isStrict): ?Match
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

        return new Match($matchData[0], $byteOffset, ...$matchData);
    }
}
