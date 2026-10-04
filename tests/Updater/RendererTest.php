<?php

declare(strict_types=1);

namespace VStelmakh\UrlHighlight\Tests\Updater;

use PHPUnit\Framework\TestCase;
use VStelmakh\UrlHighlight\Updater\Domain;
use VStelmakh\UrlHighlight\Updater\DomainList;
use VStelmakh\UrlHighlight\Updater\Renderer;

class RendererTest extends TestCase
{
    public function testRender(): void
    {
        $renderer = new Renderer();
        $domainList = new DomainList(2026100400, new \DateTimeImmutable('2026-10-04 07:07:01 UTC'), [
            new Domain('com'),
            new Domain('net'),
            new Domain('укр'),
            new Domain('中国'),
        ]);

        $actual = $renderer->render($domainList, 'https://example.com/tlds.txt');

        $expected = <<<'PHP'
            <?php

            /**
             * List of valid top-level domains provided by IANA.
             *
             * @see https://example.com/tlds.txt
             *
             * @internal
             */

            declare(strict_types=1);

            return [
                'com' => true,
                'net' => true,
                'укр' => true,
                '中国' => true,
            ];

            PHP;

        self::assertSame($expected, $actual);
    }
}
