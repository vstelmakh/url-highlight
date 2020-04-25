<?php

namespace VStelmakh\UrlHighlight\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use VStelmakh\UrlHighlight\Match;
use VStelmakh\UrlHighlight\Matcher;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\MatchValidator;

class MatcherTest extends TestCase
{
    /**
     * url, isValid, matchData: [scheme, local, host, tld] or null
     */
    private const URLS = [
        // Simple
        ['http://a', true, ['http', null, null, null]],
        ['http://b.de', true, ['http', null, null, null]],
        ['http://example.com', true, ['http', null, null, null]],
        ['http://example.com/', true, ['http', null, null, null]],
        ['http://example.com/path', true, ['http', null, null, null]],
        ['http://example.com/path/', true, ['http', null, null, null]],
        ['http://example.com/index.html', true, ['http', null, null, null]],
        ['http://example.com/app.php/some/path', true, ['http', null, null, null]],
        ['http://example.com/app.php/some/path/index.html', true, ['http', null, null, null]],
        ['http://www.example.com', true, ['http', null, null, null]],
        ['http://subdomain.example.com', true, ['http', null, null, null]],
        ['http://example-example.com', true, ['http', null, null, null]],
        ['http://sub-domain.ex-ample.com', true, ['http', null, null, null]],
        ['http://user:password@www.example.com/some/path?var1=1&var2=abc#anchor', true, ['http', null, null, null]],

        // Special chars
        ['http://example.com/with,commas,in,url', true, ['http', null, null, null]],
        ['http://example.com/with/%50,co_mm@$,in,url', true, ['http', null, null, null]],

        // Brackets
        ['http://example.com/path_with_(brackets)', true, ['http', null, null, null]],
        ['http://example.com/path_with_(brackets)_another_(brackets_2)', true, ['http', null, null, null]],
        ['http://example.com/path_with_(brackets)/another_(brackets_2)', true, ['http', null, null, null]],
        ['http://example.com/path_with_(brackets)/another_(another(inside))', true, ['http', null, null, null]],
        ['http://example.com/path_with_(brackets)#anchor-1', true, ['http', null, null, null]],
        ['http://example.com/path_with_(brackets)_continue#anchor-1', true, ['http', null, null, null]],
        ['http://example.com/unicode_(★)_in_brackets', true, ['http', null, null, null]],
        ['http://example.com/(brackets)?var=value', true, ['http', null, null, null]],

        // Unicode
        ['http://★unicode.com/path', true, ['http', null, null, null]],
        ['http://➡★.com/互联网', true, ['http', null, null, null]],
        ['http://➡-★.com/互联网', true, ['http', null, null, null]],
        ['http://www.a.tk/互联网', true, ['http', null, null, null]],
        ['http://互联网.ch', true, ['http', null, null, null]],
        ['http://互联网.ch/互联网', true, ['http', null, null, null]],
        ['http://україна.укр/привіт/світ', true, ['http', null, null, null]],

        // Other scheme
        ['https://example.com', true, ['https', null, null, null]],
        ['mailto:name@example.com', true, ['mailto', null, null, null]],
        ['ftp://localhost', true, ['ftp', null, null, null]],
        ['custom://example-CUSTOM', true, ['custom', null, null, null]],
        ['message://3d330e4f340905078926r6a4ba78dkf3fd71420c1af6fj@mail.example.com%3e', true, ['message', null, null, null]],

        // No scheme
        ['b.de', true, [null, null, 'b.de', 'de']],
        ['w.b.de', true, [null, null, 'w.b.de', 'de']],
        ['example.com', true, [null, null, 'example.com', 'com']],
        ['example.com/', true, [null, null, 'example.com', 'com']],
        ['www.example.com', true, [null, null, 'www.example.com', 'com']],
        ['WWW.EXAMPLE.COM', true, [null, null, 'WWW.EXAMPLE.COM', 'COM']],
        ['www.MyExample.com', true, [null, null, 'www.MyExample.com', 'com']],
        ['bit.ly/path', true, [null, null, 'bit.ly', 'ly']],
        ['example.com/app.php/some/path/index.html', true, [null, null, 'example.com', 'com']],
        ['★hello.tk/path', true, [null, null, '★hello.tk', 'tk']],
        ['www.a.tk/互联网', true, [null, null, 'www.a.tk', 'tk']],
        ['example-example.com', true, [null, null, 'example-example.com', 'com']],
        ['subdomain.example.com', true, [null, null, 'subdomain.example.com', 'com']],
        ['sub-domain.example.com', true, [null, null, 'sub-domain.example.com', 'com']],
        ['sub-domain.ex-ample.com', true, [null, null, 'sub-domain.ex-ample.com', 'com']],
        ['2.example.com', true, [null, null, '2.example.com', 'com']],
        ['that.is.long.host.name.example-domain.com', true, [null, null, 'that.is.long.host.name.example-domain.com', 'com']],
        ['example.name', true, [null, null, 'example.name', 'name']],
        ['example.xxx', true, [null, null, 'example.xxx', 'xxx']],
        ['example.com/with/%50,co_mm@$,in,url', true, [null, null, 'example.com', 'com']],
        ['example.com:80', true, [null, null, 'example.com', 'com']],

        // Combined
        [
            'http://user:password@subdomain.example-example.com:80/with_(brackets)-and-(another(inside))/here-(too+44)/index.php?var1=1+2&var2=abc:@xyz&var3[1]=1&var3[2]=value%202#anchor',
            true,
            ['http', null, null, null]
        ],
        [
            'user:password@subdomain.example-example.com:80/with_(brackets)-and-(another(inside))/here-(too+44)/index.php?var1=1+2&var2=abc:@xyz&var3[1]=1&var3[2]=value%202#anchor',
            false,
            [null, 'user:password', 'subdomain.example-example.com', 'com']
        ],

        // Not url
        ['6:00am', false, null],
        ['filename.txt', false, [null, null, 'filename.txt', 'txt']],
        ['/home/user/', false, null],
//        ['/home/user/filename.txt', false, [null, null, 'filename.txt', 'txt']], // TODO: fails on testMatch
        ['D:/path/to/', false, null],
//        ['D:/path/to/filename.txt', false, [null, null, 'filename.txt', 'txt']], // TODO: fails on testMatch
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
//      ['http://example.com/quotes-are-"part"', '', true],
    ];

    /**
     * @var MockObject
     */
    private $matchValidator;

    /**
     * @var Matcher
     */
    private $matcher;

    public function setUp(): void
    {
        $this->matchValidator = $this->createMock(MatchValidator::class);
        $this->matcher = new Matcher($this->matchValidator);
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
        $matchValidatorInvokedCount = ($match === null) ? $this->never() : $this->once();
        $this->matchValidator
            ->expects($matchValidatorInvokedCount)
            ->method('isValidMatch')
            ->willReturn($isValid);

        $expected = ($isValid && $match !== null) ? $match : null;
        $actual = $this->matcher->match($string);
        $this->assertEquals($expected, $actual, 'Dataset: ' . json_encode(['string' => $string, 'isValid' => $isValid, 'match' => $match]));
    }

    /**
     * @return array|array[]
     */
    public function matchDataProvider(): array
    {
        $result = [];
        foreach (self::URLS as [$url, $isValid, $matchData]) {
            $match = ($matchData !== null) ? new Match($url, ...$matchData) : null;
            $result[] = [$url, $isValid, $match];
        }
        return $result;
    }

    /**
     * @dataProvider matchAllDataProvider
     *
     * @param string $string
     * @param array|bool[] $isValidMap
     * @param array|mixed[] $expected
     */
    public function testMatchAll(string $string, array $isValidMap, array $expected): void
    {
        $this->matchValidator
            ->expects($this->exactly(count($isValidMap)))
            ->method('isValidMatch')
            ->willReturnOnConsecutiveCalls(...$isValidMap);

        $actual = $this->matcher->matchAll($string);
        $this->assertEquals($expected, $actual, 'Input: ' . $string);
    }

    /**
     * @return array|array[]
     */
    public function matchAllDataProvider(): array
    {
        $enclosed = ['\'%s\'', '"%s"', '(%s)', '{%s}', '[%s]', '<%s>', 'Example text before %s and after.', 'Text with <%s> (including brackets).'];
        $invalidPrefixChars = ['`', '~', '!', '#', '$', '%', '^', '&', '*', '(', ')', '_', '=', '+', '[', ']', '{', '}', ';', '\'', '"', ',', '<', '>', '?', '«', '»', '“', '”', '‘', '’', '/', '\\', '|', ':', '@', '-', '.'];
        $invalidSuffixChars = ['`', '!', '(', ')', '[', ']', '{', '}', ';', ':', '\'', '"', '.', ',', '<', '>', '?', '«', '»', '“', '”', '‘', '’'];

        $result = [];
        foreach (self::URLS as [$url, $isValid, $matchData]) {
            $match = ($matchData !== null) ? new Match($url, ...$matchData) : null;
            $isValidMap = ($matchData !== null) ? [$isValid] : [];
            $expected = $isValid ? [$match] : [];


            foreach ($enclosed as $item) {
                $result[] = [sprintf($item, $url), $isValidMap, $expected];
            }
            $result[] = [
                sprintf('Example text before %s and after. Open filename.txt at 3:00pm. For more info see http://google.com.', $url),
                array_merge($isValidMap, [false, true]),
                array_merge($expected, [new Match('http://google.com', 'http', null, null, null)])
            ];

            foreach ($invalidPrefixChars as $prefix) {
                $result[] = [$prefix . $url, $isValidMap, $expected];
            }

            foreach ($invalidSuffixChars as $suffix) {
                $result[] = [$url . $suffix, $isValidMap, $expected];
            }
        }
        return $result;
    }

    /**
     * @dataProvider replaceCallbackDataProvider
     *
     * @param string $string
     * @param bool $isValid
     * @param Match|null $expectedMatch
     * @param string $expected
     */
    public function testReplaceCallback(string $string, bool $isValid, ?Match $expectedMatch, string $expected): void
    {
        $matchValidatorInvokedCount = ($expectedMatch === null) ? $this->never() : $this->once();
        $this->matchValidator
            ->expects($matchValidatorInvokedCount)
            ->method('isValidMatch')
            ->willReturn($isValid);


        $callable = function (Match $match) use ($expectedMatch) {
            $this->assertEquals($expectedMatch, $match);
            return 'REPLACE';
        };

        $actual = $this->matcher->replaceCallback($string, $callable);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array|array[]
     */
    public function replaceCallbackDataProvider(): array
    {
        $result = [];
        foreach (self::URLS as [$url, $isValid, $matchData]) {
            $match = ($matchData !== null) ? new Match($url, ...$matchData) : null;
            $string = sprintf('Example text before %s and after.', $url);
            $output = $isValid ? 'Example text before REPLACE and after.' : $string;
            $result[] = [$string, $isValid, $match, $output];
        }
        return $result;
    }
}
