<?php

namespace VStelmakh\UrlHighlight\Tests\Matcher;

use VStelmakh\UrlHighlight\Matcher\Match;
use PHPUnit\Framework\TestCase;

class MatchTest extends TestCase
{
    public function testGetFullMatch(): void
    {
        $match = new Match('http://example.com', 0, 'http://example.com', null, null, null, null);
        $result = $match->getUrl();
        $this->assertSame('http://example.com', $result);
    }

    public function testGetByteOffset(): void
    {
        $match = new Match('', 29, '', null, null, null, null);
        $result = $match->getByteOffset();
        $this->assertSame(29, $result);
    }

    public function testGetUrl(): void
    {
        $match = new Match('http://example.com?a=1&amp;b=2', 0, 'http://example.com?a=1&b=2', null, null, null, null);
        $result = $match->getUrl();
        $this->assertSame('http://example.com?a=1&b=2', $result);
    }

    public function testGetScheme(): void
    {
        $match = new Match('', 0, '', 'http', null, null, null);
        $result = $match->getScheme();
        $this->assertSame('http', $result);
    }

    public function testGetLocal(): void
    {
        $match = new Match('', 0, '', null, 'user:password', null, null);
        $result = $match->getLocal();
        $this->assertSame('user:password', $result);
    }

    public function testGetHost(): void
    {
        $match = new Match('', 0, '', null, null, 'example.com', null);
        $result = $match->getHost();
        $this->assertSame('example.com', $result);
    }

    public function testGetTld(): void
    {
        $match = new Match('', 0, '', null, null, null, 'com');
        $result = $match->getTld();
        $this->assertSame('com', $result);
    }
}
