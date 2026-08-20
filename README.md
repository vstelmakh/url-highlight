<img src="./resources/url-highlight-logo.svg" width="286" height="93" alt="Url highlight logo">

---

**Url highlight** - PHP library to find URLs in text and turn them into links. Made to handle complex URLs, HTML markup
and edge cases.

Features:
- Highlight URLs in plain text, HTML or HTML entity encoded input.
- Match URLs without a scheme by top-level domain, from the IANA list: `example.com` is a URL, `readme.txt` is not.
- Handle emails, IP hosts, ports, credentials and Unicode: `user@example.com`, `127.0.0.1:8080/health`, `привіт.укр/★`.
- Drop punctuation that belongs to the text, keep brackets that belong to the URL:
  `(see example.com/path_(1)).` matches `example.com/path_(1)`.
- Leave existing links and HTML elements that may not contain anchors (`a`, `script`, etc.) untouched, nothing is
  highlighted twice.
- Render each URL your way, or extract them as parsed components.

[🚀 **See examples** 👀](./docs/examples.md)

## Installation

Requires `PHP 8.4+` with the `mbstring` extension.

Install the latest version with [Composer](https://getcomposer.org/) from
[Packagist](https://packagist.org/packages/vstelmakh/url-highlight):
```bash
composer require vstelmakh/url-highlight
```

On PHP below 8.4, use version `^3.2`. It supports PHP 7.1 - 8.x and has a different API, see the
[3.x readme](https://github.com/vstelmakh/url-highlight/blob/v3.2.0/README.md).

## Usage

[UrlHighlight](./src/UrlHighlight.php) is the only entry point. It has no state, so you can create it once and reuse it.
It provides 2 methods:
- [`highlight()`](#highlight-urls) to turn URLs into links.
- [`find()`](#find-urls) to get an array of URLs found in the text.

### Highlight URLs

Pass your text to `highlight()` and get it back with every URL replaced by a link. Everything else stays as it is.

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
the text.

#### Input Format

The `$format` argument must describe the text, otherwise URLs may not be highlighted correctly.

| Format                   | Text                                                                         |
|--------------------------|------------------------------------------------------------------------------|
| `Format::Html` (default) | HTML markup. Tags and elements that may not hold a link are left untouched.  |
| `Format::HtmlEncoded`    | HTML markup with entity encoded text, e.g. the result of `htmlspecialchars`. |
| `Format::Plain`          | Text without markup, taken as is. Angle brackets are ordinary characters.    |

```php
<?php
use VStelmakh\UrlHighlight\Format;

$encoded = htmlspecialchars('<b>http://example.com?a=1&b=2</b>');
echo $urlHighlight->highlight($encoded, format: Format::HtmlEncoded);

// Output:
// &lt;b&gt;<a href="http://example.com?a=1&amp;b=2">http://example.com?a=1&amp;b=2</a>&lt;/b&gt;
```

> [!NOTE]
> `Format::HtmlEncoded` matches against the decoded text, so `&amp;` does not break the URL, and keeps the original
> encoding in the output. It needs more work than the other formats, so use it only for encoded text.

#### Custom Highlighter

By default, each URL is wrapped in an anchor tag by [SimpleHighlighter](./src/Highlighter/SimpleHighlighter.php).
For one-off custom logic, you can use a [CallbackHighlighter](./src/Highlighter/CallbackHighlighter.php):

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

> [!TIP]
> For the logic you want to reuse or test on its own, implement the [Highlighter](./src/Highlighter/Highlighter.php)
> interface instead. See [SimpleHighlighter](./src/Highlighter/SimpleHighlighter.php) for implementation example.

> [!IMPORTANT]
> The returned string from highlighter is written to the output as is. Remember to escape HTML.

### Find URLs

Pass your text to `find()` and get the list of URLs it contains, each as a [Url](./src/Url.php) object with the
components parsed. Markup is ignored, the text is always read as plain text.

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
