<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests\Replacer\Extractor;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Replacer\Extractor\Token\CommentToken;
use VStelmakh\UrlHighlight\Replacer\Extractor\Token\PlainToken;
use VStelmakh\UrlHighlight\Replacer\Extractor\Token\TagToken;
use VStelmakh\UrlHighlight\Replacer\Extractor\Token\TagType;
use VStelmakh\UrlHighlight\Replacer\Extractor\Token\Token;
use VStelmakh\UrlHighlight\Replacer\Extractor\Tokenizer;

class TokenizerTest extends TestCase
{
    private Tokenizer $tokenizer;

    #[\Override]
    protected function setUp(): void
    {
        $this->tokenizer = new Tokenizer();
    }

    /**
     * @param list<Token> $expected
     */
    #[DataProvider('tokenizeDataProvider')]
    public function testTokenize(string $html, array $expected): void
    {
        $actual = iterator_to_array($this->tokenizer->tokenize($html));
        self::assertEquals($expected, $actual);
    }

    /**
     * @return array<string, array{string, list<Token>}>
     */
    public static function tokenizeDataProvider(): array
    {
        return [
            'empty input' => ['', []],
            'plain text only' => ['text', [new PlainToken('text')]],
            'plain text multibyte' => ['Тест', [new PlainToken('Тест')]],
            'text around tag' => ['a<b>c', [
                new PlainToken('a'),
                new TagToken('<b>', 'b', TagType::Opening),
                new PlainToken('c'),
            ]],
            'adjacent tags leave no plain token in between' => ['<b>a</b><i>', [
                new TagToken('<b>', 'b', TagType::Opening),
                new PlainToken('a'),
                new TagToken('</b>', 'b', TagType::Closing),
                new TagToken('<i>', 'i', TagType::Opening),
            ]],
            'input ending with tag yields no trailing plain token' => ['<b>', [
                new TagToken('<b>', 'b', TagType::Opening),
            ]],

            'tag name uppercase is lowercased' => ['<DIV>', [
                new TagToken('<DIV>', 'div', TagType::Opening),
            ]],
            'tag name with digits' => ['<h1>', [
                new TagToken('<h1>', 'h1', TagType::Opening),
            ]],
            'tag with attributes' => ['<a href="#" class="link">', [
                new TagToken('<a href="#" class="link">', 'a', TagType::Opening),
            ]],
            'tag spanning multiple lines' => ["<a\nhref=\"#\">", [
                new TagToken("<a\nhref=\"#\">", 'a', TagType::Opening),
            ]],
            'tag end in double quoted attribute' => ['<a title="a>b">c', [
                new TagToken('<a title="a>b">', 'a', TagType::Opening),
                new PlainToken('c'),
            ]],
            'tag end in single quoted attribute' => ["<a title='a>b'>c", [
                new TagToken("<a title='a>b'>", 'a', TagType::Opening),
                new PlainToken('c'),
            ]],
            'tag self closing' => ['<br/>', [
                new TagToken('<br/>', 'br', TagType::SelfClosing),
            ]],
            'tag self closing with space before end' => ['<br />', [
                new TagToken('<br />', 'br', TagType::SelfClosing),
            ]],
            'tag without name' => ['<3>', [
                new TagToken('<3>', '', TagType::Opening),
            ]],
            'doctype has no name' => ['<!DOCTYPE html>', [
                new TagToken('<!DOCTYPE html>', '', TagType::Opening),
            ]],
            'unterminated tag is plain text' => ['a<b', [new PlainToken('a<b')]],

            'comment' => ['<!-- c -->', [
                new CommentToken('<!-- c -->'),
            ]],
            'comment holding tags' => ['<!-- <b>x</b> -->', [
                new CommentToken('<!-- <b>x</b> -->'),
            ]],
            'comment spanning multiple lines' => ["<!-- a\nb -->", [
                new CommentToken("<!-- a\nb -->"),
            ]],
            'consecutive comments' => ['<!--a--><!--b-->', [
                new CommentToken('<!--a-->'),
                new CommentToken('<!--b-->'),
            ]],
        ];
    }
}
