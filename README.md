<img src="./resources/url-highlight-logo.svg" width="286" height="93" alt="Url highlight logo">

---

[![Build status](https://github.com/vstelmakh/url-highlight/workflows/build/badge.svg?branch=master)](https://github.com/vstelmakh/url-highlight/actions)
[![Packagist version](https://badgen.net/packagist/v/vstelmakh/url-highlight?color=orange)](https://packagist.org/packages/vstelmakh/url-highlight)
[![PHP version](https://badgen.net/packagist/php/vstelmakh/url-highlight?color=blue)](https://www.php.net/)
[![License](https://badgen.net/github/license/vstelmakh/url-highlight?color=4d9384)](LICENSE)

**Url highlight** - PHP library to parse urls from string input. Works with complex urls and edge cases.  

Features:
- Replace urls in string by html tags (make clickable). For html escaped string see [highlight type](#replace-urls-in-string-by-html-tags-make-clickable)
- Match urls without scheme by top-level domain
- Extract urls from string
- Check if string is url

## Installation
Install the latest version with [composer](https://getcomposer.org/):  
```bash
composer require vstelmakh/url-highlight
```
Also, there are
 [<img src="./resources/twig-logo.png" width="8" height="12" alt="Twig logo"> Twig extension](https://github.com/vstelmakh/url-highlight-twig-extension)
 and [<img src="./resources/symfony-logo.png" width="12" height="12" alt="Symfony logo"> Symfony bundle](https://github.com/vstelmakh/url-highlight-symfony-bundle) available.  

## Quick start  
```php
<?php
require __DIR__ . '/vendor/autoload.php';

use VStelmakh\UrlHighlight\UrlHighlight;

$urlHighlight = new UrlHighlight();
echo $urlHighlight->highlightUrls('Hello, http://example.com.');

// Output:
// Hello, <a href="http://example.com">http://example.com</a>.
```

## Configuration
Additional options could be provided via constructor:
- `match_by_tld` (bool): if true, will map matches without scheme by top level domain
    (example.com will be recognized as url). For full list of valid top level
    domains see: Domains::TOP_LEVEL_DOMAINS (default true).
- `default_scheme` (string): scheme to use when highlighting urls without scheme (default 'http').
- `scheme_blacklist` (string[]): array of schemes not allowed to be recognized as url (default []).
- `scheme_whitelist` (string[]): array of schemes explicitly allowed to be recognized as url (default []).

Example:
```php
$urlHighlight = new UrlHighlight([
    'match_by_tld' => false,
    'default_scheme' => 'https',
    'scheme_blacklist' => ['ssh', 'ftp'],
    'scheme_whitelist' => [],
]);
```

## Usage
#### Check if string is url
```php
$urlHighlight->isUrl('http://example.com'); // return: true
$urlHighlight->isUrl('Other string'); // return: false
```

#### Parse urls from string
```php
$urlHighlight->getUrls('Hello, http://example.com.');
// return: ['http://example.com']
```

#### Replace urls in string by html tags (make clickable)
```php
$urlHighlight->highlightUrls('Hello, http://example.com.');
// return: 'Hello, <a href="http://example.com">http://example.com</a>.'
```

Provide second argument to define **highlight type** and how to process input text. Allowed types:  
- `plain_text` a simple find and replace urls by html links (default).
- `html_special_chars` expect text to be html entities encoded. Works with both, plain text
    and html escaped string. Perform more regex operations than plain_text.

Use class constants to specify type, see `UrlHighlight::HIGHLIGHT_TYPE_*`  
```php
$htmlEscaped = '&lt;a href=&quot;http://example.com&quot;&gt;Example&lt;/a&gt;';
$urlHighlight->highlightUrls($htmlEscaped, UrlHighlight::HIGHLIGHT_TYPE_HTML_SPECIAL_CHARS);
// return: '&lt;a href=&quot;<a href="http://example.com">http://example.com</a>&quot;&gt;Example&lt;/a&gt;'
```

## Credits
[Volodymyr Stelmakh](https://github.com/vstelmakh)  
Licensed under the MIT License. See [LICENSE](LICENSE) for more information.  
