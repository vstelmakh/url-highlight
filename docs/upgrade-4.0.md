# Upgrade 3.x to 4.0

Version 4 has a new public interface. This page maps the 3.x API to the 4.0 equivalent.

> [!NOTE]
> Version 4 requires PHP 8.4 or higher.

## Methods

The constructor takes no arguments anymore. The highlighter and the input format are arguments of `highlight()`.

| 3.x                                                    | 4.0                                                       |
|--------------------------------------------------------|-----------------------------------------------------------|
| `new UrlHighlight($validator, $highlighter, $encoder)` | `new UrlHighlight()`                                      |
| `highlightUrls($string)`                               | `highlight($text, $highlighter, $format)`                 |
| `getUrls($string)`, returns `string[]`                 | `find($text)`, returns [Url](../src/Url.php) objects      |
| `isUrl($string)`                                       | removed                                                   |

```diff
-$urlHighlight = new UrlHighlight($validator, $highlighter, $encoder);
-echo $urlHighlight->highlightUrls($text);
+$urlHighlight = new UrlHighlight();
+echo $urlHighlight->highlight($text, $highlighter, $format);
```

`isUrl()` checked that the whole string is a URL. Use `find()` for the same result:

```php
<?php
$urls = $urlHighlight->find($text);
$isUrl = count($urls) === 1 && $urls[0]->full === $text;
```

## Input Format

The encoder is replaced by the [Format](../src/Format.php) enum.

| 3.x                             | 4.0                                                                  |
|---------------------------------|----------------------------------------------------------------------|
| `new HtmlSpecialcharsEncoder()` | `Format::HtmlEncoded`                                                |
| `new HtmlEntitiesEncoder()`     | `Format::HtmlEncoded`                                                |
| no encoder                      | `Format::Html` (default), or `Format::Plain` for text without markup |

```php
<?php
use VStelmakh\UrlHighlight\Format;

echo $urlHighlight->highlight($text, format: Format::HtmlEncoded);
```

## Highlighter

`HtmlHighlighter` and `MarkdownHighlighter` are removed, together with the constructor options for the scheme, tag
attributes and surrounding content. Build the replacement string yourself with
[CallbackHighlighter](../src/Highlighter/CallbackHighlighter.php).

The example below adds a `nofollow` attribute and uses the host as the link text, which in 3.x meant constructor
options plus an overridden `getText()`:

```diff
-$highlighter = new HtmlHighlighter('https', ['rel' => 'nofollow']);
+$highlighter = new CallbackHighlighter(fn (Url $url) => sprintf(
+    '<a href="%s" rel="nofollow">%s</a>',
+    htmlspecialchars($url->toHref('https')),
+    htmlspecialchars($url->host),
+));
```

```php
<?php
use VStelmakh\UrlHighlight\Highlighter\CallbackHighlighter;
use VStelmakh\UrlHighlight\Url;

echo $urlHighlight->highlight('Visit example.com/path today.', $highlighter);

// Output:
// Visit <a href="https://example.com/path" rel="nofollow">example.com</a> today.
```

A custom `HighlighterInterface` implementation becomes a [Highlighter](../src/Highlighter/Highlighter.php)
implementation. Instead of driving the replacer, `render()` receives one [Url](../src/Url.php) and returns its
replacement.

## Validator

The validator is gone and matching is not configurable anymore. A highlighter that returns the URL unchanged leaves it
as plain text, which covers the scheme whitelist and blacklist, match emails and match by top-level domain options.

```php
<?php
use VStelmakh\UrlHighlight\Highlighter\CallbackHighlighter;
use VStelmakh\UrlHighlight\Url;

// Highlight URLs with a scheme only, leave emails and URLs matched by top-level domain as plain text
$highlighter = new CallbackHighlighter(function (Url $url): string {
    if ($url->isEmail() || $url->scheme === null) {
        return $url->full;
    }

    return sprintf('<a href="%s">%s</a>', htmlspecialchars($url->toHref()), htmlspecialchars($url->full));
});

echo $urlHighlight->highlight('See https://example.com, example.com, user@example.com.', $highlighter);

// Output:
// See <a href="https://example.com">https://example.com</a>, example.com, user@example.com.
```
