# Changelog
All notable changes to this project are documented in this file.  
Releases following [Semantic Versioning](https://semver.org/spec/v2.0.0.html) specification.  

## 4.0.0
Version 4 is a major rethink of the library, based on everything learned since the first release.
The public interface is new and much simpler, so code written using version 3.x needs changes.

- **\[BC break\]** Raised the minimum PHP version to 8.4
- **\[BC break\]** Simplified the public interface
  - Replaced `HighlighterInterface` by a [Highlighter](./src/Highlighter/Highlighter.php) interface
  - Replaced the encoder by the [Format](./src/Format.php) enum: `Plain`, `Html`, `HtmlEncoded`
  - Removed the validator
  - Renamed methods: `highlightUrls` to `highlight` and `getUrls` to `find`
  - Removed `isUrl` method (no replacement provided)
- Improved URL matching precision
- Improved performance and memory consumption
- Added IP address host support (matched with a port or a path)
- Improved HTML support, content of the elements that may not contain an anchor is skipped: `a`, `button`, `datalist`,
  `math`, `script`, `select`, `style`, `svg`, `textarea`, `title`

## 3.2.0
- Improved scheme matching in compliance with RFC 3986
- Improved host matching with invisible characters (joiners) in compliance with RFC 5892 (Appendix A)

## 3.1.4
- Updated a top-level domain list to recent changes

## 3.1.3
- Updated a top-level domain list to recent changes
- Added CI build for PHP 8.5

## 3.1.2
- Updated a top-level domain list to recent changes

## 3.1.1
- Updated a top-level domain list to recent changes
- Added CI build for PHP 8.4

## 3.1.0
- Improved userinfo matching in compliance with RFC 3986 Uniform Resource Identifier (URI): Generic Syntax

## 3.0.3
- Updated a top-level domain list to recent changes
- Added CI build for PHP 8.3

## 3.0.2
- Updated a top-level domain list to recent changes
- Added CI build for PHP 8.2
- Added declare strict types

## 3.0.1
- Updated a top-level domain list to recent changes
- Added CI build for PHP 8.1

## 3.0.0
- **\[BC break\]** Refactored [HighlighterInterface](https://github.com/vstelmakh/url-highlight/blob/v3.2.0/src/Highlighter/HighlighterInterface.php) to be more flexible
  - Refactored [HtmlHighlighter](https://github.com/vstelmakh/url-highlight/blob/v3.2.0/src/Highlighter/HtmlHighlighter.php) to follow a template method pattern (much easier to extend)
  - Refactored [MarkdownHighlighter](https://github.com/vstelmakh/url-highlight/blob/v3.2.0/src/Highlighter/MarkdownHighlighter.php) to be aware of HTML content (extends [HtmlHighlighter](https://github.com/vstelmakh/url-highlight/blob/v3.2.0/src/Highlighter/HtmlHighlighter.php))
- Added [MatcherFactory](https://github.com/vstelmakh/url-highlight/blob/v3.2.0/src/Matcher/MatcherFactory.php) and [ReplacerFactory](https://github.com/vstelmakh/url-highlight/blob/v3.2.0/src/Replacer/ReplacerFactory.php)

## 2.3.0
- Added PHP 8 compatibility
- Renamed class `VStelmakh\UrlHighlight\Matcher\Match` to `UrlMatch`
  > Since PHP v8.0 `match` is a reserved keyword.  
  > This will affect you if custom [Validator](https://github.com/vstelmakh/url-highlight/blob/v3.2.0/README.md#validator) or [Highlighter](https://github.com/vstelmakh/url-highlight/blob/v3.2.0/README.md#highlighter) implemented.
  > Consider expecting [UrlMatch](https://github.com/vstelmakh/url-highlight/blob/v3.2.0/src/Matcher/UrlMatch.php) instead of `Match` in your implementations.

## 2.2.0
- Fixed match complex URLs with brackets (e.g. ELK URLs)
- Improved matcher performance (~3x faster)
- Fixed HTML entity encoder regex builder (not all html entities were encoded)
- Added fallback to encoded matcher to skip match if the encoder is not able to encode
- Improved URL match regex to cover more cases
- Added match emails by default in validator

## 2.1.0
- Added HTML highlighter tag attributes
- Added Markdown highlighter

## 2.0.0
- **\[BC break\]** Replaced array configuration by specific interfaces: Validator, Highlighter, Encoder
- Added support for HTML escaped input
- Fixed HTML highlight when href for URLs contains `"` (double quote)
- Added changelog

## 1.2.4
- Fixed match by host includes colon at the end (e.g. `example.com:` will not include colon anymore)

## 1.2.3
- Fixed match scheme without host (e.g. `http://` will not match anymore)

## 1.2.2
- Improved URL regex (stricter scheme and host name)
- Changed to not match emails (temporary)

## 1.2.1
- Added `.gitattributes` to ignore dev files (e.g. tests) on export
- Improved readme
- Added project logo

## 1.2.0
- Added configuration. Additional options could be provided via constructor:
  - `match_by_tld`
  - `default_scheme`
  - `scheme_blacklist`
  - `scheme_whitelist`

## 1.1.0
- Added locate URLs without a protocol using a top-level domain name

## 1.0.0
- Added check if string is URL `isUrl`
- Added parse URLs from string `getUrls`
- Added replace URLs in string by HTML tags `highlightUrls`
