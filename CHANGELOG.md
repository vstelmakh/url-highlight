# Changelog
All notable changes to this project are documented in this file.  
Releases following [Semantic Versioning](https://semver.org/spec/v2.0.0.html) specification.  

### 3.2.0
- Improved scheme matching in compliance with RFC 3986
- Improved host matching with invisible characters (joiners) in compliance with RFC 5892 (Appendix A)

### 3.1.4
- Updated a top-level domain list to recent changes

### 3.1.3
- Updated a top-level domain list to recent changes
- Added CI build for PHP 8.5

### 3.1.2
- Updated a top-level domain list to recent changes

### 3.1.1
- Updated a top-level domain list to recent changes
- Added CI build for PHP 8.4

### 3.1.0
- Improved userinfo matching in compliance with RFC 3986 Uniform Resource Identifier (URI): Generic Syntax

### 3.0.3
- Updated a top-level domain list to recent changes
- Added CI build for PHP 8.3

### 3.0.2
- Updated a top-level domain list to recent changes
- Added CI build for PHP 8.2
- Added declare strict types

### 3.0.1
- Updated a top-level domain list to recent changes
- Added CI build for PHP 8.1

### 3.0.0
- Refactored [HighlighterInterface](./src/Highlighter/HighlighterInterface.php) to be more flexible **\[BC break\]**
  - Refactored [HtmlHighlighter](./src/Highlighter/HtmlHighlighter.php) to follow a template method pattern (much easier to extend)
  - Refactored [MarkdownHighlighter](./src/Highlighter/MarkdownHighlighter.php) to be aware of HTML content (extends [HtmlHighlighter](./src/Highlighter/HtmlHighlighter.php))
- Added [MatcherFactory](./src/Matcher/MatcherFactory.php) and [ReplacerFactory](./src/Replacer/ReplacerFactory.php)

### 2.3.0
- Added PHP 8 compatibility
- Renamed class `VStelmakh\UrlHighlight\Matcher\Match` to `UrlMatch`
  > Since PHP v8.0 `match` is a reserved keyword.  
  > This will affect you if custom [Validator](./README.md#validator) or [Highlighter](./README.md#highlighter) implemented.
  > Consider expecting [UrlMatch](./src/Matcher/UrlMatch.php) instead of `Match` in your implementations.

### 2.2.0
- Fixed match complex urls with brackets (e.g. ELK urls)
- Improved matcher performance (~3x faster)
- Fixed HTML entity encoder regex builder (not all html entities were encoded)
- Added fallback to encoded matcher to skip match if the encoder is not able to encode
- Improved url match regex to cover more cases
- Added match emails by default in validator

### 2.1.0
- Added HTML highlighter tag attributes
- Added Markdown highlighter

### 2.0.0
- Replaced array configuration by specific interfaces: Validator, Highlighter, Encoder **\[BC break\]**
- Added support for HTML escaped input
- Fixed HTML highlight when href for urls contains `"` (double quote)
- Added changelog

### 1.2.4
- Fixed match by host includes colon at the end (e.g. `example.com:` will not include colon anymore)

### 1.2.3
- Fixed match scheme without host (e.g. `http://` will not match anymore)

### 1.2.2
- Improved url regex (stricter scheme and host name)
- Changed to not match emails (temporary)

### 1.2.1
- Added `.gitattributes` to ignore dev files (e.g. tests) on export
- Improved readme
- Added project logo

### 1.2.0
- Added configuration. Additional options could be provided via constructor:
  - `match_by_tld`
  - `default_scheme`
  - `scheme_blacklist`
  - `scheme_whitelist`

### 1.1.0
- Added locate urls without a protocol using a top-level domain name

### 1.0.0
- Added check if string is url `isUrl`
- Added parse urls from string `getUrls`
- Added replace urls in string by HTML tags `highlightUrls`
