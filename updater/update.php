<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use VStelmakh\UrlHighlight\Updater\Updater;

$relativePath = 'src/Matcher/Domains/tld_map.php';
$absolutePath = dirname(__DIR__) . '/' . $relativePath;

try {
    fwrite(STDOUT, "Updating top-level domain map\n");
    fwrite(STDOUT, "Path: \033[36m{$relativePath}\033[0m\n\n");

    $updater = Updater::create();
    $updater->update($absolutePath);

    fwrite(STDOUT, "\033[42m\033[30m Success \033[0m\n");
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, "{$e->getMessage()}\n");
    fwrite(STDOUT, "\n\033[41m Error \033[0m\n");
    exit(1);
}
