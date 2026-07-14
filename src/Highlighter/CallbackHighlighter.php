<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Highlighter;

use VStelmakh\UrlHighlight\Url;

/**
 * Delegates rendering to a user-provided callback. Use for one-off highlighting logic without defining a
 * dedicated {@see Highlighter} class.
 *
 * @api
 */
final readonly class CallbackHighlighter implements Highlighter
{
    /** @var \Closure(Url): string */
    private \Closure $callback;

    /**
     * @param callable(Url): string $callback Called once per detected URL. Returns the replacement string written
     *     directly into the output, so any HTML must be properly escaped.
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback(...);
    }

    #[\Override]
    public function render(Url $url): string
    {
        return ($this->callback)($url);
    }
}
