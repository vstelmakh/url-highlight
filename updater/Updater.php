<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Updater;

final readonly class Updater
{
    public static function create(): self
    {
        return new self(new Parser(new LineReader()), new Renderer(), new FileWriter());
    }

    public function __construct(
        private Parser $parser,
        private Renderer $renderer,
        private FileWriter $fileWriter,
    ) {}

    public function update(string $targetPath): void
    {
        $domainList = $this->parser->parse();
        $content = $this->renderer->render($domainList);
        $this->fileWriter->write($targetPath, $content);
    }
}
