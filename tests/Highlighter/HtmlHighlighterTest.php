<?php

namespace VStelmakh\UrlHighlight\Tests\Highlighter;

use VStelmakh\UrlHighlight\Highlighter\HtmlHighlighter;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Replacer\ReplacerFactory;

class HtmlHighlighterTest extends TestCase
{
    /**
     * @dataProvider highlightDataProvider
     *
     * @param string $input
     * @param array&string[] $attributes
     * @param string $contentBefore
     * @param string $contentAfter
     * @param string|null $expected
     */
    public function testHighlight(
        string $input,
        array $attributes,
        string $contentBefore,
        string $contentAfter,
        ?string $expected
    ): void {
        if ($expected === null) {
            $this->expectException(\InvalidArgumentException::class);
        }

        $htmlHighlighter = new HtmlHighlighter('http', $attributes, $contentBefore, $contentAfter);
        $replacer = ReplacerFactory::createReplacer();
        $actual = $htmlHighlighter->highlight($input, $replacer);

        self::assertSame($expected, $actual);
    }

    /**
     * @return mixed[]
     */
    public function highlightDataProvider(): array
    {
        return [
            [
                'Example text',
                [],
                '',
                '',
                'Example text'
            ],
            [
                'Example text before http://example.com and after.',
                [],
                '',
                '',
                'Example text before <a href="http://example.com">http://example.com</a> and after.'
            ],
            [
                'Example text before example.com and after.',
                [],
                '',
                '',
                'Example text before <a href="http://example.com">example.com</a> and after.'
            ],
            [
                'Example text before mailto:user@example.com and after.',
                [],
                '',
                '',
                'Example text before <a href="mailto:user@example.com">mailto:user@example.com</a> and after.'
            ],
            [
                'Example text before user@example.com and after.',
                [],
                '',
                '',
                'Example text before <a href="mailto:user@example.com">user@example.com</a> and after.'
            ],
            [
                'Example text before http://example.com?a="1"&b=2 and after.',
                [],
                '',
                '',
                'Example text before <a href="http://example.com?a=%221%22&b=2">http://example.com?a="1"&b=2</a> and after.'
            ],
            [
                'Example text before http://example.com and after.',
                ['rel' => 'nofollow', 'title' => '"quotes"'],
                '',
                '',
                'Example text before <a href="http://example.com" rel="nofollow" title="&quot;quotes&quot;">http://example.com</a> and after.'
            ],
            [
                'Example text before http://example.com and after.',
                ['"quotes"' => 'value'],
                '',
                '',
                null
            ],
            [
                'Example text before http://example.com and after.',
                [],
                'BEFORE ',
                ' AFTER',
                'Example text before BEFORE <a href="http://example.com">http://example.com</a> AFTER and after.'
            ],
            [
                'Example text before <a href="http://example.com">http://example.com</a> and after.',
                [],
                '',
                '',
                'Example text before <a href="http://example.com">http://example.com</a> and after.'
            ],
            [
                'Example text before <a href="mailto:user@example.com">contact user@example.com for help</a> and after.',
                [],
                '',
                '',
                'Example text before <a href="mailto:user@example.com">contact user@example.com for help</a> and after.'
            ],
            [
                'Example text before <p>http://example.com</p> and after.',
                [],
                '',
                '',
                'Example text before <p><a href="http://example.com">http://example.com</a></p> and after.'
            ],
            [
                '<div>Example text before <p>http://example.com</p> and <div><a href="http://example.com">http://example.com</a></div> after.</div>',
                ['rel' => 'nofollow'],
                'BEFORE ',
                ' AFTER',
                '<div>Example text before <p>BEFORE <a href="http://example.com" rel="nofollow">http://example.com</a> AFTER</p> and <div><a href="http://example.com">http://example.com</a></div> after.</div>'
            ],
        ];
    }
}
