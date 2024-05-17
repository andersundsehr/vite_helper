<?php

namespace AUS\ViteHelper\Tests\Unit;

use Generator;
use AUS\ViteHelper\ViteUtility;
use TYPO3\CMS\Core\Core\ApplicationContext;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class ViteUtilityTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Environment::initialize(
            new ApplicationContext('Testing'),
            true,
            true,
            '/app',
            __DIR__ . '/fixtures',
            '/app/var',
            '/app/config',
            '/app/public/index.php',
            'UNIX'
        );
    }

    /**
     * @test
     * @covers       \AUS\ViteHelper\ViteUtility
     */
    public function exceptionUnsupported(): void
    {
        $utility = new ViteUtility();
        putenv('NODE_ACTIVE=FALSE');
        $this->expectExceptionMessage('type not defined unsupported.');
        $utility->headTag('', ['unsupported.' => ['src/typescript/index.ts']]);
    }

    /**
     * @test
     * @covers       \AUS\ViteHelper\ViteUtility
     */
    public function exceptionFileNotFoundInManifest(): void
    {
        $utility = new ViteUtility();
        putenv('NODE_ACTIVE=FALSE');
        $this->expectExceptionMessage('configured js. src/typescript/fileNotFoundInManifest.ts not found in manifest.json');
        $utility->headTag('', ['js.' => ['src/typescript/fileNotFoundInManifest.ts']]);
    }

    /**
     * @test
     * @covers       \AUS\ViteHelper\ViteUtility
     * @dataProvider headTagProvider
     *
     * @param array<string, string[]> $config
     */
    public function headTag(string $expected, array $config, bool $nodeActive, string $nodeDomain): void
    {
        $utility = new ViteUtility();
        putenv('NODE_ACTIVE=' . ($nodeActive ? 'TRUE' : 'FALSE'));
        putenv('NODE_DOMAIN=' . $nodeDomain);
        $result = $utility->headTag('<head>', $config);
        self::assertSame(explode("\n", $expected), explode("\n", $result));
    }

    public static function headTagProvider(): Generator
    {
        yield [
            'expected' => '<head>',
            'config' => [

            ],
            'nodeActive' => false,
            'nodeDomain' => 'https://node.test.vm23.iveins.de',
        ];
        yield [
            'expected' => <<<'EOF'
<head>
<script type="module" src="https://node.test.vm23.iveins.de/@vite/client"></script>
<script type="module" src="https://node.test.vm23.iveins.de/@vite-plugin-checker-runtime-entry"></script>
EOF,
            'config' => [

            ],
            'nodeActive' => true,
            'nodeDomain' => 'https://node.test.vm23.iveins.de',
        ];

        yield [
            'expected' => <<<'EOF'
<head>
<link rel="preload" href="/assets/icomoon-04f9b5bd.ttf" as="font" crossorigin />
<link rel="preload" href="/assets/lato-v23-latin-700-c447dd76.woff2" as="font" crossorigin />
<link rel="stylesheet" href="/assets/index-efe754a5.css" media="all">
<script defer type="module" src="/assets/index-51605102.js"></script>
EOF,
            'config' => [
                'fontPreLoad.' => [
                    'src/fonts/Iconfont/icomoon.ttf',
                    'src/fonts/lato-v23-latin-700.woff2',
                ],
                'css.' => [
                    'src/typescript/index.css',
                ],
                'js.' => [
                    '/src/typescript/index.ts',
                ],
            ],
            'nodeActive' => false,
            'nodeDomain' => 'https://node.test.vm23.iveins.de',
        ];
        yield [
            'expected' => <<<'EOF'
<head>
<script type="module" src="https://node.test.vm23.iveins.de/@vite/client"></script>
<script type="module" src="https://node.test.vm23.iveins.de/src/typescript/index.ts"></script>
<script type="module" src="https://node.test.vm23.iveins.de/@vite-plugin-checker-runtime-entry"></script>
EOF,
            'config' => [
                'fontPreLoad.' => [
                    'src/fonts/Iconfont/icomoon.ttf',
                    'src/fonts/lato-v23-latin-700.woff2',
                ],
                'css.' => [
                    'src/typescript/index.css',
                ],
                'js.' => [
                    '/src/typescript/index.ts',
                ],
            ],
            'nodeActive' => true,
            'nodeDomain' => 'https://node.test.vm23.iveins.de',
        ];
    }
}
