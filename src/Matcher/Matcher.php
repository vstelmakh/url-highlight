<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Matcher;

/**
 * Finds URLs in a string via {@see UrlRegex}, then discards the ones with an invalid host and trims trailing
 * punctuation carried over from the surrounding text.
 *
 * @internal
 */
final readonly class Matcher
{
    private PunctuationFilter $punctuationFilter;
    private HostValidator $hostValidator;
    private UrlRegex $regex;

    public function __construct()
    {
        $this->punctuationFilter = new PunctuationFilter();
        $this->hostValidator = new HostValidator();
        $this->regex = new UrlRegex();
    }

    /**
     * @return array<UrlMatch>
     */
    public function match(string $text): array
    {
        $result = [];

        foreach ($this->regex->findAll($text) as $match) {
            if (!$this->hostValidator->isValid($match->url)) {
                continue;
            }

            $url = $this->punctuationFilter->filter($match->url);

            $result[] = $url === $match->url ? $match : new UrlMatch($match->start, $url);
        }

        return $result;
    }
}
