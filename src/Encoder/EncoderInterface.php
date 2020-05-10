<?php

namespace VStelmakh\UrlHighlight\Encoder;

interface EncoderInterface
{
    /**
     * @param string $string
     * @return string
     */
    public function decode(string $string): string;

    /**
     * @param string $char
     * @param string $delimiter
     * @return string
     */
    public function getEncodedCharRegex(string $char, string $delimiter = '/'): string;

    /**
     * @return string[]|null
     */
    public function getSupportedChars(): ?array;
}
