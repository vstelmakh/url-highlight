<?php

namespace VStelmakh\UrlHighlight\Benchmarks;

use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
use VStelmakh\UrlHighlight\Encoder\HtmlEntitiesEncoder;
use VStelmakh\UrlHighlight\Encoder\HtmlSpecialcharsEncoder;
use VStelmakh\UrlHighlight\UrlHighlight;

class UrlHighlightBench
{
    /**
     * @Revs(10)
     * @Iterations(5)
     */
    public function benchHighlightPlain(): void
    {
        $urlHighlight = new UrlHighlight();

        $input = file_get_contents(__DIR__ . '/input_plain.txt');
        $urlHighlight->highlightUrls($input);
    }

    /**
     * @Revs(10)
     * @Iterations(5)
     */
    public function benchHighlightHtml(): void
    {
        $urlHighlight = new UrlHighlight();

        $input = file_get_contents(__DIR__ . '/input_html.txt');
        $urlHighlight->highlightUrls($input);
    }

    /**
     * @Revs(10)
     * @Iterations(5)
     */
    public function benchHighlightHtmlSpecialChars(): void
    {
        $urlHighlight = new UrlHighlight(null, null, new HtmlSpecialcharsEncoder());

        $input = file_get_contents(__DIR__ . '/input_html_special_chars.txt');
        $urlHighlight->highlightUrls($input);
    }

    /**
     * @Revs(10)
     * @Iterations(5)
     */
    public function benchHighlightHtmlEntities(): void
    {
        $urlHighlight = new UrlHighlight(null, null, new HtmlEntitiesEncoder());

        $input = file_get_contents(__DIR__ . '/input_html_entities.txt');
        $urlHighlight->highlightUrls($input);
    }
}
