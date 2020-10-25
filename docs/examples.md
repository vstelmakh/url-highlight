[Readme](../README.md) / Examples

---

# Url highlight examples

Generic examples of Url highlight capabilities.
> 🔬 Feel free to inspect [UrlHighlightTest](../tests/UrlHighlightTest.php) and [MatcherTest](../tests/Matcher/MatcherTest.php) for more details.

## Quick example

**Input:**  
The main purpose of this library is to linkify urls in string input. The easiest case would be url with scheme like http&#65279;://example.com.
Without scheme example.com - also works fine, but not for example.txt. You want to know what about punctuation. 
If this highlighted properly: http&#65279;://example.com? It is! Also, it takes care about brackets. So enclosed urls looks fine 
(http&#65279;://example.com/path_with_(brackets)). Of course, it supports emails user@&#65279;example.com. In real life 
there is urls like this one: http&#65279;://elk.example.com:81/app/kibana#/discover?_g=()&_a=(columns:!(_source),index:'deve-*',interval:auto,query:(query_string:(analyze_wildcard:!t,query:'*')),sort:!('@timestamp',desc)) 
from ELK. As you see, it also works fine. Yes, it supports HTML and HTML encoded input, see examples bellow.

**Result:**  
The main purpose of this library is to linkify urls in string input. The easiest case would be url with scheme like <a href="http://example.com" rel="nofollow">http://example.com</a>.
Without scheme <a href="http://example.com" rel="nofollow">example.com</a> - also works fine, but not for example.txt. You want to know what about punctuation. 
If this highlighted properly: <a href="http://example.com" rel="nofollow">http://example.com</a>? It is! Also, it takes care about brackets. So enclosed urls looks fine 
(<a href="http://example.com/path_with_(brackets)" rel="nofollow">http://example.com/path_with_(brackets)</a>). Of course, it supports emails <a href="mailto:user@example.com" rel="nofollow">user@example.com</a>. In real life 
there is urls like this one: <a href="http://elk.example.com:81/app/kibana#/discover?_g=()&_a=(columns:!(_source),index:'deve-*',interval:auto,query:(query_string:(analyze_wildcard:!t,query:'*')),sort:!('@timestamp',desc))" rel="nofollow">http://elk.example.com:81/app/kibana#/discover?_g=()&_a=(columns:!(_source),index:'deve-*',interval:auto,query:(query_string:(analyze_wildcard:!t,query:'*')),sort:!('@timestamp',desc))</a> 
from ELK. As you see, it also works fine. Yes, it supports HTML and HTML encoded input, see examples bellow.

## Specific cases

### Plain text

#### Simple
|           | Value                                                                        |
|-----------|------------------------------------------------------------------------------|
| Input:    | `Text before http://example.com and after.`                                  |
| Output:   | `Text before <a href="http://example.com">http://example.com</a> and after.` |
| Rendered: | Text before <a href="http://example.com" rel="nofollow">http://example.com</a> and after.   |

#### No scheme
|           | Value                                                                                      |
|-----------|--------------------------------------------------------------------------------------------|
| Input:    | `Text before example.com and after, but not example.txt.`                                  |
| Output:   | `Text before <a href="http://example.com">example.com</a> and after, but not example.txt.` |
| Rendered: | Text before <a href="http://example.com" rel="nofollow">example.com</a> and after, but not example.txt.   |

#### Punctuation
|           | Value                                                                |
|-----------|----------------------------------------------------------------------|
| Input:    | `Did you visit http://example.com?`                                  |
| Output:   | `Did you visit <a href="http://example.com">http://example.com</a>?` |
| Rendered: | Did you visit <a href="http://example.com" rel="nofollow">http://example.com</a>?   |

#### Brackets
|           | Value                                                                                                                    |
|-----------|--------------------------------------------------------------------------------------------------------------------------|
| Input:    | `Text before (http://example.com/path_with_(brackets)) and after.`                                                       |
| Output:   | `Text before (<a href="http://example.com/path_with_(brackets)">http://example.com/path_with_(brackets)</a>) and after.` |
| Rendered: | Text before (<a href="http://example.com/path_with_(brackets)" rel="nofollow">http://example.com/path_with_(brackets)</a>) and after.   |

### HTML

#### Simple
|           | Value                                                                               |
|-----------|-------------------------------------------------------------------------------------|
| Input:    | `<p>Text before http://example.com and after.</p>`                                  |
| Output:   | `<p>Text before <a href="http://example.com">http://example.com</a> and after.</p>` |
| Rendered: | <p>Text before <a href="http://example.com" rel="nofollow">http://example.com</a> and after.</p>   |

#### Link
|           | Value                                                                        |
|-----------|------------------------------------------------------------------------------|
| Input:    | `Text before <a href="http://example.com">http://example.com</a> and after.` |
| Output:   | `Text before <a href="http://example.com">http://example.com</a> and after.` |
| Rendered: | Text before <a href="http://example.com" rel="nofollow">http://example.com</a> and after.   |

#### Image
|           | Value                                                                                                                                                            |
|-----------|------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Input:    | `Text before <img src="https://github.githubassets.com/images/icons/emoji/unicode/1f369.png" height="14"> http://example.com and after.`                                  |
| Output:   | `Text before <img src="https://github.githubassets.com/images/icons/emoji/unicode/1f369.png" height="14"> <a href="http://example.com">http://example.com</a> and after.` |
| Rendered: | Text before <img src="https://github.githubassets.com/images/icons/emoji/unicode/1f369.png" height="14"> <a href="http://example.com" rel="nofollow">http://example.com</a> and after.   |

### Email

#### Simple
|           | Value                                                                           |
|-----------|---------------------------------------------------------------------------------|
| Input:    | `Text before user@example.com and after.`                                       |
| Output:   | `Text before <a href="mailto:user@example.com">user@example.com</a> and after.` |
| Rendered: | Text before <a href="mailto:user@example.com" rel="nofollow">user@example.com</a> and after.   |

#### With scheme
|           | Value                                                                                  |
|-----------|----------------------------------------------------------------------------------------|
| Input:    | `Text before mailto:user@example.com and after.`                                       |
| Output:   | `Text before <a href="mailto:user@example.com">mailto:user@example.com</a> and after.` |
| Rendered: | Text before <a href="mailto:user@example.com" rel="nofollow">mailto:user@example.com</a> and after.   |

### Encoded

#### HTML special chars
|           | Value                                                                                                                                                         |
|-----------|---------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Input:    | `Text before &lt;a href=&quot;http://example.com&quot;&gt;example.com&lt;/a&gt; and after.`                                                                   |
| Output:   | `Text before &lt;a href=&quot;<a href="http://example.com">http://example.com</a>&quot;&gt;<a href="http://example.com">example.com</a>&lt;/a&gt; and after.` |
| Rendered: | Text before &lt;a href=&quot;<a href="http://example.com" rel="nofollow">http://example.com</a>&quot;&gt;<a href="http://example.com" rel="nofollow">example.com</a>&lt;/a&gt; and after.   |

> 💡 **Tip**: [HtmlSpecialcharsEncoder](../src/Encoder/HtmlSpecialcharsEncoder.php) used here.
> See [Encoder](../README.md#encoder) for more details.

#### HTML encoded
|           | Value                                                                                                                                                                                                                                                                                        |
|-----------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Input:    | `&#x54;&#x65;&#x78;&#x74;&#x20;&#x62;&#x65;&#x66;&#x6F;&#x72;&#x65;&#x20;&#x68;&#x74;&#x74;&#x70;&colon;&sol;&sol;&#x65;&#x78;&#x61;&#x6D;&#x70;&#x6C;&#x65;&period;&#x63;&#x6F;&#x6D;&#x20;&#x61;&#x6E;&#x64;&#x20;&#x61;&#x66;&#x74;&#x65;&#x72;&period;`                                  |
| Output:   | `&#x54;&#x65;&#x78;&#x74;&#x20;&#x62;&#x65;&#x66;&#x6F;&#x72;&#x65;&#x20;<a href="http://example.com">&#x68;&#x74;&#x74;&#x70;&colon;&sol;&sol;&#x65;&#x78;&#x61;&#x6D;&#x70;&#x6C;&#x65;&period;&#x63;&#x6F;&#x6D;</a>&#x20;&#x61;&#x6E;&#x64;&#x20;&#x61;&#x66;&#x74;&#x65;&#x72;&period;` |
| Rendered: | &#x54;&#x65;&#x78;&#x74;&#x20;&#x62;&#x65;&#x66;&#x6F;&#x72;&#x65;&#x20;<a href="http://example.com" rel="nofollow">&#x68;&#x74;&#x74;&#x70;&colon;&sol;&sol;&#x65;&#x78;&#x61;&#x6D;&#x70;&#x6C;&#x65;&period;&#x63;&#x6F;&#x6D;</a>&#x20;&#x61;&#x6E;&#x64;&#x20;&#x61;&#x66;&#x74;&#x65;&#x72;&period;   |

> 💡 **Tip**: [HtmlEntitiesEncoder](../src/Encoder/HtmlEntitiesEncoder.php) used here.
> See [Encoder](../README.md#encoder) for more details.
