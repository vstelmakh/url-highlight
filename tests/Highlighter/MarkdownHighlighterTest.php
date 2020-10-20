<?php

namespace VStelmakh\UrlHighlight\Tests\Highlighter;

use VStelmakh\UrlHighlight\Highlighter\MarkdownHighlighter;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Matcher\Matcher;
use VStelmakh\UrlHighlight\Replacer\Replacer;
use VStelmakh\UrlHighlight\Validator\Validator;

class MarkdownHighlighterTest extends TestCase
{
    /**
     * @dataProvider highlightDataProvider
     *
     * @param string $input
     * @param string $expected
     */
    public function testHighlight(
        string $input,
        string $expected
    ): void {
        $markdownHighlighter = new MarkdownHighlighter('http');
        $actual = $markdownHighlighter->highlight($input, $this->createReplacer());

        self::assertSame($expected, $actual);
    }

    /**
     * @return array&array[]
     */
    public function highlightDataProvider(): array
    {
        return [
            [
                'Example text',
                'Example text'
            ],
            [
                'Example text before http://example.com and after.',
                'Example text before [http://example.com](http://example.com) and after.'
            ],
            [
                'Example text before example.com and after.',
                'Example text before [example.com](http://example.com) and after.'
            ],
            [
                'Example text before mailto:user@example.com and after.',
                'Example text before [mailto:user@example.com](mailto:user@example.com) and after.'
            ],
            [
                'Example text before user@example.com and after.',
                'Example text before [user@example.com](mailto:user@example.com) and after.'
            ],
            [
                'Example text before http://example.com/brackets[is]here and after.',
                'Example text before [http://example.com/brackets\[is\]here](http://example.com/brackets[is]here) and after.'
            ],
            [
                'Example text before http://example.com/brackets(is)here and after.',
                'Example text before [http://example.com/brackets(is)here](http://example.com/brackets%28is%29here) and after.'
            ],
            [
                'Example text before <a href="http://example.com">http://example.com</a> and after.',
                'Example text before <a href="http://example.com">http://example.com</a> and after.'
            ],
            [
                'Example text before [http://example.com](http://example.com) and after.',
                'Example text before [http://example.com](http://example.com) and after.'
            ],
            [
                'Example text before [http://example.com] and after.',
                'Example text before [http://example.com] and after.'
            ],
            [
                'Example text before [example.com]: http://example.com and after.',
                'Example text before [example.com]: http://example.com and after.'
            ],
            [
                'Example text before <a href="mailto:user@example.com">contact user@example.com for help</a> and after.',
                'Example text before <a href="mailto:user@example.com">contact user@example.com for help</a> and after.'
            ],
            [
                'Example text before <p>http://example.com</p> and after.',
                'Example text before <p>[http://example.com](http://example.com)</p> and after.'
            ],
        ];
    }

    /**
     * @return Replacer
     */
    private function createReplacer(): Replacer
    {
        $validator = new Validator();
        $matcher = new Matcher($validator);
        return new Replacer($matcher);
    }
}
