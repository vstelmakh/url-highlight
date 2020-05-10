<?php

namespace VStelmakh\UrlHighlight\Encoder;

class HtmlSpecialcharsEncoder implements EncoderInterface
{
    private const HTML_SPECIAL_CHARS = ['&', '"', '\'', '<', '>'];

    /**
     * @var HtmlEntitiesEncoder
     */
    private $htmlEntitiesEncoder;

    public function __construct()
    {
        $this->htmlEntitiesEncoder = new HtmlEntitiesEncoder();
    }

    /**
     * @param string $string
     * @return string
     */
    public function decode(string $string): string
    {
        return $this->htmlEntitiesEncoder->decode($string);
    }

    /**
     * If html special char, return regex to match: char or html entity or numeric character reference
     *     else return provided char regex safe
     *
     * @param string $char
     * @param string $delimiter
     * @return string
     */
    public function getEncodedCharRegex(string $char, string $delimiter = '/'): string
    {
        return \in_array($char, self::HTML_SPECIAL_CHARS, true)
            ? $this->htmlEntitiesEncoder->getEncodedCharRegex($char)
            : preg_quote($char, $delimiter);
    }

    /**
     * @return string[]|null
     */
    public function getSupportedChars(): ?array
    {
        return self::HTML_SPECIAL_CHARS;
    }
}
