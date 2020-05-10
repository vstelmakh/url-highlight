<?php

namespace VStelmakh\UrlHighlight\Tests;

use VStelmakh\UrlHighlight\Encoder\HtmlSpecialcharsEncoder;
use VStelmakh\UrlHighlight\Highlighter\HtmlHighlighter;
use VStelmakh\UrlHighlight\Replacer\EncodedReplacer;
use VStelmakh\UrlHighlight\UrlHighlight;
use PHPUnit\Framework\TestCase;

class UrlHighlightIntegrationTest extends TestCase
{
    public function testHighlightUrls(): void
    {
        $highlighter = new HtmlHighlighter('http');
        $encoder = new HtmlSpecialcharsEncoder();
        $replacer = new EncodedReplacer($highlighter, $encoder);
        $urlHighlight = new UrlHighlight([], $replacer);

        $input = 'Hello ★, follow the link: &lt;a id=&quot;example&quot; class=&quot;link&quot; href=&quot;http://example.com?a=1&amp;b=2#anchor&quot; title=&quot;Example&quot;&gt;example.com&lt;/a&gt;.';
        $expected = 'Hello ★, follow the link: &lt;a id=&quot;example&quot; class=&quot;link&quot; href=&quot;<a href="http://example.com?a=1&b=2#anchor">http://example.com?a=1&amp;b=2#anchor</a>&quot; title=&quot;Example&quot;&gt;<a href="http://example.com">example.com</a>&lt;/a&gt;.';

        $actual = $urlHighlight->highlightUrls($input);
        $this->assertSame($expected, $actual);
    }
}
