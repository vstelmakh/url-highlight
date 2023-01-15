<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests\Encoder;

use VStelmakh\UrlHighlight\Encoder\HtmlSpecialcharsEncoder;
use PHPUnit\Framework\TestCase;

class HtmlSpecialcharsEncoderTest extends TestCase
{
    /** @var HtmlSpecialcharsEncoder */
    private $htmlSpecialcharsEncoder;

    public function setUp(): void
    {
        $this->htmlSpecialcharsEncoder = new HtmlSpecialcharsEncoder();
    }

    /**
     * @dataProvider decodeDataProvider
     *
     * @param string $string
     * @param string $expected
     */
    public function testDecode(string $string, string $expected): void
    {
        $actual = $this->htmlSpecialcharsEncoder->decode($string);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return mixed[]
     */
    public function decodeDataProvider(): array
    {
        return [
            'literal' => [
                '&lt;a href&equals;&quot;http://example&period;com&quot;&gt;Example&lt;&sol;a&gt;',
                '<a href="http://example.com">Example</a>',
            ],
            'dec' => [
                '&#60;a href=&#34;http://example.com&#34;&#62;Example&#60;/a&#62;',
                '<a href="http://example.com">Example</a>',
            ],
            'hex' => [
                '&#x0003C;a href=&#x00022;http://example.com&#x00022;&#x0003E;Example&#x0003C;/a&#x0003E;',
                '<a href="http://example.com">Example</a>',
            ],
        ];
    }

    /**
     * @dataProvider getEncodedCharRegexDataProvider
     *
     * @param string $char
     * @param string $delimiter
     * @param string $expected
     */
    public function testGetEncodedCharRegex(string $char, string $delimiter, string $expected): void
    {
        $actual = $this->htmlSpecialcharsEncoder->getEncodedCharRegex($char, $delimiter);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return mixed[]
     */
    public function getEncodedCharRegexDataProvider(): array
    {
        return [
            ['a', '/', 'a'],
            ['/', '/', '\/'],
            ['&', '/', '&|&amp;|&#0*38;|&#x0*26;'],
            ['"', '/', '"|&quot;|&#0*34;|&#x0*22;'],
            ['\'', '/', '\'|&apos;|&#0*39;|&#x0*27;'],
            ['<', '/', '\<|&lt;|&#0*60;|&#x0*3c;'],
            ['>', '/', '\>|&gt;|&#0*62;|&#x0*3e;'],
        ];
    }

    public function testGetSupportedChars(): void
    {
        $actual = $this->htmlSpecialcharsEncoder->getSupportedChars();
        $this->assertSame($actual, ['&', '"', '\'', '<', '>']);
    }
}
