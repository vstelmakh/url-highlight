<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use VStelmakh\UrlHighlight\Updater\Updater;

try {
    echo "Updating top-level domains\n\n";

    $updater = Updater::create();
    $result = $updater->update();

    $lastUpdated = $result->lastUpdated->format('Y-m-d H:i:s T');

    echo "Source:  \033[36m{$result->sourceUrl}\033[0m\n";
    echo "Version: \033[32m{$result->version}\033[0m updated at \033[33m{$lastUpdated}\033[0m\n";
    echo "Target:  \033[36m{$result->targetPath}\033[0m\n";
    echo "Domains: {$result->previousCount} → {$result->count}\n\n";

    if (!$result->hasChanges()) {
        echo "No changes\n";
    } else {
        echo "Changes:\n";

        foreach ($result->removed as $domain) {
            echo "\033[31m- {$domain}\033[0m\n";
        }

        foreach ($result->added as $domain) {
            echo "\033[32m+ {$domain}\033[0m\n";
        }
    }

    echo "\n\033[42m\033[30m Success \033[0m\n";
    exit(0);
} catch (Throwable $e) {
    echo "{$e->getMessage()}\n";
    echo "\n\033[41m Error \033[0m\n";
    exit(1);
}
