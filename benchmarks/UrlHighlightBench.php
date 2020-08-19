<?php

namespace VStelmakh\UrlHighlight\Benchmarks;

use PhpBench\Benchmark\Metadata\Annotations\Iterations;
use PhpBench\Benchmark\Metadata\Annotations\Revs;
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
}
