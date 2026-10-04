<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use VStelmakh\UrlHighlight\Updater\Updater;

try {
    fwrite(STDOUT, "Updating top-level domains\n\n");

    $updater = Updater::create();
    $result = $updater->update();

    $lastUpdated = $result->lastUpdated->format('Y-m-d H:i:s T');

    fwrite(STDOUT, "Source:  \033[36m{$result->sourceUrl}\033[0m\n");
    fwrite(STDOUT, "Version: \033[32m{$result->version}\033[0m updated at \033[33m{$lastUpdated}\033[0m\n");
    fwrite(STDOUT, "Target:  \033[36m{$result->targetPath}\033[0m\n");
    fwrite(STDOUT, "Domains: {$result->previousCount} → {$result->count}\n\n");

    if (!$result->hasChanges()) {
        fwrite(STDOUT, "No changes\n");
    } else {
        fwrite(STDOUT, "Changes:\n");

        foreach ($result->removed as $domain) {
            fwrite(STDOUT, "\033[31m- {$domain}\033[0m\n");
        }

        foreach ($result->added as $domain) {
            fwrite(STDOUT, "\033[32m+ {$domain}\033[0m\n");
        }
    }

    fwrite(STDOUT, "\n\033[42m\033[30m Success \033[0m\n");
    exit(0);
} catch (Throwable $e) {
    fwrite(STDERR, "{$e->getMessage()}\n");
    fwrite(STDOUT, "\n\033[41m Error \033[0m\n");
    exit(1);
}
