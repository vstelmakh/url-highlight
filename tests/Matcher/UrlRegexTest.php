<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests\Matcher;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Matcher\UrlMatch;
use VStelmakh\UrlHighlight\Matcher\UrlRegex;
use VStelmakh\UrlHighlight\Url;

class UrlRegexTest extends TestCase
{
    private UrlRegex $urlRegex;

    #[\Override]
    protected function setUp(): void
    {
        $this->urlRegex = new UrlRegex();
    }

    /**
     * @param array<UrlMatch> $expected
     */
    #[DataProvider('findAllDataProvider')]
    public function testFindAll(string $text, array $expected): void
    {
        $actual = iterator_to_array($this->urlRegex->findAll($text), false);
        self::assertEquals($expected, $actual);
    }

    /**
     * @return array<string, array{string, array<UrlMatch>}>
     */
    public static function findAllDataProvider(): array
    {
        return [
            'scheme: http' => [
                'Visit http://example.com now.',
                [self::urlMatch(start: 6, full: 'http://example.com', host: 'example.com', scheme: 'http')],
            ],
            'scheme: https' => [
                'Visit https://example.com now.',
                [self::urlMatch(start: 6, full: 'https://example.com', host: 'example.com', scheme: 'https')],
            ],
            'scheme: ftp' => [
                'Visit ftp://example.com now.',
                [self::urlMatch(start: 6, full: 'ftp://example.com', host: 'example.com', scheme: 'ftp')],
            ],
            'scheme: mailto' => [
                'Visit mailto:user@example.com now.',
                [self::urlMatch(
                    start: 6,
                    full: 'mailto:user@example.com',
                    host: 'example.com',
                    scheme: 'mailto',
                    userinfo: 'user',
                )],
            ],
            'scheme: allowed characters' => [
                'Visit x-custom+scheme.1://example.com now.',
                [self::urlMatch(
                    start: 6,
                    full: 'x-custom+scheme.1://example.com',
                    host: 'example.com',
                    scheme: 'x-custom+scheme.1',
                )],
            ],
            'scheme: uppercase' => [
                'Visit HTTP://example.com now.',
                [self::urlMatch(start: 6, full: 'HTTP://example.com', host: 'example.com', scheme: 'HTTP')],
            ],
            'scheme: mailto uppercase' => [
                'Visit MAILTO:user@example.com now.',
                [self::urlMatch(
                    start: 6,
                    full: 'MAILTO:user@example.com',
                    host: 'example.com',
                    scheme: 'MAILTO',
                    userinfo: 'user',
                )],
            ],
            'scheme: absent' => [
                'Visit example.com now.',
                [self::urlMatch(start: 6, full: 'example.com', host: 'example.com')],
            ],
            'scheme: does not start with digit' => [
                'Visit 1http://example.com now.',
                [self::urlMatch(start: 7, full: 'http://example.com', host: 'example.com', scheme: 'http')],
            ],
            'scheme: requires double slash' => [
                'Visit http:/example.com now.',
                [self::urlMatch(start: 12, full: 'example.com', host: 'example.com')],
            ],
            'scheme: colon alone is not a scheme' => [
                'Visit http:example.com now.',
                [self::urlMatch(start: 11, full: 'example.com', host: 'example.com')],
            ],
            'scheme: double colon is not a scheme' => [
                'Visit http:://localhost now.',
                [self::urlMatch(start: 14, full: 'localhost', host: 'localhost')],
            ],

            'userinfo: user with scheme' => [
                'Visit http://user@example.com now.',
                [self::urlMatch(
                    start: 6,
                    full: 'http://user@example.com',
                    host: 'example.com',
                    scheme: 'http',
                    userinfo: 'user',
                )],
            ],
            'userinfo: user and password with scheme' => [
                'Visit http://user:pass@example.com now.',
                [self::urlMatch(
                    start: 6,
                    full: 'http://user:pass@example.com',
                    host: 'example.com',
                    scheme: 'http',
                    userinfo: 'user:pass',
                )],
            ],
            'userinfo: user without scheme' => [
                'Mail to user@example.com now.',
                [self::urlMatch(start: 8, full: 'user@example.com', host: 'example.com', userinfo: 'user')],
            ],
            'userinfo: user and password without scheme' => [
                'Visit user:pass@example.com now.',
                [self::urlMatch(start: 6, full: 'user:pass@example.com', host: 'example.com', userinfo: 'user:pass')],
            ],
            'userinfo: allowed characters' => [
                'Mail to user.name+tag@example.com now.',
                [self::urlMatch(
                    start: 8,
                    full: 'user.name+tag@example.com',
                    host: 'example.com',
                    userinfo: 'user.name+tag',
                )],
            ],
            'userinfo: unreserved and sub delimiters' => [
                'Visit http://name.sur,name:pa$$w0rd~!+s-t_r%23*(n)=;g@example.com now.',
                [self::urlMatch(
                    start: 6,
                    full: 'http://name.sur,name:pa$$w0rd~!+s-t_r%23*(n)=;g@example.com',
                    host: 'example.com',
                    scheme: 'http',
                    userinfo: 'name.sur,name:pa$$w0rd~!+s-t_r%23*(n)=;g',
                )],
            ],
            'userinfo: digits' => [
                'Visit message://3d330e4f34090507@mail.example.com now.',
                [self::urlMatch(
                    start: 6,
                    full: 'message://3d330e4f34090507@mail.example.com',
                    host: 'mail.example.com',
                    scheme: 'message',
                    userinfo: '3d330e4f34090507',
                )],
            ],
            'userinfo: leading punctuation is excluded' => [
                'Mail to .user@example.com now.',
                [self::urlMatch(start: 9, full: 'user@example.com', host: 'example.com', userinfo: 'user')],
            ],
            'userinfo: leading currency symbol is excluded' => [
                'Mail to $user@example.com now.',
                [self::urlMatch(start: 9, full: 'user@example.com', host: 'example.com', userinfo: 'user')],
            ],
            'userinfo: leading mathematical symbol is excluded' => [
                'Mail to =user@example.com now.',
                [self::urlMatch(start: 9, full: 'user@example.com', host: 'example.com', userinfo: 'user')],
            ],
            'userinfo: leading modifier symbol is excluded' => [
                'Mail to ^user@example.com now.',
                [self::urlMatch(start: 9, full: 'user@example.com', host: 'example.com', userinfo: 'user')],
            ],
            'userinfo: empty' => [
                'Visit @example.com now.',
                [self::urlMatch(start: 7, full: 'example.com', host: 'example.com')],
            ],
            'userinfo: second at sign starts a new match' => [
                'Visit a@b@example.com now.',
                [
                    self::urlMatch(start: 6, full: 'a@b', host: 'b', userinfo: 'a'),
                    self::urlMatch(start: 10, full: 'example.com', host: 'example.com'),
                ],
            ],

            'host: subdomains' => [
                'Visit sub.domain.example.com now.',
                [self::urlMatch(start: 6, full: 'sub.domain.example.com', host: 'sub.domain.example.com')],
            ],
            'host: many labels' => [
                'Visit that.is.long.host.name.example-domain.com now.',
                [self::urlMatch(
                    start: 6,
                    full: 'that.is.long.host.name.example-domain.com',
                    host: 'that.is.long.host.name.example-domain.com',
                )],
            ],
            'host: label starting with digit' => [
                'Visit 2.example.com now.',
                [self::urlMatch(start: 6, full: '2.example.com', host: '2.example.com')],
            ],
            'host: hyphen inside label' => [
                'Visit my-host.com now.',
                [self::urlMatch(start: 6, full: 'my-host.com', host: 'my-host.com')],
            ],
            'host: leading hyphen is excluded' => [
                'Visit -host.com now.',
                [self::urlMatch(start: 7, full: 'host.com', host: 'host.com')],
            ],
            'host: trailing hyphen prevents match' => [
                'Visit host-.com now.',
                [],
            ],
            'host: leading hyphen in second label prevents match' => [
                'Visit example.-com now.',
                [],
            ],
            'host: trailing hyphen ends the match' => [
                'Visit example.c-.com now.',
                [self::urlMatch(start: 6, full: 'example.c', host: 'example.c')],
            ],
            'host: quote ends the match' => [
                'Visit example.c"om now.',
                [self::urlMatch(start: 6, full: 'example.c', host: 'example.c')],
            ],
            'host: mathematical symbol ends the match' => [
                'Visit example.c+om now.',
                [self::urlMatch(start: 6, full: 'example.c', host: 'example.c')],
            ],
            'host: currency symbol ends the match' => [
                'Visit example.c$om now.',
                [self::urlMatch(start: 6, full: 'example.c', host: 'example.c')],
            ],
            'host: empty label prevents match' => [
                'Visit example..com now.',
                [],
            ],
            'host: trailing dot ends the match' => [
                'Visit example.com. now.',
                [self::urlMatch(start: 6, full: 'example.com', host: 'example.com')],
            ],
            'host: single label requires scheme or userinfo' => [
                'Visit localhost now.',
                [],
            ],
            'host: single label with scheme' => [
                'Visit http://localhost now.',
                [self::urlMatch(start: 6, full: 'http://localhost', host: 'localhost', scheme: 'http')],
            ],
            'host: single label with userinfo' => [
                'Mail to user@localhost now.',
                [self::urlMatch(start: 8, full: 'user@localhost', host: 'localhost', userinfo: 'user')],
            ],
            'host: ip address' => [
                'Visit 127.0.0.1 now.',
                [self::urlMatch(start: 6, full: '127.0.0.1', host: '127.0.0.1')],
            ],
            'host: internationalized' => [
                'Visit україна.укр now.',
                [self::urlMatch(start: 6, full: 'україна.укр', host: 'україна.укр')],
            ],
            'host: chinese' => [
                'Visit 互联网.ch now.',
                [self::urlMatch(start: 6, full: '互联网.ch', host: '互联网.ch')],
            ],
            'host: punycode' => [
                'Visit xn--80aikifvh.xn--j1amh now.',
                [self::urlMatch(start: 6, full: 'xn--80aikifvh.xn--j1amh', host: 'xn--80aikifvh.xn--j1amh')],
            ],
            'host: other symbols are allowed' => [
                'Visit ★unicode.com now.',
                [self::urlMatch(start: 6, full: '★unicode.com', host: '★unicode.com')],
            ],
            'host: zero width joiner is allowed' => [
                "Visit a\u{200D}b.com now.",
                [self::urlMatch(start: 6, full: "a\u{200D}b.com", host: "a\u{200D}b.com")],
            ],
            'host: zero width joiner is allowed, unicode' => [
                'Visit ශ්‍රී.com now.',
                [self::urlMatch(start: 6, full: 'ශ්‍රී.com', host: 'ශ්‍රී.com')],
            ],
            'host: zero width non joiner is allowed' => [
                "Visit a\u{200C}b.com now.",
                [self::urlMatch(start: 6, full: "a\u{200C}b.com", host: "a\u{200C}b.com")],
            ],
            'host: zero width non joiner is allowed, RTL' => [
                'Visit نامه‌ای.com now.',
                [self::urlMatch(start: 6, full: 'نامه‌ای.com', host: 'نامه‌ای.com')],
            ],
            'host: zero width space breaks the label' => [
                "Visit a\u{200B}b.com now.",
                [self::urlMatch(start: 10, full: 'b.com', host: 'b.com')],
            ],
            'host: middle dot is allowed' => [
                "Visit a\u{00B7}b.com now.",
                [self::urlMatch(start: 6, full: "a\u{00B7}b.com", host: "a\u{00B7}b.com")],
            ],
            'host: greek lower numeral sign is allowed' => [
                "Visit a\u{0375}b.com now.",
                [self::urlMatch(start: 6, full: "a\u{0375}b.com", host: "a\u{0375}b.com")],
            ],
            'host: hebrew punctuation geresh is allowed' => [
                "Visit a\u{05F3}b.com now.",
                [self::urlMatch(start: 6, full: "a\u{05F3}b.com", host: "a\u{05F3}b.com")],
            ],
            'host: hebrew punctuation gershayim is allowed' => [
                "Visit a\u{05F4}b.com now.",
                [self::urlMatch(start: 6, full: "a\u{05F4}b.com", host: "a\u{05F4}b.com")],
            ],
            'host: katakana middle dot is allowed' => [
                "Visit a\u{30FB}b.com now.",
                [self::urlMatch(start: 6, full: "a\u{30FB}b.com", host: "a\u{30FB}b.com")],
            ],
            'host: arabic-indic digits are allowed' => [
                "Visit \u{0661}\u{0662}\u{0663}.com now.",
                [self::urlMatch(
                    start: 6,
                    full: "\u{0661}\u{0662}\u{0663}.com",
                    host: "\u{0661}\u{0662}\u{0663}.com",
                )],
            ],
            'host: extended arabic-indic digits are allowed' => [
                "Visit \u{06F1}\u{06F2}\u{06F3}.com now.",
                [self::urlMatch(
                    start: 6,
                    full: "\u{06F1}\u{06F2}\u{06F3}.com",
                    host: "\u{06F1}\u{06F2}\u{06F3}.com",
                )],
            ],
            'host: uppercase is preserved' => [
                'Visit EXAMPLE.COM now.',
                [self::urlMatch(start: 6, full: 'EXAMPLE.COM', host: 'EXAMPLE.COM')],
            ],
            'host: label of maximum length' => [
                'Visit ' . str_repeat('a', 63) . '.com now.',
                [self::urlMatch(
                    start: 6,
                    full: str_repeat('a', 63) . '.com',
                    host: str_repeat('a', 63) . '.com',
                )],
            ],
            'host: label longer than maximum drops leading characters' => [
                'Visit ' . str_repeat('a', 64) . '.com now.',
                [self::urlMatch(
                    start: 7,
                    full: str_repeat('a', 63) . '.com',
                    host: str_repeat('a', 63) . '.com',
                )],
            ],

            'port: with scheme' => [
                'Visit http://example.com:8080 now.',
                [self::urlMatch(
                    start: 6,
                    full: 'http://example.com:8080',
                    host: 'example.com',
                    scheme: 'http',
                    port: 8080,
                )],
            ],
            'port: without scheme' => [
                'Visit example.com:8080 now.',
                [self::urlMatch(start: 6, full: 'example.com:8080', host: 'example.com', port: 8080)],
            ],
            'port: followed by path' => [
                'Visit example.com:8080/path now.',
                [self::urlMatch(
                    start: 6,
                    full: 'example.com:8080/path',
                    host: 'example.com',
                    port: 8080,
                    path: '/path',
                )],
            ],
            'port: followed by query' => [
                'Visit example.com:8080?a=1 now.',
                [self::urlMatch(
                    start: 6,
                    full: 'example.com:8080?a=1',
                    host: 'example.com',
                    port: 8080,
                    query: '?a=1',
                )],
            ],
            'port: zero' => [
                'Visit example.com:0 now.',
                [self::urlMatch(start: 6, full: 'example.com:0', host: 'example.com', port: 0)],
            ],
            'port: colon without digits is excluded' => [
                'Visit example.com: now.',
                [self::urlMatch(start: 6, full: 'example.com', host: 'example.com')],
            ],
            'port: non digits are excluded' => [
                'Visit example.com:port now.',
                [self::urlMatch(start: 6, full: 'example.com', host: 'example.com')],
            ],

            'path: root' => [
                'Visit example.com/ now.',
                [self::urlMatch(start: 6, full: 'example.com/', host: 'example.com', path: '/')],
            ],
            'path: segments' => [
                'Visit example.com/a/b/c.html now.',
                [self::urlMatch(start: 6, full: 'example.com/a/b/c.html', host: 'example.com', path: '/a/b/c.html')],
            ],
            'path: percent encoded' => [
                'Visit example.com/a%20b now.',
                [self::urlMatch(start: 6, full: 'example.com/a%20b', host: 'example.com', path: '/a%20b')],
            ],
            'path: unicode' => [
                'Visit example.com/приклад now.',
                [self::urlMatch(start: 6, full: 'example.com/приклад', host: 'example.com', path: '/приклад')],
            ],
            'path: commas' => [
                'Visit example.com/with,commas,in,url now.',
                [self::urlMatch(
                    start: 6,
                    full: 'example.com/with,commas,in,url',
                    host: 'example.com',
                    path: '/with,commas,in,url',
                )],
            ],
            'path: at sign and currency symbol' => [
                'Visit example.com/with/%50,co_mm@$,in,url now.',
                [self::urlMatch(
                    start: 6,
                    full: 'example.com/with/%50,co_mm@$,in,url',
                    host: 'example.com',
                    path: '/with/%50,co_mm@$,in,url',
                )],
            ],
            'path: nested brackets' => [
                'Visit example.com/with_(brackets)/another_(another(inside)) now.',
                [self::urlMatch(
                    start: 6,
                    full: 'example.com/with_(brackets)/another_(another(inside))',
                    host: 'example.com',
                    path: '/with_(brackets)/another_(another(inside))',
                )],
            ],
            'path: ends at whitespace' => [
                'Visit example.com/a b now.',
                [self::urlMatch(start: 6, full: 'example.com/a', host: 'example.com', path: '/a')],
            ],
            'path: ends at query' => [
                'Visit example.com/a?b now.',
                [self::urlMatch(start: 6, full: 'example.com/a?b', host: 'example.com', path: '/a', query: '?b')],
            ],
            'path: ends at fragment' => [
                'Visit example.com/a#b now.',
                [self::urlMatch(start: 6, full: 'example.com/a#b', host: 'example.com', path: '/a', fragment: '#b')],
            ],

            'query: without path' => [
                'Visit example.com?a=1 now.',
                [self::urlMatch(start: 6, full: 'example.com?a=1', host: 'example.com', query: '?a=1')],
            ],
            'query: multiple parameters' => [
                'Visit example.com/path?a=1&b=2 now.',
                [self::urlMatch(
                    start: 6,
                    full: 'example.com/path?a=1&b=2',
                    host: 'example.com',
                    path: '/path',
                    query: '?a=1&b=2',
                )],
            ],
            'query: empty' => [
                'Visit example.com/path? now.',
                [self::urlMatch(start: 6, full: 'example.com/path?', host: 'example.com', path: '/path', query: '?')],
            ],
            'query: at sign is not userinfo' => [
                'Visit example.com/?email=user@host.com now.',
                [self::urlMatch(
                    start: 6,
                    full: 'example.com/?email=user@host.com',
                    host: 'example.com',
                    path: '/',
                    query: '?email=user@host.com',
                )],
            ],
            'query: brackets and colon' => [
                'Visit example.com/i.php?var1=abc:@xyz&var3[1]=value%202 now.',
                [self::urlMatch(
                    start: 6,
                    full: 'example.com/i.php?var1=abc:@xyz&var3[1]=value%202',
                    host: 'example.com',
                    path: '/i.php',
                    query: '?var1=abc:@xyz&var3[1]=value%202',
                )],
            ],
            'query: ends at whitespace' => [
                'Visit example.com/path?a=1 b now.',
                [self::urlMatch(
                    start: 6,
                    full: 'example.com/path?a=1',
                    host: 'example.com',
                    path: '/path',
                    query: '?a=1',
                )],
            ],
            'query: ends at fragment' => [
                'Visit example.com/path?a=1#top now.',
                [self::urlMatch(
                    start: 6,
                    full: 'example.com/path?a=1#top',
                    host: 'example.com',
                    path: '/path',
                    query: '?a=1',
                    fragment: '#top',
                )],
            ],

            'fragment: without path' => [
                'Visit example.com#top now.',
                [self::urlMatch(start: 6, full: 'example.com#top', host: 'example.com', fragment: '#top')],
            ],
            'fragment: with path' => [
                'Visit example.com/path#top now.',
                [self::urlMatch(
                    start: 6,
                    full: 'example.com/path#top',
                    host: 'example.com',
                    path: '/path',
                    fragment: '#top',
                )],
            ],
            'fragment: empty' => [
                'Visit example.com/path# now.',
                [self::urlMatch(
                    start: 6,
                    full: 'example.com/path#',
                    host: 'example.com',
                    path: '/path',
                    fragment: '#',
                )],
            ],
            'fragment: question mark is included' => [
                'Visit example.com/path#a?b now.',
                [self::urlMatch(
                    start: 6,
                    full: 'example.com/path#a?b',
                    host: 'example.com',
                    path: '/path',
                    fragment: '#a?b',
                )],
            ],
            'fragment: client side route' => [
                "Visit example.com/app#/discover?_g=()&_a=(index:'deve-*') now.",
                [self::urlMatch(
                    start: 6,
                    full: "example.com/app#/discover?_g=()&_a=(index:'deve-*')",
                    host: 'example.com',
                    path: '/app',
                    fragment: "#/discover?_g=()&_a=(index:'deve-*')",
                )],
            ],
            'fragment: ends at whitespace' => [
                'Visit example.com/path#a b now.',
                [self::urlMatch(
                    start: 6,
                    full: 'example.com/path#a',
                    host: 'example.com',
                    path: '/path',
                    fragment: '#a',
                )],
            ],

            // Matches everything URL-shaped, the host validator and the punctuation filter will narrow it down.
            'over match: trailing punctuation is included' => [
                '(see example.com/path).',
                [self::urlMatch(start: 5, full: 'example.com/path).', host: 'example.com', path: '/path).')],
            ],
            'over match: file name' => [
                'Open filename.txt please.',
                [self::urlMatch(start: 5, full: 'filename.txt', host: 'filename.txt')],
            ],
            'over match: version number' => [
                'Version 1.2.3 released.',
                [self::urlMatch(start: 8, full: '1.2.3', host: '1.2.3')],
            ],
            'over match: unknown top level domain' => [
                'Visit a.b now.',
                [self::urlMatch(start: 6, full: 'a.b', host: 'a.b')],
            ],
            'over match: unix file path' => [
                '/home/user/filename.txt',
                [self::urlMatch(start: 11, full: 'filename.txt', host: 'filename.txt')],
            ],
            'over match: windows file path' => [
                'D:/path/to/filename.txt',
                [self::urlMatch(start: 11, full: 'filename.txt', host: 'filename.txt')],
            ],
            'over match: file scheme url' => [
                'Open file:///home/user/note.txt please.',
                [self::urlMatch(
                    start: 13,
                    full: 'home/user/note.txt',
                    host: 'home',
                    path: '/user/note.txt',
                )],
            ],

            'enclosed: angle brackets' => [
                '<example.com>',
                [self::urlMatch(start: 1, full: 'example.com', host: 'example.com')],
            ],
            'enclosed: square brackets' => [
                '[example.com]',
                [self::urlMatch(start: 1, full: 'example.com', host: 'example.com')],
            ],
            'enclosed: quotes' => [
                '"example.com"',
                [self::urlMatch(start: 1, full: 'example.com', host: 'example.com')],
            ],
            'enclosed: guillemets' => [
                '«example.com»',
                [self::urlMatch(start: 2, full: 'example.com', host: 'example.com')],
            ],
            'preceded by: underscore' => [
                '_example.com',
                [self::urlMatch(start: 1, full: 'example.com', host: 'example.com')],
            ],
            'preceded by: slash' => [
                '/example.com',
                [self::urlMatch(start: 1, full: 'example.com', host: 'example.com')],
            ],
            'preceded by: backslash' => [
                '\\example.com',
                [self::urlMatch(start: 1, full: 'example.com', host: 'example.com')],
            ],

            'find all: order of appearance' => [
                'See a.com,b.com and http://c.com/x end',
                [
                    self::urlMatch(start: 4, full: 'a.com', host: 'a.com'),
                    self::urlMatch(start: 10, full: 'b.com', host: 'b.com'),
                    self::urlMatch(start: 20, full: 'http://c.com/x', host: 'c.com', scheme: 'http', path: '/x'),
                ],
            ],

            'find all: offset at the start of text' => [
                'example.com is here',
                [self::urlMatch(start: 0, full: 'example.com', host: 'example.com')],
            ],
            'find all: offset counted in bytes' => [
                'Тест example.com кінець.',
                [self::urlMatch(start: 9, full: 'example.com', host: 'example.com')],
            ],

            'no match: empty text' => ['', []],
            'no match: whitespace only' => ['   ', []],
            'no match: single word' => ['word', []],
            'no match: sentence' => ['Nothing to see here', []],
            'no match: time' => ['At 3:00pm today.', []],
            'no match: at sign only' => ['@', []],
            'no match: scheme without host' => ['http://', []],
            'no match: class constant' => ['self::CONSTANT', []],
            'no match: user and password without host' => ['user:admin', []],
            'no match: directory path' => ['/home/user/', []],
            'no match: ip version 6 is not supported' => ['Visit http://[::1]:8080/x now.', []],
        ];
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
