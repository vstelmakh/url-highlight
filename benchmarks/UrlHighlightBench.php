<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Benchmarks;

use PhpBench\Attributes as Bench;
use VStelmakh\UrlHighlight\UrlHighlight;

/**
 * Important to warmup. PhpBench runs every iteration in a fresh process, so without it the first timed revolution also
 * pays the one-time costs (class loading, TLD map load, regex compile). Which is distorting the ratios this benchmark
 * exists to compare.
 */
#[Bench\Revs(10)]
#[Bench\Iterations(5)]
#[Bench\Warmup(1)]
#[Bench\BeforeMethods('setUp')]
final class UrlHighlightBench
{
    private UrlHighlight $urlHighlight;
    private string $input;
    private bool $isHtmlEncoded;

    /**
     * @param array{file: string, isHtmlEncoded: bool} $params
     */
    public function setUp(array $params): void
    {
        $this->urlHighlight = new UrlHighlight();
        $this->input = (string) file_get_contents(__DIR__ . '/' . $params['file']);
        $this->isHtmlEncoded = $params['isHtmlEncoded'];
    }

    #[Bench\ParamProviders('highlightParamProvider')]
    public function benchHighlight(): void
    {
        $this->urlHighlight->highlight(text: $this->input, isHtmlEncoded: $this->isHtmlEncoded);
    }

    /**
     * @return array<string, array{file: string, isHtmlEncoded: bool}>
     */
    public function highlightParamProvider(): array
    {
        return [
            'plain' => $this->dataset('input_plain.txt', false),
            'html' => $this->dataset('input_html.txt', false),
            'html special chars' => $this->dataset('input_html_special_chars.txt', true),
            'html entities' => $this->dataset('input_html_entities.txt', true),
        ];
    }

    #[Bench\ParamProviders('findParamProvider')]
    public function benchFind(): void
    {
        $this->urlHighlight->find($this->input);
    }

    /**
     * @return array<string, array{file: string, isHtmlEncoded: bool}>
     */
    public function findParamProvider(): array
    {
        return [
            'plain' => $this->dataset('input_plain.txt', false),
        ];
    }

    /**
     * @return array{file: string, isHtmlEncoded: bool}
     */
    private function dataset(string $file, bool $isHtmlEncoded): array
    {
        return ['file' => $file, 'isHtmlEncoded' => $isHtmlEncoded];
    }
}
