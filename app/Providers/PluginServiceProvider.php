<?php

namespace App\Providers;

use App\Models\Plugin;
use Illuminate\Support\ServiceProvider;

class PluginServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Registrar lógica relacionada a vistas solo DESPUÉS de que 'view' exista
        $this->app->afterResolving('view', function ($factory, $app) {
            // $factory es Illuminate\Contracts\View\Factory
            // Compartí plugins activos agrupados por posición a TODAS las vistas
            $plugins = Plugin::query()->where('is_active', true)->get();

            $byPos = [];
            foreach ($plugins as $p) {
                $pos = $p->config['position'] ?? 'home_top';
                $byPos[$pos][] = $p;
            }

            $factory->composer('*', function ($view) use ($byPos) {
                $view->with('hookPlugins', $byPos);
            });
        });
    }
}