<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Updater;

final readonly class Updater
{
    public static function create(): self
    {
        return new self(
            sourceUrl: 'https://data.iana.org/TLD/tlds-alpha-by-domain.txt',
            targetPath: dirname(__DIR__) . '/src/Matcher/Domains/tld_map.php',
            parser: new Parser(),
            renderer: new Renderer(),
            filesystem: new Filesystem(),
        );
    }

    public function __construct(
        private string $sourceUrl,
        private string $targetPath,
        private Parser $parser,
        private Renderer $renderer,
        private Filesystem $filesystem,
    ) {}

    public function update(): Result
    {
        $previous = $this->loadExistingDomains($this->targetPath);

        $lines = $this->filesystem->readLines($this->sourceUrl);
        $domainList = $this->parser->parse($lines);
        $content = $this->renderer->render($domainList, $this->sourceUrl);
        $this->filesystem->write($this->targetPath, $content);

        return $this->createResult($domainList, $previous);
    }

    /**
     * @return list<string>
     */
    private function loadExistingDomains(string $path): array
    {
        if (!is_file($path)) {
            return [];
        }

        $map = require $path;

        if (!is_array($map)) {
            throw new \RuntimeException(sprintf('File "%s" should return an array.', $path));
        }

        $domains = array_keys($map);

        return array_map(strval(...), $domains);
    }

    /**
     * @param list<string> $previous
     */
    private function createResult(DomainList $domainList, array $previous): Result
    {
        $current = array_map(static fn (Domain $domain) => $domain->unicode, $domainList->domains);
        $added = array_values(array_diff($current, $previous));
        $removed = array_values(array_diff($previous, $current));

        return new Result(
            sourceUrl: $this->sourceUrl,
            targetPath: $this->targetPath,
            version: $domainList->version,
            lastUpdated: $domainList->lastUpdated,
            count: $domainList->count(),
            previousCount: count($previous),
            added: $added,
            removed: $removed,
        );
    }
}
