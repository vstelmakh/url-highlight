# Changelog
All notable changes to this project are documented in this file.  
Releases follow the [Semantic Versioning](https://semver.org/spec/v2.0.0.html) specification.  

## 4.0.2
- Excluded URLs with an unsafe scheme (`javascript`, `vbscript`, `data`) from matching to prevent XSS.

## 4.0.1
- Updated the top-level domain list with recent changes.

## 4.0.0
Version 4 is a major rethink of the library, based on everything learned since the first release.
The public interface is new and much simpler, so code written using version 3.x needs changes.
See [Upgrade 3.x to 4.0](./docs/upgrade-4.0.md) for the migration steps.

- **\[BC break\]** Raised the minimum PHP version to 8.4.
- **\[BC break\]** Simplified the public interface:
  - Replaced `HighlighterInterface` with a [Highlighter](./src/Highlighter/Highlighter.php) interface.
  - Replaced the encoder with the [Format](./src/Format.php) enum: `Plain`, `Html`, `HtmlEncoded`.
  - Removed the validator.
  - Renamed methods: `highlightUrls` to `highlight` and `getUrls` to `find`.
  - Removed the `isUrl` method (no replacement provided).
- Improved URL matching precision.
- Improved performance, most notably on large input.
- Added IP address host support (matched with a port or a path).
- Improved HTML support. The content of elements that may not contain an anchor is skipped: `a`, `button`,
  `datalist`, `math`, `script`, `select`, `style`, `svg`, `textarea`, `title`.

## 3.2.0
- Improved scheme matching in compliance with RFC 3986.
- Improved host matching with invisible characters (joiners) in compliance with RFC 5892 (Appendix A).

## 3.1.4
- Updated the top-level domain list with recent changes.

## 3.1.3
- Updated the top-level domain list with recent changes.
- Added a CI check for PHP 8.5.

## 3.1.2
- Updated the top-level domain list with recent changes.

## 3.1.1
- Updated the top-level domain list with recent changes.
- Added a CI check for PHP 8.4.

## 3.1.0
- Improved userinfo matching in compliance with RFC 3986 Uniform Resource Identifier (URI): Generic Syntax.

## 3.0.3
- Updated the top-level domain list with recent changes.
- Added a CI check for PHP 8.3.

## 3.0.2
- Updated the top-level domain list with recent changes.
- Added a CI check for PHP 8.2.
- Added the strict types declaration.

## 3.0.1
- Updated the top-level domain list with recent changes.
- Added a CI check for PHP 8.1.

## 3.0.0
- **\[BC break\]** Refactored [HighlighterInterface](https://github.com/vstelmakh/url-highlight/blob/v3.2.0/src/Highlighter/HighlighterInterface.php) to be more flexible:
  - Refactored [HtmlHighlighter](https://github.com/vstelmakh/url-highlight/blob/v3.2.0/src/Highlighter/HtmlHighlighter.php) to follow a template method pattern (much easier to extend).
  - Refactored [MarkdownHighlighter](https://github.com/vstelmakh/url-highlight/blob/v3.2.0/src/Highlighter/MarkdownHighlighter.php) to be aware of HTML content (extends [HtmlHighlighter](https://github.com/vstelmakh/url-highlight/blob/v3.2.0/src/Highlighter/HtmlHighlighter.php)).
- Added [MatcherFactory](https://github.com/vstelmakh/url-highlight/blob/v3.2.0/src/Matcher/MatcherFactory.php) and [ReplacerFactory](https://github.com/vstelmakh/url-highlight/blob/v3.2.0/src/Replacer/ReplacerFactory.php).

## 2.3.0
- Added PHP 8 compatibility.
- Renamed the class `VStelmakh\UrlHighlight\Matcher\Match` to `UrlMatch`.
  > Since PHP 8.0, `match` is a reserved keyword.  
  > This affects you if you implemented a custom [Validator](https://github.com/vstelmakh/url-highlight/blob/v3.2.0/README.md#validator) or [Highlighter](https://github.com/vstelmakh/url-highlight/blob/v3.2.0/README.md#highlighter).
  > Consider expecting [UrlMatch](https://github.com/vstelmakh/url-highlight/blob/v3.2.0/src/Matcher/UrlMatch.php) instead of `Match` in your implementations.

## 2.2.0
- Fixed matching of complex URLs with brackets (e.g. ELK URLs).
- Improved matcher performance (~3x faster).
- Fixed the HTML entity encoder regex builder (not all HTML entities were encoded).
- Added a fallback to the encoded matcher to skip a match if the encoder is not able to encode it.
- Improved the URL match regex to cover more cases.
- Added matching of emails by default in the validator.

## 2.1.0
- Added tag attributes to the HTML highlighter.
- Added a Markdown highlighter.

## 2.0.0
- **\[BC break\]** Replaced the array configuration with specific interfaces: Validator, Highlighter, Encoder.
- Added support for HTML escaped input.
- Fixed the HTML highlight when the href of a URL contains `"` (double quote).
- Added this changelog.

## 1.2.4
- Fixed a match by host that included a colon at the end (e.g. `example.com:` will not include the colon anymore).

## 1.2.3
- Fixed matching a scheme without a host (e.g. `http://` will not match anymore).

## 1.2.2
- Improved the URL regex (stricter scheme and host name).
- Changed to not match emails (temporary).

## 1.2.1
- Added `.gitattributes` to ignore dev files (e.g. tests) on export.
- Improved the readme.
- Added a project logo.

## 1.2.0
- Added configuration. Additional options can be provided via the constructor:
  - `match_by_tld`
  - `default_scheme`
  - `scheme_blacklist`
  - `scheme_whitelist`

## 1.1.0
- Added locating URLs without a protocol by their top-level domain name.

## 1.0.0
- Added `isUrl` to check if a string is a URL.
- Added `getUrls` to parse URLs from a string.
- Added `highlightUrls` to replace URLs in a string with HTML tags.
