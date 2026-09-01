# Examples

## Overview

The example below demonstrates highlighting URLs in text, combining cases of varying complexity.

**Input:**  
A URL with a scheme is the easy case: http&#65279;://example.com, and https&#65279;://example.com works the same.
Other schemes are matched as well, for example ftp&#65279;://example.com.
Without a scheme it still works, example.com becomes a link, but readme.txt does not.
Punctuation belongs to the sentence, not to the URL: did you visit http&#65279;://example.com?
A comma stays out of example.com/first, a semicolon out of example.com/second; and a trailing dot out of example.com/third.
Brackets are matched in pairs, so an enclosed URL stays whole (http&#65279;://example.com/path/(brackets)), and a quoted one too: "example.com/path".
Emails become mailto links, write to user@&#65279;example.com, to user+tag@&#65279;mail.example.com, or spell the scheme out as mailto:user@&#65279;example.com.
A host may carry a port, example.com:8080/path, or be an IP address, 127.0.0.1:8080/health, while a version number such as 1.2.3.4 stays plain text.
Credentials are kept, http&#65279;://user:pass@&#65279;example.com, and so is Unicode, http&#65279;://приклад.укр/привіт.
A path may have several segments, and a query several parameters: example.com/store/catalog/laptops?size=14"&amp;color=black#results.
Long and complex URLs are highlighted correctly, query and fragment included: http&#65279;://elk.example.com:81/app/kibana#/discover?_g=()&amp;_a=(columns:!(_source),index:'deve-',interval:auto,query:(query_string:(analyze_wildcard:!t,query:'')),sort:!('@timestamp',desc)).
An existing link, like &lt;a href="http&#65279;://example.com"&gt;example.com&lt;/a&gt;, is not highlighted twice.

**Result:**  
A URL with a scheme is the easy case: <a href="http://example.com" rel="nofollow">http://example.com</a>, and <a href="https://example.com" rel="nofollow">https://example.com</a> works the same.
Other schemes are matched as well, for example <a href="ftp://example.com" rel="nofollow">ftp://example.com</a>.
Without a scheme it still works, <a href="http://example.com" rel="nofollow">example.com</a> becomes a link, but readme.txt does not.
Punctuation belongs to the sentence, not to the URL: did you visit <a href="http://example.com" rel="nofollow">http://example.com</a>?
A comma stays out of <a href="http://example.com/first" rel="nofollow">example.com/first</a>, a semicolon out of <a href="http://example.com/second" rel="nofollow">example.com/second</a>; and a trailing dot out of <a href="http://example.com/third" rel="nofollow">example.com/third</a>.
Brackets are matched in pairs, so an enclosed URL stays whole (<a href="http://example.com/path/(brackets)" rel="nofollow">http://example.com/path/(brackets)</a>), and a quoted one too: "<a href="http://example.com/path" rel="nofollow">example.com/path</a>".
Emails become mailto links, write to <a href="mailto:user@example.com" rel="nofollow">user@example.com</a>, to <a href="mailto:user+tag@mail.example.com" rel="nofollow">user+tag@mail.example.com</a>, or spell the scheme out as <a href="mailto:user@example.com" rel="nofollow">mailto:user@example.com</a>.
A host may carry a port, <a href="http://example.com:8080/path" rel="nofollow">example.com:8080/path</a>, or be an IP address, <a href="http://127.0.0.1:8080/health" rel="nofollow">127.0.0.1:8080/health</a>, while a version number such as 1.2.3.4 stays plain text.
Credentials are kept, <a href="http://user:pass@example.com" rel="nofollow">http://user:pass@example.com</a>, and so is Unicode, <a href="http://приклад.укр/привіт" rel="nofollow">http://приклад.укр/привіт</a>.
A path may have several segments, and a query several parameters: <a href="http://example.com/store/catalog/laptops?size=14&quot;&amp;color=black#results" rel="nofollow">example.com/store/catalog/laptops?size=14&quot;&amp;color=black#results</a>.
Long and complex URLs are highlighted correctly, query and fragment included: <a href="http://elk.example.com:81/app/kibana#/discover?_g=()&amp;_a=(columns:!(_source),index:&apos;deve-&apos;,interval:auto,query:(query_string:(analyze_wildcard:!t,query:&apos;&apos;)),sort:!(&apos;@timestamp&apos;,desc))" rel="nofollow">http://elk.example.com:81/app/kibana#/discover?_g=()&amp;_a=(columns:!(_source),index:&apos;deve-&apos;,interval:auto,query:(query_string:(analyze_wildcard:!t,query:&apos;&apos;)),sort:!(&apos;@timestamp&apos;,desc))</a>.
An existing link, like <a href="http://example.com" rel="nofollow">example.com</a>, is not highlighted twice.

## Matching

The table shows which part of the input is matched as a URL, and the rule that applies.

| Input                                     | Matched URL                    | Rule                                         |
|-------------------------------------------|--------------------------------|----------------------------------------------|
| `Visit http://example.com today.`         | `http://example.com`           | URL with a (any) scheme is always matched    |
| `Visit example.com today.`                | `example.com`                  | no scheme, host has a known top-level domain |
| `Read readme.txt now.`                    | not matched                    | `txt` is not a top-level domain              |
| `Did you visit http://example.com?`       | `http://example.com`           | trailing punctuation belongs to the text     |
| `See (example.com/path/(brackets)) here.` | `example.com/path/(brackets)`  | brackets are matched in pairs                |
| `Quote "example.com/path" here.`          | `example.com/path`             | enclosing quotes belong to the text          |
| `Contact user@example.com now.`           | `user@example.com`             | email                                        |
| `Contact mailto:user@example.com now.`    | `mailto:user@example.com`      | email with the scheme                        |
| `Visit example.com:8080/path now.`        | `example.com:8080/path`        | host with a port                             |
| `Check 127.0.0.1:8080/health now.`        | `127.0.0.1:8080/health`        | IP host with a port or a path                |
| `Version 1.2.3.4 released.`               | not matched                    | IP host without a port or a path             |
| `Login http://user:pass@example.com now.` | `http://user:pass@example.com` | credentials are part of the URL              |
| `Visit http://приклад.укр/привіт now.`    | `http://приклад.укр/привіт`    | Unicode host and path                        |

> [!TIP]
> For the full set of cases, see [UrlRegexTest](../tests/Matcher/UrlRegexTest.php),
> [MatcherTest](../tests/Matcher/MatcherTest.php) and [UrlHighlightTest](../tests/UrlHighlightTest.php).

## Formats

The format tells the library how to read the input. With the HTML formats, tags stay as they are and URLs are matched
only in the text between them.

| Format                | Input                                          | Output                                                                                                                                                                                                               |
|-----------------------|------------------------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `Format::Html`        | `<p>Visit example.com now.</p>`                | `<p>Visit <a href="http://example.com">example.com</a> now.</p>`<br>URL highlighted, anchor may be placed inside `p`                                                                                                 |
| `Format::Html`        | `<a href="http://example.com">example.com</a>` | `<a href="http://example.com">example.com</a>`<br>not changed, the URL is already a link                                                                                                                             |
| `Format::Html`        | `<script>var u = "example.com";</script>`      | `<script>var u = "example.com";</script>`<br>not changed, elements that may not contain an anchor are skipped: `a`, `button`, `datalist`, `math`, `script`, `select`, `style`, `svg`, `textarea`, `title`            |
| `Format::HtmlEncoded` | `&lt;b&gt;example.com?a=1&amp;b=2&lt;/b&gt;`   | `&lt;b&gt;<a href="http://example.com?a=1&amp;b=2">example.com?a=1&amp;b=2</a>&lt;/b&gt;`<br>URL matched against the decoded text, the original encoding is kept                                                     |
| `Format::Html`        | `&lt;b&gt;example.com?a=1&amp;b=2&lt;/b&gt;`   | `&lt;b&gt;<a href="http://example.com?a=1&amp;amp;b=2&amp;lt;/b&amp;gt">example.com?a=1&amp;amp;b=2&amp;lt;/b&amp;gt</a>;`<br>wrong format for this input, the match runs past the URL, into the markup that follows |
| `Format::Plain`       | `Mail <user@example.com> please.`              | `Mail <<a href="mailto:user@example.com">user@example.com</a>> please.`<br>angle brackets are ordinary characters, `Format::Html` would read `<user@example.com>` as a tag                                           |
