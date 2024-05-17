<?php

declare(strict_types=1);

namespace AUS\ViteHelper;

use Exception;
use TYPO3\CMS\Core\Core\Environment;

final class ViteUtility
{
    /**
     * @param array{ 'js.'?: string[], 'css.'?: string[], 'fontPreLoad.'?: string[] } $conf
     */
    public function headTag(string $content, array $conf): string
    {
        if (getenv('NODE_ACTIVE') === 'TRUE') {
            $lines = [$content, ...$this->developmentVite($conf)];
        } else {
            $lines = [$content, ...$this->productionVite($conf)];
        }

        return implode("\n", $lines);
    }

    /**
     * @param array<string, string[]> $conf
     * @return array<string>
     */
    private function productionVite(array $conf): array
    {
        $path = Environment::getPublicPath() . '/assets/manifest.json';
        $content = file_get_contents($path);
        if (!$content) {
            throw new Exception(sprintf('vite manifest is necessary, tried to find it here: %s', $path));
        }

        $data = json_decode(json: $content, associative: true, flags: JSON_THROW_ON_ERROR);

        $lines = [];
        foreach ($conf as $type => $files) {
            foreach ($files as $fileName) {
                $fileName = ltrim($fileName, '/');
                if (!isset($data[$fileName])) {
                    throw new Exception(sprintf('configured %s %s not found in manifest.json', $type, $fileName));
                }

                $fileName = '/' . $data[$fileName]['file'];
                $lines[] = match ($type) {
                    'js.' => sprintf('<script defer type="module" src="%s"></script>', $fileName),
                    'css.' => sprintf('<link rel="stylesheet" href="%s" media="all">', $fileName),
                    'fontPreLoad.' => sprintf('<link rel="preload" href="%s" as="font" crossorigin />', $fileName),
                    default => throw new Exception(sprintf('type not defined %s', $type))
                };
            }
        }

        return $lines;
    }

    /**
     * @param array{ 'js.'?: string[], 'css.'?: string[], 'fontPreLoad.'?: string[] } $conf
     * @return array<string>
     */
    private function developmentVite(array $conf): array
    {
        $lines = [];
        $jsFiles = [
            '@vite/client',
            ...($conf['js.'] ?? []),
            ...($conf['css.'] ?? []),
            '@vite-plugin-checker-runtime-entry'
        ];
        foreach ($jsFiles as $jsFile) {
            $url = rtrim(getenv('NODE_DOMAIN') ?: '', '/') . '/' . ltrim($jsFile, '/');
            $lines[] = '<script type="module" src="' . $url . '"></script>';
        }

        return $lines;
    }
}
