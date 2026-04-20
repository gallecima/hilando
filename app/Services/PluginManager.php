<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

class PluginManager
{
    public function __construct(
        protected Filesystem $fs
    ) {}

    public function catalog(): array
    {
        $base = config('plugins.path');
        if (!$this->fs->isDirectory($base)) return [];

        $catalog = [];
        foreach ($this->fs->directories($base) as $dir) {
            $json = $dir . DIRECTORY_SEPARATOR . 'plugin.json';
            if (!$this->fs->exists($json)) continue;

            $meta = json_decode($this->fs->get($json), true) ?: [];
            if (empty($meta['slug']) || empty($meta['provider'])) continue;

            $slug       = Str::slug($meta['slug']);
            $assetsDir  = $dir . DIRECTORY_SEPARATOR . 'assets';
            $imageRel   = $this->resolveImageRelativePath($dir, $assetsDir, $meta);

            $catalog[$slug] = [
                'slug'        => $slug,
                'name'        => $meta['name']        ?? $slug,
                'version'     => $meta['version']     ?? '1.0.0',
                'description' => $meta['description'] ?? '',
                'provider'    => $meta['provider'],
                'defaults'    => $meta['defaults']    ?? [],
                'has_admin'   => (bool)($meta['has_admin'] ?? true),
                'admin_route' => $meta['admin_route'] ?? null,
                'path'        => $dir,
                // URL pública a la imagen (si existe)
                'image_url'   => $imageRel ? url('plugin-assets/'.$slug.'/'.ltrim($imageRel, '/')) : null,
            ];
        }

        ksort($catalog);
        return $catalog;
    }

    /**
     * Intenta resolver una imagen "portada" del plugin y devolver el path relativo a /assets.
     * Prioriza:
     * 1) meta["image"] si apunta dentro de /assets
     * 2) assets/img/{logo,cover,banner,icon}.{png,jpg,jpeg,webp,svg}
     * 3) Primer archivo de imagen en assets/img
     */
    protected function resolveImageRelativePath(string $pluginDir, string $assetsDir, array $meta): ?string
    {
        if (!$this->fs->isDirectory($assetsDir)) return null;

        // 1) Si el plugin.json define "image"
        $declared = trim((string)($meta['image'] ?? ''));
        if ($declared !== '') {
            // Si viene relativo a /assets (ej: "img/logo.png")
            $candidate = $assetsDir . DIRECTORY_SEPARATOR . ltrim($declared, '/\\');
            if ($this->fs->exists($candidate)) {
                // relativo a /assets
                return ltrim($declared, '/\\');
            }

            // Si viene absoluto dentro del plugin (ej: "assets/img/logo.png")
            $candidate2 = $pluginDir . DIRECTORY_SEPARATOR . ltrim($declared, '/\\');
            if ($this->fs->exists($candidate2)) {
                $realAssets = realpath($assetsDir);
                $realFile   = realpath($candidate2);
                if ($realAssets && $realFile && strncmp($realAssets, $realFile, strlen($realAssets)) === 0) {
                    // convertir a path relativo a /assets
                    return ltrim(str_replace($realAssets . DIRECTORY_SEPARATOR, '', $realFile), '/\\');
                }
            }
        }

        // 2) Candidatos típicos
        $imgDir = $assetsDir . DIRECTORY_SEPARATOR . 'img';
        $names  = ['logo','cover','banner','icon'];
        $exts   = ['png','jpg','jpeg','webp','svg'];

        foreach ($names as $n) {
            foreach ($exts as $e) {
                $c = $imgDir . DIRECTORY_SEPARATOR . $n . '.' . $e;
                if ($this->fs->exists($c)) {
                    return 'img/' . $n . '.' . $e; // relativo a /assets
                }
            }
        }

        // 3) Primer imagen cualquiera dentro de assets/img
        if ($this->fs->isDirectory($imgDir)) {
            foreach ($this->fs->files($imgDir) as $f) {
                $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                if (in_array($ext, $exts, true)) {
                    return 'img/' . basename($f);
                }
            }
        }

        return null;
    }
}