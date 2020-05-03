<?php

namespace VStelmakh\UrlHighlight\Highlighter;

use VStelmakh\UrlHighlight\Match;
use VStelmakh\UrlHighlight\Matcher;
use VStelmakh\UrlHighlight\Util\NormalizedCollection;
use VStelmakh\UrlHighlight\Util\NormalizedMap;

/**
 * @internal
 */
class HtmlSpecialCharsHighlighter extends AbstractHighlighter
{
    private const BYTES_PER_CHAR = 4;

    /**
     * @var Matcher
     */
    private $matcher;

    /**
     * @var string
     */
    private $defaultScheme;

    public function __construct(Matcher $matcher, string $defaultScheme)
    {
        $this->matcher = $matcher;
        $this->defaultScheme = $defaultScheme;
    }

    /**
     * Parse html entity encoded string and replace urls with html links
     *
     * @param string $string
     * @return string
     */
    public function highlightUrls(string $string): string
    {
        $decodedString = html_entity_decode($string, ENT_QUOTES + ENT_HTML5);
        $replacementsMap = new NormalizedMap();

        $matches = $this->matcher->matchAll($decodedString);
        foreach ($matches as $match) {
            $charBefore = $this->getCharBeforeMatch($decodedString, $match);
            $charAfter = $this->getCharAfterMatch($decodedString, $match);

            $regex = sprintf(
                '/(%s)(%s)(%s)/iu',
                $this->getCharVariationsRegex($charBefore),
                $this->getFullMatchRegex($match),
                $this->getCharVariationsRegex($charAfter)
            );

            $replacement = $this->getHighlightBuilder($match, $this->defaultScheme)
                ->setPrefix('$1')
                ->setText('$2')
                ->setSuffix('$3')
                ->getHighlight();

            $replacementsMap->set($regex, $replacement);
        }

        $regexes = $replacementsMap->getKeys();
        $replacements = $replacementsMap->getValues();
        $result = preg_replace($regexes, $replacements, $string) ?? $string;
        $result = $this->filterHighlight($result);
        return $result;
    }

    /**
     * @param string $string
     * @param Match $match
     * @return string
     */
    private function getCharBeforeMatch(string $string, Match $match): string
    {
        $offsetBytes = $match->getByteOffset();
        if ($offsetBytes > 0) {
            $prefixBytes = substr($string, $offsetBytes - self::BYTES_PER_CHAR, self::BYTES_PER_CHAR);
            return mb_substr($prefixBytes, -1, 1);
        }
        return '';
    }

    /**
     * @param string $string
     * @param Match $match
     * @return string
     */
    private function getCharAfterMatch(string $string, Match $match): string
    {
        $offsetBytes = $match->getByteOffset();
        $lengthBytes = strlen($match->getFullMatch());
        if (($offsetBytes + $lengthBytes) < strlen($string)) {
            $suffixBytes = substr($string, $offsetBytes + $lengthBytes, self::BYTES_PER_CHAR);
            return mb_substr($suffixBytes, 0, 1);
        }
        return '';
    }

    /**
     * Replace possible html encoded chars with char variations regex
     *
     * @param Match $match
     * @return string
     */
    private function getFullMatchRegex(Match $match): string
    {
        $htmlSpecialChars = ['&', '"', '\'', '<', '>'];
        $replace = [];
        foreach ($htmlSpecialChars as $char) {
            $replace[] = '(?:' . $this->getCharVariationsRegex($char) . ')';
        }
        $fullMatchRegexSafe = preg_quote($match->getFullMatch(), '/');
        return str_replace($htmlSpecialChars, $replace, $fullMatchRegexSafe);
    }

    /**
     * TODO: add char code support
     *
     * @param string $char
     * @return string
     */
    private function getCharVariationsRegex(string $char): string
    {
        $variations = new NormalizedCollection([preg_quote($char, '/')]);
        $encodedChar = htmlspecialchars($char, ENT_QUOTES + ENT_HTML5);
        $variations->add(preg_quote($encodedChar, '/'));
        return implode('|', $variations->toArray());
    }

    /**
     * @param string $string
     * @return string
     */
    private function filterHighlight(string $string): string
    {
        do {
            $stringBeforeFilter = $string;
            $string = $this->filterHighlightInTagAttributes($string);
            $string = $this->filterHighlightInLinks($string);
        } while ($stringBeforeFilter !== $string);

        return $string;
    }
}
