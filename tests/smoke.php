<?php

/**
 * Verifies the package works with only production dependencies installed. Not a PHPUnit test: PHPUnit itself is a dev
 * dependency and would not be available. Run directly: `php tests/smoke.php [path/to/vendor/autoload.php]`.
 */

declare(strict_types=1);

require $argv[1] ?? __DIR__ . '/../vendor/autoload.php';

use VStelmakh\UrlHighlight\UrlHighlight;

$urlHighlight = new UrlHighlight();
$text = 'Check the example.com website.';

$highlighted = $urlHighlight->highlight($text);
$expected = 'Check the <a href="http://example.com">example.com</a> website.';
if ($highlighted !== $expected) {
    fwrite(STDERR, "highlight() mismatch.\nExpected: {$expected}\nActual:   {$highlighted}\n");
    exit(1);
}

$urls = $urlHighlight->find($text);
if (count($urls) !== 1 || (string) $urls[0] !== 'example.com') {
    fwrite(STDERR, "find() failed to return the expected URL.\n");
    exit(1);
}

echo "OK\n";
