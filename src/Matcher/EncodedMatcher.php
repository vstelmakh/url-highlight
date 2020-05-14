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

    /**
     * @internal
     * @param Matcher $matcher
     * @param EncoderInterface $encoder
     */
    public function __construct(Matcher $matcher, EncoderInterface $encoder)
    {
        $this->matcher = $matcher;
        $this->encoder = $encoder;
    }

    /**
     * Match string by url regex
     *
     * @param string $string
     * @return Match|null
     */
    public function match(string $string): ?Match
    {
        $decodedString = $this->encoder->decode($string);
        $match = $this->matcher->match($decodedString);
        return $match ? $this->getEncodedMatch($string, 0, $match) : null;
    }

    /**
     * Get all valid url regex matches from encoded string
     *
     * @param string $string
     * @return array&Match[]
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
            $encodedMatches[] = $this->getEncodedMatch($encodedFullMatch, $encodedByteOffset, $match);
        }

        return $encodedMatches;
    }

    /**
     * Replace all valid url matches by callback from encoded string
     *
     * @param string $string
     * @param callable $callback
     * @return string
     */
    public function replaceCallback(string $string, callable $callback): string
    {
        $offset = 0;

        $matches = $this->matchAll($string);
        foreach ($matches as $match) {
            $replacement = $callback($match, $match->getFullMatch());
            $position = $match->getByteOffset() + $offset;
            $length = strlen($match->getFullMatch());
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
            ? $this->getUrlRegexBySupportedChars($match, $supportedChars)
            : $this->getUrlRegexByAllChars($match);
    }

    /**
     * @param Match $match
     * @param array&string[] $supportedChars
     * @return string
     */
    private function getUrlRegexBySupportedChars(Match $match, array $supportedChars): string
    {
        $replace = [];
        foreach ($supportedChars as $char) {
            $replace[] = $this->getRegexCharGroup($char);
        }

        $fullMatchRegexSafe = preg_quote($match->getUrl(), '/');
        return str_replace($supportedChars, $replace, $fullMatchRegexSafe);
    }

    /**
     * @param Match $match
     * @return string
     */
    private function getUrlRegexByAllChars(Match $match): string
    {
        $fullMatchRegex = '';

        $fullMatchChars = Str::getChars($match->getUrl());
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

    /**
     * @param string $fullMatch
     * @param int $byteOffset
     * @param Match $match
     * @return Match
     */
    private function getEncodedMatch(string $fullMatch, int $byteOffset, Match $match): Match
    {
        return new Match(
            $fullMatch,
            $byteOffset,
            $match->getUrl(),
            $match->getScheme(),
            $match->getLocal(),
            $match->getHost(),
            $match->getTld()
        );
    }
}
