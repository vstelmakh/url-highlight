<?php

declare(strict_types=1);

$finder = new PhpCsFixer\Finder()
    ->in(__DIR__)
    ->exclude('var')
;

return new PhpCsFixer\Config()
    ->setFinder($finder)
    ->setCacheFile(__DIR__ . '/var/phpcs')
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRules([
        '@PER-CS' => true,
        '@Symfony' => true,
        'blank_line_before_statement' => false,
        'concat_space' => ['spacing' => 'one'],
        'increment_style' => ['style' => 'post'],
        'operator_linebreak' => ['position' => 'beginning', 'only_booleans' => false],
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_annotation_without_dot' => false,
        'phpdoc_line_span' => ['const' => 'single', 'property' => 'single'],
        'single_line_comment_style' => true,
        'single_line_empty_body' => true,
        'single_line_throw' => false,
        'yoda_style' => false,
    ])
;
