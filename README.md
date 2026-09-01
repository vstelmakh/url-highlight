<img src="./resources/url-highlight-logo.svg" width="286" height="93" alt="Url highlight logo">

---

[![Checks](https://github.com/vstelmakh/url-highlight/actions/workflows/checks.yml/badge.svg)](https://github.com/vstelmakh/url-highlight/actions/workflows/checks.yml)
[![Packagist Downloads](https://img.shields.io/packagist/dm/vstelmakh/url-highlight?label=Downloads&labelColor=%23363e45&color=%23ff751f)](https://packagist.org/packages/vstelmakh/url-highlight/stats)

**Url highlight** - PHP library to find URLs in text and turn them into clickable links. Made to handle complex URLs,
HTML markup and edge cases.

- Works with plain text, HTML or HTML entity encoded input.
- Matches URLs without a scheme by top-level domain, emails, IP hosts, Unicode and edge cases.
- Drops punctuation that belongs to the text, keeps what belongs to the URL.
- Leaves existing links and elements that may not contain anchors untouched.
- Renders links your way, or returns URLs as parsed components.

[🚀 **See examples** 👀](./docs/examples.md)

## Installation

Requires `PHP 8.4+` with the `mbstring` extension.

Install the latest version with [Composer](https://getcomposer.org/):
```bash
composer require vstelmakh/url-highlight
```

On PHP below 8.4, use version `^3.2`. It supports PHP 7.1 - 8.x but has a different API, see the
[3.x readme](https://github.com/vstelmakh/url-highlight/blob/v3.2.0/README.md).  
Coming from 3.x? See [Upgrade 3.x to 4.0](./docs/upgrade-4.0.md).

## Usage

[UrlHighlight](./src/UrlHighlight.php) is the only entry point. It provides 2 methods:
- [`highlight()`](#highlight-urls) to turn URLs into links.
- [`find()`](#find-urls) to get an array of URLs found in the text.

### Highlight URLs

Pass your input text to `highlight()` and get it back with every URL replaced by a link. Everything else stays as it is.

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use VStelmakh\UrlHighlight\UrlHighlight;

$urlHighlight = new UrlHighlight();
echo $urlHighlight->highlight('Check the example.com website.');

// Output:
// Check the <a href="http://example.com">example.com</a> website.
```

Two optional arguments control the result: `$highlighter` renders each URL, `$format` tells the library how to read
the input text.

#### Custom Highlighter

By default, each URL is simply wrapped in an anchor tag by [SimpleHighlighter](./src/Highlighter/SimpleHighlighter.php).
For any custom logic, you can use a [CallbackHighlighter](./src/Highlighter/CallbackHighlighter.php). The example below
adds a `nofollow` attribute and uses the host as the link text:

```php
<?php
use VStelmakh\UrlHighlight\Highlighter\CallbackHighlighter;
use VStelmakh\UrlHighlight\Url;

$highlighter = new CallbackHighlighter(fn (Url $url) => sprintf(
    '<a href="%s" rel="nofollow">%s</a>',
    htmlspecialchars($url->toHref()),
    htmlspecialchars($url->host),
));

echo $urlHighlight->highlight('Visit http://example.com/path?q=hello today.', $highlighter);

// Output:
// Visit <a href="http://example.com/path?q=hello" rel="nofollow">example.com</a> today.
```

A highlighter does not have to produce a link. The returned string replaces the URL, so it may be anything, for example
`[redacted]` for URLs pointing to an external host.

> [!TIP]
> There is also the [Highlighter](./src/Highlighter/Highlighter.php) interface. Implement it in a dedicated class for
> more complex logic. Such a highlighter is reusable and testable.
> See [SimpleHighlighter](./src/Highlighter/SimpleHighlighter.php) for an example.

> [!IMPORTANT]
> The returned string from highlighter is written to the output as is. Remember to escape HTML when necessary.

#### Input Format

The `$format` argument must describe the input text, otherwise URLs may not be highlighted correctly.

| Format                   | Text                                                                         |
|--------------------------|------------------------------------------------------------------------------|
| `Format::Html` (default) | HTML markup. Tags and elements that may not hold a link are left untouched.  |
| `Format::HtmlEncoded`    | HTML markup with entity encoded text, e.g. the result of `htmlspecialchars`. |
| `Format::Plain`          | Text without markup, taken as is. Angle brackets are ordinary characters.    |

The default `Format::Html` fits text without markup as well. Use `Format::Plain` when angle brackets in the text must
stay ordinary characters, for example to match `<user@example.com>`.

A common case is user input that you escape before rendering. Set the format to `Format::HtmlEncoded`, so the escaped
text is processed correctly:

```php
<?php
use VStelmakh\UrlHighlight\Format;

$encoded = htmlspecialchars('<b>http://example.com?a=1&b=2</b>');
echo $urlHighlight->highlight($encoded, format: Format::HtmlEncoded);

// Output:
// &lt;b&gt;<a href="http://example.com?a=1&amp;b=2">http://example.com?a=1&amp;b=2</a>&lt;/b&gt;
```

> [!NOTE]
> `Format::HtmlEncoded` matches against the decoded text, so the URL ends where it really ends, instead of running into
> the markup that follows. It does more work internally than the other formats, so use it only for encoded text.

### Find URLs

Pass your input text to `find()` and get the list of URLs it contains, each as a [Url](./src/Url.php) object with the
components parsed. Markup is ignored, the text is always processed as plain text.

```php
<?php
$urls = $urlHighlight->find('Visit example.com/path?a=1 today.');
$url = $urls[0];

$url->full;      // 'example.com/path?a=1'
$url->host;      // 'example.com'
$url->path;      // '/path'
$url->query;     // '?a=1'
$url->toHref();  // 'http://example.com/path?a=1'
$url->isEmail(); // false
```

> [!TIP]
> See [Url](./src/Url.php) for all available components and methods.

## Contributing

Contributions are welcome. Please check out [CONTRIBUTING.md](./CONTRIBUTING.md) for guidelines.  
If you find this library useful, don't hesitate to give it a star! ⭐

## Credits

[Volodymyr Stelmakh](https://github.com/vstelmakh)  
Licensed under the MIT License. See [LICENSE](./LICENSE) for more information.
