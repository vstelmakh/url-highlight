[Readme](../README.md) / [Highlighter](../README.md#highlighter)  / Custom highlighter

---

# Custom highlighter
- [Extending `HtmlHighlighter`](#extending-htmlhighlighter)
- [Implementing `HighlighterInterface`](#implementing-highlighterinterface)
- [Testing custom highlighter](#testing-custom-highlighter)

If you need more than just replace links by html tags.
There are 2 options available for your choice:
- Extend [HtmlHighlighter](../src/Highlighter/HtmlHighlighter.php), bundled with library. 
- Create highlighter implementing [HighlighterInterface](../src/Highlighter/HighlighterInterface.php).
  More flexibility, could be complicated.

> 💡 **Tip**: Check [HtmlHighlighter](../src/Highlighter/HtmlHighlighter.php) constructor.
> There is simple configuration available, possibly covering your case.

## Extending `HtmlHighlighter`
In most of the cases it would be enough to extend [HtmlHighlighter](../src/Highlighter/HtmlHighlighter.php).
It's highly customizable providing variety of protected methods which gives ability to change any part of highlighting process.

#### Example
Let's say you want to display only hostname as link text and add `nofollow` attribute for external websites.  
To achieve this, you need is to create custom highlighter and override 2 methods:

```php
<?php

use VStelmakh\UrlHighlight\Highlighter\HtmlHighlighter;
use VStelmakh\UrlHighlight\Matcher\UrlMatch;

class CustomHighlighter extends HtmlHighlighter
{
    protected function getText(UrlMatch $match): string
    {
        return $match->getHost();
    }
    
    protected function getAttributes(UrlMatch $match): string
    {
        return $match->getHost() === 'your-website.com'
            ? ''
            : ' rel="nofollow"';
    }
}
```

> 💡 **Tip**: [UrlMatch](../src/Matcher/UrlMatch.php) gives you access to all properties of current url match.
> Check class public methods for more details.

Then you need to configure [UrlHighlight](../src/UrlHighlight.php) to use `CustomHighlighter`. This could be done via dependency injection:

```php
<?php

use VStelmakh\UrlHighlight\UrlHighlight;

$customHighlighter = new CustomHighlighter();
$urlHighlight = new UrlHighlight(null, $customHighlighter);
echo $urlHighlight->highlightUrls('Follow http://your-website.com/welcome not http://other-website.com.');

// Output:
// Follow <a href="http://your-website.com/welcome">your-website.com</a> not 
//  <a href="http://other-website.com" rel="nofollow">other-website.com</a>.

// line break added for readability
```

For all the possibilities check protected methods in [HtmlHighlighter](../src/Highlighter/HtmlHighlighter.php).
It's well documented in doc-blocks itself.

## Implementing `HighlighterInterface`

If you need completely custom behaviour or need to highlight urls using not HTML syntax - implementing `HighlighterInterface`
could be the option for you.
This way is more complicated, but gives you ability to create completely custom behaviour.

> 💡 **Tip**: If you need custom highligter aware of HTML syntax, you could extend [HtmlHighlighter](../src/Highlighter/HtmlHighlighter.php),
> but completely change highlighting logic. See [MarkdownHighlighter](../src/Highlighter/MarkdownHighlighter.php) for example.

#### Example

For example, you want to remove all the urls from string input:

```php
<?php

use VStelmakh\UrlHighlight\Highlighter\HighlighterInterface;
use VStelmakh\UrlHighlight\Matcher\UrlMatch;
use VStelmakh\UrlHighlight\Replacer\ReplacerInterface;

class CustomHighlighter implements HighlighterInterface
{
    public function highlight(string $string, ReplacerInterface $replacer): string
    {
        // replaceCallback expects string input and callback accepting UrlMatch as argument
        // Callback should return string replacement for provided url match
        return $replacer->replaceCallback($string, function (UrlMatch $match) {
            // There is no reason to care about encoded input at this point.
            // Replacer internally using encoder provided to UrlHighlight constructor.
            return '%censored%';
        });
    }
}
```

> 💡 **Tip**: You could use private class method as a callback using `\Closure`,
> see [HtmlHighlighter::doHighlight](../src/Highlighter/HtmlHighlighter.php) for example.

```php
<?php

use VStelmakh\UrlHighlight\UrlHighlight;

$customHighlighter = new CustomHighlighter();
$urlHighlight = new UrlHighlight(null, $customHighlighter);
echo $urlHighlight->highlightUrls('Visit http://example.com and http://example2.com.');

// Output:
// Visit %censored% and %censored%.
```

## Testing custom highlighter
To test `HighlighterInterface::highlight()` you need to provide 2 arguments: input `string` and instance of [ReplacerInterface](../src/Replacer/ReplacerInterface.php).
Find a test string is simple, but for [ReplacerInterface](../src/Replacer/ReplacerInterface.php) you need a real object or mock.
That's the point where it could be confusing.

Mocking [ReplacerInterface](../src/Replacer/ReplacerInterface.php) is not easy and unnecessary.
Use [ReplacerFactory](../src/Replacer/ReplacerFactory.php) to get a real object implementing [ReplacerInterface](../src/Replacer/ReplacerInterface.php).
It's simple and gives you same behaviour as Url highlight library do.
So you can focus on testing your custom highlighter logic, instead of building complex mocks.

#### Example

```php
<?php

use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Replacer\ReplacerFactory;

class CustomHighlighterTest extends TestCase
{
    public function testHighlight(): void
    {
        $input = 'http://exmple.com';
        $expected = '<a href="http://exmple.com">http://exmple.com</a>';

        $htmlHighlighter = new CustomHighlighter();
        $replacer = ReplacerFactory::createReplacer();
        $actual = $htmlHighlighter->highlight($input, $replacer);

        self::assertSame($expected, $actual);
    }
}
```
> 💡 **Tip**: See [HtmlHighlighterTest](../tests/Highlighter/HtmlHighlighterTest.php) and
> [MarkdownHighlighterTest](../tests/Highlighter/MarkdownHighlighterTest.php) for additional examples.
