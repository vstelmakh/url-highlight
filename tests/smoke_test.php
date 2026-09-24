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

fwrite(STDOUT, 'Smoke Test' . "\n");
fwrite(STDOUT, 'Autoload path: ' . realpath($autoloadPath) . "\n\n");

$urlHighlight = new UrlHighlight();
$text = 'Check the example.com website.';

// Default format (Html) and highlighter (SimpleHighlighter)
$highlighted = $urlHighlight->highlight($text);
$expected = 'Check the <a href="http://example.com">example.com</a> website.';
if ($highlighted !== $expected) {
    fail('highlight() default mismatch.', $expected, $highlighted);
}

// CallbackHighlighter
$highlighted = $urlHighlight->highlight($text, new CallbackHighlighter(static fn (Url $url) => "[{$url}]"));
$expected = 'Check the [example.com] website.';
if ($highlighted !== $expected) {
    fail('highlight() with CallbackHighlighter mismatch.', $expected, $highlighted);
}

// Format::Plain
$highlighted = $urlHighlight->highlight($text, format: Format::Plain);
$expected = 'Check the <a href="http://example.com">example.com</a> website.';
if ($highlighted !== $expected) {
    fail('highlight() with Format::Plain mismatch.', $expected, $highlighted);
}

// Format::HtmlEncoded
$encodedText = 'Visit &lt;example.com&gt; now.';
$highlighted = $urlHighlight->highlight($encodedText, format: Format::HtmlEncoded);
$expected = 'Visit &lt;<a href="http://example.com">example.com</a>&gt; now.';
if ($highlighted !== $expected) {
    fail('highlight() with Format::HtmlEncoded mismatch.', $expected, $highlighted);
}

// Find URLs
$urls = $urlHighlight->find($text);
if (count($urls) !== 1 || (string) $urls[0] !== 'example.com') {
    fail('find() failed to return the expected URL.');
}

success();

// - - - - - - - - - -

function success(): never
{
    fwrite(STDOUT, "\033[42m\033[30m OK \033[0m\n");
    exit(0);
}

function fail(string $message, ?string $expected = null, ?string $actual = null): never
{
    fwrite(STDERR, "{$message}\n");

    if ($expected !== null) {
        fwrite(STDERR, "Expected: {$expected}\n");
    }

    if ($actual !== null) {
        fwrite(STDERR, "Actual:   {$actual}\n");
    }

    fwrite(STDOUT, "\n\033[41m Fail \033[0m\n");
    exit(1);
}
