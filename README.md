# Url highlight
![Build status](https://github.com/vstelmakh/url-highlight/workflows/build/badge.svg?branch=master)
![PHP version](https://img.shields.io/packagist/php-v/vstelmakh/url-highlight)
![License](https://img.shields.io/github/license/vstelmakh/url-highlight)

PHP library to parse urls from string input. Current features:
- Check if string is url
- Parse urls from string
- Replace urls in string by html tags

## Installation
There are [Twig extension](https://github.com/vstelmakh/url-highlight-twig-extension) and [Symfony bundle](https://github.com/vstelmakh/url-highlight-symfony-bundle) available.  

Install the latest version with:  
```bash
$ composer require vstelmakh/url-highlight
```

## Setup
Just instantiate class object:  
```php
<?php

use VStelmakh\UrlHighlight\UrlHighlight;

$urlHighlight = new UrlHighlight();
```

## Usage
Check if string is url:  
```php
$urlHighlight->isUrl('http://example.com'); // return: true
$urlHighlight->isUrl('Other string'); // return: false
```

Parse urls from string:  
```php
$urlHighlight->getUrls('Hello, http://example.com.'); // return: ['http://example.com.']
```

Replace urls in string by html tags:  
```php
$urlHighlight->highlightUrls('Hello, http://example.com.'); // return: 'Hello, <a href="http://example.com">http://example.com</a>.'
```

## Credits
[Volodymyr Stelmakh](https://github.com/vstelmakh)  
Licensed under the MIT License. See [LICENSE](LICENSE) for more information.  