<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter;

use VStelmakh\UrlHighlight\Url;

/**
 * Immutable builder of an HTML `<a>` element for a detected URL.
 *
 * @api
 */
final readonly class Anchor implements \Stringable
{
    /**
     * Creates an anchor that links to the URL and shows the URL as it appears in the text.
     *
     * @param string $fallbackScheme Scheme for the href when the URL has none, like `example.com`.
     */
    public static function fromUrl(Url $url, string $fallbackScheme = 'http'): self
    {
        $href = $url->toHref($fallbackScheme);
        $innerHtml = self::escape($url->full);
        return new self($innerHtml, ['href' => $href]);
    }

    /**
     * Uses the given text as the link content.
     *
     * The text is escaped, so HTML tags in it are shown as text.
     */
    public function withText(string $value): self
    {
        $innerHtml = self::escape($value);
        return new self($innerHtml, $this->attributes);
    }

    /**
     * Uses the given HTML as the link content.
     *
     * The HTML is not escaped. Escape every untrusted part yourself, including the URL text:
     * `htmlspecialchars($url->full)`.
     */
    public function withHtml(string $value): self
    {
        return new self($value, $this->attributes);
    }

    /**
     * Adds the attribute, or replaces its value if it exists. Names are case-insensitive and rendered in lowercase.
     *
     * A null value renders the name only. The value is escaped but not validated, so a `javascript:` URL in `href` or
     * an `onclick` handler still runs script. Do not pass untrusted input.
     *
     * @throws \InvalidArgumentException If the name is not a valid attribute name.
     */
    public function withAttribute(string $name, ?string $value = null): self
    {
        if (preg_match('/^[^\p{Cc}\s"\'<>\/=]+$/uD', $name) !== 1) {
            throw new \InvalidArgumentException(sprintf('Invalid attribute name "%s".', $name));
        }

        $attributes = $this->attributes;
        // Not `mb_strtolower`, ASCII only, as the HTML parser lowercases only A-Z in attribute names.
        $attributes[strtolower($name)] = $value;
        return new self($this->innerHtml, $attributes);
    }

    /**
     * Renders the anchor as an HTML `<a>` element.
     *
     * The output is ready-to-use HTML. Insert it as is, without escaping it again.
     */
    public function render(): string
    {
        $attributes = '';
        foreach ($this->attributes as $name => $value) {
            $attributes .= $value === null ? " {$name}" : sprintf(' %s="%s"', $name, self::escape($value));
        }

        return sprintf('<a%s>%s</a>', $attributes, $this->innerHtml);
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->render();
    }

    /**
     * @param string $innerHtml Link content as HTML. It is rendered as is, so escape any text before passing it.
     * @param array<string, ?string> $attributes
     */
    private function __construct(
        private string $innerHtml,
        private array $attributes,
    ) {}

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);
    }
}
