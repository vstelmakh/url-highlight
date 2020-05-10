<?php

namespace VStelmakh\UrlHighlight\Matcher;

use VStelmakh\UrlHighlight\Encoder\EncoderInterface;
use VStelmakh\UrlHighlight\Util\Str;

/**
 * @internal
 */
class EncodedMatcher implements MatcherInterface
{
    /**
     * @var Matcher
     */
    private $matcher;

    /**
     * @var EncoderInterface
     */
    private $encoder;

    public function __construct(Matcher $matcher, EncoderInterface $encoder)
    {
        $this->matcher = $matcher;
        $this->encoder = $encoder;
    }

    /**
     * Match string by url regex
     *
     * @param string $string
     * @return EncodedMatch|null
     */
    public function match(string $string): ?MatchInterface
    {
        $decodedString = $this->encoder->decode($string);
        $match = $this->matcher->match($decodedString);
        return $match ? new EncodedMatch($decodedString, 0, $match) : null;
    }

    /**
     * @param string $string
     * @return array&EncodedMatch[]
     */
    public function matchAll(string $string): array
    {
        $encodedMatches = [];
        $nextMatchOffset = 0;

        $decodedString = $this->encoder->decode($string);
        $matches = $this->matcher->matchAll($decodedString);

        foreach ($matches as $match) {
            $regex = sprintf('/%s/iu', $this->getEncodedMatchRegex($match));
            preg_match($regex, $string, $encodedRawMatch, PREG_OFFSET_CAPTURE, $nextMatchOffset);

            $encodedFullMatch = $encodedRawMatch[0][0];
            $encodedByteOffset = $encodedRawMatch[0][1];
            $nextMatchOffset = $encodedByteOffset + strlen($encodedFullMatch);
            $encodedMatches[] = new EncodedMatch($encodedFullMatch, $encodedByteOffset, $match);
        }

        return $encodedMatches;
    }

    /**
     * @param string $string
     * @param callable $callback
     * @return string
     */
    public function replaceCallback(string $string, callable $callback): string
    {
        $offset = 0;

        $encodedMatches = $this->matchAll($string);
        foreach ($encodedMatches as $encodedMatch) {
            $replacement = $callback($encodedMatch, $encodedMatch->getEncodedFullMatch());
            $position = $encodedMatch->getEncodedByteOffset() + $offset;
            $length = strlen($encodedMatch->getEncodedFullMatch());
            $string = substr_replace($string, $replacement, $position, $length);
            $offset += strlen($replacement) - $length;
        }

        return $string;
    }

    /**
     * Replace html special chars with char variations regex
     *
     * @param Match $match
     * @return string
     */
    private function getEncodedMatchRegex(Match $match): string
    {
        $supportedChars = $this->encoder->getSupportedChars();
        return !empty($supportedChars)
            ? $this->getFullMatchRegexBySupportedChars($match, $supportedChars)
            : $this->getFullMatchRegexByAllChars($match);
    }

    /**
     * @param Match $match
     * @param array&string[] $supportedChars
     * @return string
     */
    private function getFullMatchRegexBySupportedChars(Match $match, array $supportedChars): string
    {
        $replace = [];
        foreach ($supportedChars as $char) {
            $replace[] = $this->getRegexCharGroup($char);
        }

        $fullMatchRegexSafe = preg_quote($match->getFullMatch(), '/');
        return str_replace($supportedChars, $replace, $fullMatchRegexSafe);
    }

    /**
     * @param Match $match
     * @return string
     */
    private function getFullMatchRegexByAllChars(Match $match): string
    {
        $fullMatchRegex = '';

        $fullMatchChars = Str::getChars($match->getFullMatch());
        foreach ($fullMatchChars as $char) {
            $fullMatchRegex .= $this->getRegexCharGroup($char);
        }

        return $fullMatchRegex;
    }

    /**
     * @param string $char
     * @return string
     */
    private function getRegexCharGroup(string $char): string
    {
        return '(?:' . $this->encoder->getEncodedCharRegex($char, '/') . ')';
    }
}
