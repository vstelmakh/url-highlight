<?php

namespace VStelmakh\UrlHighlight\Tests\Encoder;

use VStelmakh\UrlHighlight\Encoder\HtmlEntitiesEncoder;
use PHPUnit\Framework\TestCase;

class HtmlEntitiesEncoderTest extends TestCase
{
    /**
     * @var HtmlEntitiesEncoder
     */
    private $htmlEntitiesEncoder;

    public function setUp(): void
    {
        $this->htmlEntitiesEncoder = new HtmlEntitiesEncoder();
    }

    /**
     * @dataProvider decodeDataProvider
     *
     * @param string $string
     * @param string $expected
     */
    public function testDecode(string $string, string $expected): void
    {
        $actual = $this->htmlEntitiesEncoder->decode($string);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return array&array[]
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
        $actual = $this->htmlEntitiesEncoder->getEncodedCharRegex($char, $delimiter);
        $this->assertSame($expected, $actual);
    }

    /**
     * @return array&array[]
     */
    public function getEncodedCharRegexDataProvider(): array
    {
        return [
            ['a', '/', 'a|&#0*97;|&#x0*61;'],
            ['"', '/', '"|&quot;|&#0*34;|&#x0*22;'],
            ['/', '/', '\/|&#0*47;|&#x0*2f;'],
            [' ', '/', ' |&#0*32;|&#x0*20;'],
            ['', '/', ''],
        ];
    }

    public function testGetSupportedChars(): void
    {
        $actual = $this->htmlEntitiesEncoder->getSupportedChars();
        $this->assertNull($actual);
    }
}
