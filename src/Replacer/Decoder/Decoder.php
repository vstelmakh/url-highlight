<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Replacer\Decoder;

/**
 * Decodes HTML entities while preserving a map from decoded byte offsets back to the original encoded positions.
 * Used so that URLs inside HTML-escaped input (e.g. via htmlspecialchars) are matched against their true characters
 * while the encoded form is left unchanged in the output.
 *
 * @internal
 */
final class Decoder
{
    public function decode(string $encoded): DecodedString
    {
        if (!str_contains($encoded, '&')) {
            return new DecodedString($encoded, [], []);
        }

        $decoded = '';
        $cursor = 0;
        $decodedOffsets = [];
        $encodedOffsets = [];

        // Lots of the same HTML entities may repeat throughout an input.
        // Decoding entity once and keeping the result in a map for further use makes the process noticeably faster.
        $decodedEntities = [];

        // Matches HTML entities: named, decimal numeric and hexadecimal numeric.
        $pattern = '/&(?:[a-z][a-z0-9]*|#\d+|#x[0-9a-f]+);/i';

        // Matching one entity at a time from a moving cursor, rather than collecting them all up front, keeps only
        // the current match in memory. This approach, significantly saves memory for entity-dense inputs.
        while (preg_match($pattern, $encoded, $match, PREG_OFFSET_CAPTURE, $cursor) === 1) {
            [$entity, $encodedOffset] = $match[0];

            $decoded .= substr($encoded, $cursor, $encodedOffset - $cursor);
            $decoded .= $decodedEntities[$entity] ??= html_entity_decode($entity, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $cursor = $encodedOffset + strlen($entity);

            // Record the position right after the entity, where both strings line up again.
            $decodedOffsets[] = strlen($decoded);
            $encodedOffsets[] = $cursor;
        }

        // On PCRE failure add the remaining input, which leaves the value correct and simply unmapped from there on.
        return new DecodedString($decoded . substr($encoded, $cursor), $decodedOffsets, $encodedOffsets);
    }
}
