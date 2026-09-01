<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests\Highlighter;

use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Highlighter\CallbackHighlighter;
use VStelmakh\UrlHighlight\Url;

class CallbackHighlighterTest extends TestCase
{
    public function testRenderReturnsCallbackResult(): void
    {
        $url = self::url('http://example.com');
        $highlighter = new CallbackHighlighter(static fn (Url $url): string => "[{$url->host}]");

        $actual = $highlighter->render($url);

        self::assertSame('[example.com]', $actual);
    }

    public function testRenderPassesUrlToCallback(): void
    {
        $url = self::url('http://example.com');
        $received = null;
        $highlighter = new CallbackHighlighter(static function (Url $url) use (&$received): string {
            $received = $url;
            return '';
        });

        $highlighter->render($url);

        self::assertSame($url, $received);
    }

    public function testRenderDoesNotEscapeCallbackResult(): void
    {
        $url = self::url('http://example.com');
        $highlighter = new CallbackHighlighter(static fn (Url $url): string => "<a href=\"{$url->full}\">link</a>");

        $actual = $highlighter->render($url);

        self::assertSame('<a href="http://example.com">link</a>', $actual);
    }

    public function testRenderAcceptsInvokableObjectAsCallback(): void
    {
        $url = self::url('http://example.com');
        $callback = new class {
            public function __invoke(Url $url): string
            {
                return $url->full;
            }
        };
        $highlighter = new CallbackHighlighter($callback);

        $actual = $highlighter->render($url);

        self::assertSame('http://example.com', $actual);
    }

    private static function url(string $full): Url
    {
        return new Url($full, 'http', null, 'example.com', null, null, null, null);
    }
}
