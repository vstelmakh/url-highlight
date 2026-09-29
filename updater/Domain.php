<?php

declare(strict_types=1);

// @php-cs-fixer-ignore array_indentation

namespace VStelmakh\UrlHighlight\Updater;

final readonly class Domain implements \Stringable
{
    public string $unicode;
    public string $punycode;

    public function __construct(string $value)
    {
        $this->validate($value);
        $this->unicode = $this->normalize($value);
        $this->punycode = $this->toPunycode($this->unicode);
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->unicode;
    }

    public function isIdn(): bool
    {
        return $this->punycode !== $this->unicode;
    }

    private function validate(string $value): void
    {
        $pattern = implode('', [
            '/^(?=[^\-])',            // not start with: "-"
            '(?:',                    // non-capturing group, consists of:
                '[^',                     // not (exclude):
                    '\p{Z}',                  // whitespace
                    '\p{Sm}',                 // mathematical
                    '\p{Sc}',                 // currency
                    '\p{Sk}',                 // modifier symbol
                    '\p{C}',                  // control character (invisible)
                    '\p{P}',                  // punctuation
                ']',
                '|',
                '[',                      // except (include):
                    '\-',                     // "-"
                    '\x{200C}',               // zero width non-joiner
                    '\x{200D}',               // zero width joiner
                    '\x{00B7}',               // middle dot
                    '\x{0375}',               // greek lower numeral sign
                    '\x{05F3}',               // hebrew punctuation geresh
                    '\x{05F4}',               // hebrew punctuation gershayim
                    '\x{30FB}',               // katakana middle dot
                    '\x{0660}-\x{0669}',      // arabic-indic digits
                    '\x{06F0}-\x{06F9}',      // extended arabic-indic digits
                ']',
            ')',                       // close group
            '{1,63}',                  // length: 1-63 chars
            '(?<=[^\-])$/u',           // not end with: "-"
        ]);

        $isValid = preg_match($pattern, $value);

        if ($isValid !== 1) {
            throw new \DomainException(sprintf('Domain value "%s" is invalid.', $value));
        }
    }

    private function normalize(string $value): string
    {
        return mb_strtolower($value);
    }

    private function toPunycode(string $value): string
    {
        $punycode = idn_to_ascii($value, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);

        if ($punycode === false) {
            throw new \DomainException(sprintf('Domain value "%s" could not be converted to punycode.', $value));
        }

        return $punycode;
    }
}
