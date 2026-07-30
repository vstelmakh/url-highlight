<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Benchmarks;

use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use VStelmakh\UrlHighlight\UrlHighlight;

#[Revs(10)]
#[Iterations(5)]
class UrlHighlightBench
{
    public function benchHighlightPlain(): void
    {
        $urlHighlight = new UrlHighlight();
        $input = (string) file_get_contents(__DIR__ . '/input_plain.txt');
        $urlHighlight->highlight(text: $input);
    }

    public function benchHighlightHtml(): void
    {
        $urlHighlight = new UrlHighlight();
        $input = (string) file_get_contents(__DIR__ . '/input_html.txt');
        $urlHighlight->highlight(text: $input);
    }

    public function benchHighlightHtmlSpecialChars(): void
    {
        $urlHighlight = new UrlHighlight();
        $input = (string) file_get_contents(__DIR__ . '/input_html_special_chars.txt');
        $urlHighlight->highlight(text: $input, isHtmlEncoded: true);
    }

    public function benchHighlightHtmlEntities(): void
    {
        $urlHighlight = new UrlHighlight();
        $input = (string) file_get_contents(__DIR__ . '/input_html_entities.txt');
        $urlHighlight->highlight(text: $input, isHtmlEncoded: true);
    }
}
