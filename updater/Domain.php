<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Updater;

final readonly class Domain implements \Stringable
{
    private const int IDNA_FLAGS = IDNA_USE_STD3_RULES
        | IDNA_CHECK_BIDI
        | IDNA_CHECK_CONTEXTJ
        | IDNA_NONTRANSITIONAL_TO_ASCII
        | IDNA_NONTRANSITIONAL_TO_UNICODE;

    private const int IDNA_VARIANT = INTL_IDNA_VARIANT_UTS46;

    public string $unicode;
    public string $punycode;

    public function __construct(string $value)
    {
        $this->validateNotEmpty($value);
        $this->unicode = $this->toUnicode($value);
        $this->punycode = $this->toPunycode($this->unicode);
        $this->validateSingleLabel($this->punycode);
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

    private function validateNotEmpty(string $value): void
    {
        if ($value === '') {
            throw new \DomainException('Domain value should not be empty.');
        }
    }

    /**
     * Checks the punycode form, because conversion maps dot variants like "。" to ".".
     */
    private function validateSingleLabel(string $punycode): void
    {
        if (str_contains($punycode, '.')) {
            throw new \DomainException(sprintf('Domain value "%s" should be a single label.', $punycode));
        }
    }

    private function toUnicode(string $value): string
    {
        $unicode = idn_to_utf8($value, self::IDNA_FLAGS, self::IDNA_VARIANT, $idnaInfo);

        if ($unicode === false) {
            throw new \DomainException(sprintf(
                'Domain value "%s" could not be converted to unicode. IDNA errors: %s.',
                $value,
                $this->resolveIdnaErrorNames($idnaInfo['errors']),
            ));
        }

        return $unicode;
    }

    private function toPunycode(string $value): string
    {
        $punycode = idn_to_ascii($value, self::IDNA_FLAGS, self::IDNA_VARIANT, $idnaInfo);

        if ($punycode === false) {
            throw new \DomainException(sprintf(
                'Domain value "%s" could not be converted to punycode. IDNA errors: %s.',
                $value,
                $this->resolveIdnaErrorNames($idnaInfo['errors']),
            ));
        }

        return $punycode;
    }

    /**
     * Error bitmask resolved to "IDNA_ERROR_*" constant names, or the raw bitmask if no constant matches.
     */
    private function resolveIdnaErrorNames(int $errors): string
    {
        $constants = get_defined_constants(true)['intl'] ?? [];
        $names = [];

        foreach ($constants as $name => $value) {
            $isIdnaError = str_starts_with($name, 'IDNA_ERROR_') && is_int($value);

            if ($isIdnaError && ($errors & $value) !== 0) {
                $names[] = $name;
            }
        }

        if ($names === []) {
            return (string) $errors;
        }

        return implode(', ', $names);
    }
}
