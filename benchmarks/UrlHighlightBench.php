<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Benchmarks;

use PhpBench\Attributes as Bench;
use VStelmakh\UrlHighlight\Format;
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
    private Format $inputFormat;

    /**
     * @param array{file: string, inputFormat: Format} $params
     */
    public function setUp(array $params): void
    {
        $this->urlHighlight = new UrlHighlight();
        $this->input = (string) file_get_contents(__DIR__ . '/' . $params['file']);
        $this->inputFormat = $params['inputFormat'];
    }

    #[Bench\ParamProviders('highlightParamProvider')]
    public function benchHighlight(): void
    {
        $this->urlHighlight->highlight(text: $this->input, format: $this->inputFormat);
    }

    /**
     * @return array<string, array{file: string, inputFormat: Format}>
     */
    public function highlightParamProvider(): array
    {
        return [
            'plain' => $this->dataset('input_plain.txt', Format::Plain),
            'html' => $this->dataset('input_html.txt', Format::Html),
            'html special chars' => $this->dataset('input_html_special_chars.txt', Format::HtmlEncoded),
            'html entities' => $this->dataset('input_html_entities.txt', Format::HtmlEncoded),
        ];
    }

    #[Bench\ParamProviders('findParamProvider')]
    public function benchFind(): void
    {
        $this->urlHighlight->find($this->input);
    }

    /**
     * @return array<string, array{file: string, inputFormat: Format}>
     */
    public function findParamProvider(): array
    {
        return [
            'plain' => $this->dataset('input_plain.txt', Format::Plain),
        ];
    }

    /**
     * @return array{file: string, inputFormat: Format}
     */
    private function dataset(string $file, Format $inputFormat): array
    {
        return ['file' => $file, 'inputFormat' => $inputFormat];
    }
}
