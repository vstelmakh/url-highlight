<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter\Decoder;

/**
 * Decodes HTML entities while preserving a sparse map from decoded byte offsets back to the original
 * encoded positions. Used so that URLs inside HTML-escaped input (e.g. via htmlspecialchars) are
 * matched against their true characters while the encoded form is kept verbatim in the output.
 *
 * @internal
 */
final class Decoder
{
    private const string ENTITY_REGEX = '/&(?:[a-z][a-z0-9]*|#\d+|#x[0-9a-f]+);/i';

    public function decode(string $encoded): DecodedString
    {
        if (!str_contains($encoded, '&')) {
            return new DecodedString($encoded, []);
        }

        preg_match_all(self::ENTITY_REGEX, $encoded, $entities, PREG_OFFSET_CAPTURE);
        if ($entities[0] === []) {
            return new DecodedString($encoded, []);
        }

        $decoded = '';
        $shifts = [];
        $cursor = 0;
        $shift = 0;

        /** @var array<array{0: string, 1: int}> $matches */
        $matches = $entities[0];
        foreach ($matches as [$entity, $offset]) {
            $decoded .= substr($encoded, $cursor, $offset - $cursor);
            $decodedEntity = html_entity_decode($entity, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $decoded .= $decodedEntity;
            $shift += strlen($entity) - strlen($decodedEntity);
            $shifts[strlen($decoded)] = $shift;
            $cursor = $offset + strlen($entity);
        }
        $decoded .= substr($encoded, $cursor);

        return new DecodedString($decoded, $shifts);
    }
}
