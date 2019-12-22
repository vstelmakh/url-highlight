# Url highlight
PHP library to parse urls from string input. Current features:
- Check if string is url
- Parse urls from string
- Replace urls in string by html tags

## Installation
Install the latest version with:  
```bash
$ composer require vstelmakh/url-highlight
```

## Setup
Just instantiate class object:  
```php
<?php

use VStelmakh\UrlHighlight;

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