# Changelog
All notable changes to this project documented in this file.  
Releases following [Semantic Versioning](https://semver.org/spec/v2.0.0.html) specification.  

### v2.2.0 (2020-09-04)
- Fixed match complex urls with brackets (e.g. ELK urls)
- Improved matcher performance (~3x faster)
- Fixed html entity encoder regex builder (not all html entities were encoded)
- Added fallback to encoded matcher to skip match if encoder not able to encode
- Improved url match regex to cover more cases
- Added match emails by default in validator

### v2.1.0 (2020-07-28)
- Added html highlighter tag attributes
- Added markdown highlighter

### v2.0.0 (2020-05-19)
- Replaced array configuration by specific interfaces: Validator, Highlighter, Encoder **\[BC break\]**
- Added support for html escaped input
- Fixed html highlight when href for urls contains `"` (double quote)
- Added changelog

### v1.2.4 (2020-05-01)
- Fixed match by host includes colon at the end (e.g. `example.com:` will not include colon anymore)

### v1.2.3 (2020-04-04)
- Fixed match scheme without host (e.g. `http://` will not match anymore)

### v1.2.2 (2020-03-22)
- Improved url regex (more strict scheme and host name)
- Changed to not match emails (temporary)

### v1.2.1 (2020-03-14)
- Added `.gitattributes` to ignore dev files (e.g. tests) on export
- Improved readme
- Added project logo

### v1.2.0 (2020-01-15)
- Added configuration. Additional options could be provided via constructor:
  - `match_by_tld`
  - `default_scheme`
  - `scheme_blacklist`
  - `scheme_whitelist`

### v1.1.0 (2020-01-05)
- Added locate urls without protocol using top level domain names

### v1.0.0 (2019-12-22)
- Added check if string is url `isUrl`
- Added parse urls from string `getUrls`
- Added replace urls in string by html tags `highlightUrls`
