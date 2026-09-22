<?php

/**
 * Verifies the package works with only production dependencies installed. Not a PHPUnit test: PHPUnit itself is a dev
 * dependency and would not be available. Run directly: `php tests/smoke_test.php [path/to/vendor/autoload.php]`.
 */

declare(strict_types=1);

$autoloadPath = $argv[1] ?? __DIR__ . '/../vendor/autoload.php';
require $autoloadPath;

use VStelmakh\UrlHighlight\Format;
use VStelmakh\UrlHighlight\Highlighter\CallbackHighlighter;
use VStelmakh\UrlHighlight\Url;
use VStelmakh\UrlHighlight\UrlHighlight;

fwrite(STDOUT, 'Autoload path: ' . realpath($autoloadPath) . "\n");

$urlHighlight = new UrlHighlight();
$text = 'Check the example.com website.';

// Default format (Html) and highlighter (SimpleHighlighter)
$highlighted = $urlHighlight->highlight($text);
$expected = 'Check the <a href="http://example.com">example.com</a> website.';
if ($highlighted !== $expected) {
    fwrite(STDERR, "highlight() mismatch.\nExpected: {$expected}\nActual:   {$highlighted}\n");
    exit(1);
}

// CallbackHighlighter
$highlighted = $urlHighlight->highlight($text, new CallbackHighlighter(static fn (Url $url) => "[{$url}]"));
$expected = 'Check the [example.com] website.';
if ($highlighted !== $expected) {
    fwrite(STDERR, "highlight() with CallbackHighlighter mismatch.\nExpected: {$expected}\nActual:   {$highlighted}\n");
    exit(1);
}

// Format::Plain
$highlighted = $urlHighlight->highlight($text, format: Format::Plain);
$expected = 'Check the <a href="http://example.com">example.com</a> website.';
if ($highlighted !== $expected) {
    fwrite(STDERR, "highlight() with Format::Plain mismatch.\nExpected: {$expected}\nActual:   {$highlighted}\n");
    exit(1);
}

// Format::HtmlEncoded
$encodedText = 'Visit &lt;example.com&gt; now.';
$highlighted = $urlHighlight->highlight($encodedText, format: Format::HtmlEncoded);
$expected = 'Visit &lt;<a href="http://example.com">example.com</a>&gt; now.';
if ($highlighted !== $expected) {
    fwrite(STDERR, "highlight() with Format::HtmlEncoded mismatch.\nExpected: {$expected}\nActual:   {$highlighted}\n");
    exit(1);
}

// Find URLs
$urls = $urlHighlight->find($text);
if (count($urls) !== 1 || (string) $urls[0] !== 'example.com') {
    fwrite(STDERR, "find() failed to return the expected URL.\n");
    exit(1);
}

echo "OK\n";
