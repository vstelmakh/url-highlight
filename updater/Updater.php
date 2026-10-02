<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Updater;

final readonly class Updater
{
    public static function create(): self
    {
        $filesystem = new Filesystem();
        return new self(new Parser($filesystem), new Renderer(), $filesystem);
    }

    public function __construct(
        private Parser $parser,
        private Renderer $renderer,
        private Filesystem $filesystem,
    ) {}

    public function update(string $targetPath): void
    {
        $domainList = $this->parser->parse();
        $content = $this->renderer->render($domainList);
        $this->filesystem->write($targetPath, $content);
    }
}
