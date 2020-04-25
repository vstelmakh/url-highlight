<img src="./resources/url-highlight-logo.svg" width="286" height="93" alt="Url highlight logo">

---

[![Build status](https://github.com/vstelmakh/url-highlight/workflows/build/badge.svg?branch=master)](https://github.com/vstelmakh/url-highlight/actions)
[![PHP version](https://img.shields.io/packagist/php-v/vstelmakh/url-highlight)](https://www.php.net/)
[![License](https://img.shields.io/github/license/vstelmakh/url-highlight)](LICENSE)

**Url highlight** - PHP library to parse urls from string input. Current features:
- Check if string is url
- Parse urls from string
- Replace urls in string by html tags

## Installation
Install the latest version with [composer](https://getcomposer.org/):  
```bash
composer require vstelmakh/url-highlight
```
Also, there are
 [<img src="./resources/twig-logo.png" width="8" height="12" alt="Twig logo"> Twig extension](https://github.com/vstelmakh/url-highlight-twig-extension)
 and [<img src="./resources/symfony-logo.png" width="12" height="12" alt="Symfony logo"> Symfony bundle](https://github.com/vstelmakh/url-highlight-symfony-bundle) available.  

## Setup
Just instantiate class object:  
```php
<?php
require __DIR__ . '/vendor/autoload.php';

use VStelmakh\UrlHighlight\UrlHighlight;

$urlHighlight = new UrlHighlight();
```

## Configuration
Additional options could be provided via constructor:
- `match_by_tld`: if true, will map matches without scheme by top level domain
    (example.com will be recognized as url). For full list of valid top level
    domains see: Domains::TOP_LEVEL_DOMAINS (default true).
- `default_scheme`: scheme to use when highlighting urls without scheme (default 'http').
- `scheme_blacklist`: array of schemes not allowed to be recognized as url (default []).
- `scheme_whitelist`: array of schemes explicitly allowed to be recognized as url (default []).

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
Check if string is url:  
```php
$urlHighlight->isUrl('http://example.com'); // return: true
$urlHighlight->isUrl('Other string'); // return: false
```

Parse urls from string:  
```php
$urlHighlight->getUrls('Hello, http://example.com.'); // return: ['http://example.com']
```

Replace urls in string by html tags:  
```php
$urlHighlight->highlightUrls('Hello, http://example.com.'); // return: 'Hello, <a href="http://example.com">http://example.com</a>.'
```

## Credits
[Volodymyr Stelmakh](https://github.com/vstelmakh)  
Licensed under the MIT License. See [LICENSE](LICENSE) for more information.  