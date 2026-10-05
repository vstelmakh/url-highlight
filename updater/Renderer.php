<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Updater;

final readonly class Renderer
{
    public function render(DomainList $list, string $sourceUrl): string
    {
        $map = '';
        foreach ($list->domains as $domain) {
            $value = var_export($domain->unicode, true);
            $map .= "    $value => true,\n";
        }
        $map = trim($map);

        return <<<PHP
            <?php

            /**
             * List of valid top-level domains provided by IANA.
             *
             * @see {$sourceUrl}
             *
             * @internal
             */

            declare(strict_types=1);

            return [
                {$map}
            ];

            PHP;
    }
}
