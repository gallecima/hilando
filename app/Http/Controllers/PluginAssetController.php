<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Filesystem\Filesystem;

class PluginAssetController extends Controller
{
    public function __construct(protected Filesystem $fs) {}

    public function show(Request $request, string $slug, string $path)
    {
        $pluginsBase = rtrim(config('plugins.path'), DIRECTORY_SEPARATOR);
        $assetsBase  = $pluginsBase . DIRECTORY_SEPARATOR . $slug . DIRECTORY_SEPARATOR . 'assets';

        // Normalizamos la ruta pedida (relativa al /assets)
        $safeRelPath = ltrim($path, '/\\');
        $candidate   = $assetsBase . DIRECTORY_SEPARATOR . $safeRelPath;

        $realAssets = realpath($assetsBase);
        $realFile   = ($candidate && file_exists($candidate)) ? realpath($candidate) : null;

        // Seguridad: archivo debe existir y estar dentro de /assets
        if (!$realAssets || !$realFile || strncmp($realAssets, $realFile, strlen($realAssets)) !== 0) {
            abort(404);
        }

        // Mime básico (imágenes + fallback)
        $ext  = strtolower(pathinfo($realFile, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'svg'  => 'image/svg+xml',
            'webp' => 'image/webp',
            'jpg', 'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'css'  => 'text/css',
            'js'   => 'application/javascript',
            default => mime_content_type($realFile) ?: 'application/octet-stream',
        };

        return response()->file($realFile, [
            'Content-Type'  => $mime,
            'Cache-Control' => 'public, max-age=604800, immutable', // 7 días
        ]);
    }
}