<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Updater;

final readonly class Updater
{
    private const string IANA_TLD_LIST_URL = 'https://data.iana.org/TLD/tlds-alpha-by-domain.txt';

    public static function create(): self
    {
        return new self(new Parser(), new Renderer(), new Filesystem());
    }

    public function __construct(
        private Parser $parser,
        private Renderer $renderer,
        private Filesystem $filesystem,
    ) {}

    public function update(string $targetPath): void
    {
        $lines = $this->filesystem->readLines(self::IANA_TLD_LIST_URL);
        $domainList = $this->parser->parse($lines);
        $content = $this->renderer->render($domainList, self::IANA_TLD_LIST_URL);
        $this->filesystem->write($targetPath, $content);
    }
}
