<?php

namespace VStelmakh\UrlHighlight\Tests\Matcher;

use VStelmakh\UrlHighlight\Matcher\Match;
use PHPUnit\Framework\TestCase;

class MatchTest extends TestCase
{
    public function testGetFullMatch(): void
    {
        $match = new Match('http://example.com', 0, 'http://example.com', null, null, null, null, null, null);
        $result = $match->getUrl();
        self::assertSame('http://example.com', $result);
    }

    public function testGetByteOffset(): void
    {
        $match = new Match('', 29, '', null, null, null, null, null, null);
        $result = $match->getByteOffset();
        self::assertSame(29, $result);
    }

    public function testGetUrl(): void
    {
        $match = new Match('http://example.com?a=1&amp;b=2', 0, 'http://example.com?a=1&b=2', null, null, null, null, null, null);
        $result = $match->getUrl();
        self::assertSame('http://example.com?a=1&b=2', $result);
    }

    public function testGetScheme(): void
    {
        $match = new Match('', 0, '', 'http', null, null, null, null, null);
        $result = $match->getScheme();
        self::assertSame('http', $result);
    }

    public function testGetUserinfo(): void
    {
        $match = new Match('', 0, '', null, 'user:password', null, null, null, null);
        $result = $match->getUserinfo();
        self::assertSame('user:password', $result);
    }

    public function testGetHost(): void
    {
        $match = new Match('', 0, '', null, null, 'example.com', null, null, null);
        $result = $match->getHost();
        self::assertSame('example.com', $result);
    }

    public function testGetTld(): void
    {
        $match = new Match('', 0, '', null, null, null, 'com', null, null);
        $result = $match->getTld();
        self::assertSame('com', $result);
    }

    public function testGetPort(): void
    {
        $match = new Match('', 0, '', null, null, null, null, '80', null);
        $result = $match->getPort();
        self::assertSame(80, $result);
    }

    public function testGetPath(): void
    {
        $match = new Match('', 0, '', null, null, null, null, null, 'path/to/page.html');
        $result = $match->getPath();
        self::assertSame('path/to/page.html', $result);
    }
}
