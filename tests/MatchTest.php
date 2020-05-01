<?php

namespace VStelmakh\UrlHighlight\Tests;

use VStelmakh\UrlHighlight\Match;
use PHPUnit\Framework\TestCase;

class MatchTest extends TestCase
{
    public function testGetFullMatch(): void
    {
        $match = new Match('http://example.com', null, null, null, null, null);
        $result = $match->getFullMatch();
        $this->assertSame('http://example.com', $result);
    }

    public function testGetScheme(): void
    {
        $match = new Match('', 'http', null, null, null, null);
        $result = $match->getScheme();
        $this->assertSame('http', $result);
    }

    public function testGetLocal(): void
    {
        $match = new Match('', null, 'user:password', null, null, null);
        $result = $match->getLocal();
        $this->assertSame('user:password', $result);
    }

    public function testGetHost(): void
    {
        $match = new Match('', null, null, 'example.com', null, null);
        $result = $match->getHost();
        $this->assertSame('example.com', $result);
    }

    public function testGetTld(): void
    {
        $match = new Match('', null, null, null, 'com', null);
        $result = $match->getTld();
        $this->assertSame('com', $result);
    }

    public function testGetByteOffset(): void
    {
        $match = new Match('', null, null, null, null, 29);
        $result = $match->getByteOffset();
        $this->assertSame(29, $result);
    }
}
