<?php

namespace Plugins\HelloWorld;

use App\Models\Plugin;
use App\Support\Hooks;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class HelloWorldServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Carga de rutas y vistas del plugin
        $this->loadRoutesFrom(__DIR__ . '/routes/admin.php');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'helloworld');
    }

    public function boot(): void
    {        

        // Si aún no existe la tabla, abortar silenciosamente
        if (! Schema::hasTable('plugins')) {            
            return;
        }

        // Estado del plugin
        $row = Plugin::where('slug', 'helloworld')->first();        

        if (! $row || ! $row->is_installed || ! $row->is_active) {            
            return;
        }

        // Config
        $cfg     = $row->config ?? [];
        $hookKey = $cfg['hook'] ?? 'front:global:banner';

        // Normalizamos contexts a array de strings
        $contexts = $this->normalizeContexts($cfg['contexts'] ?? ['home']);



        // Registramos el hook
        app(Hooks::class)->add($hookKey, function () use ($cfg, $contexts) {            

            $message = (string) ($cfg['message'] ?? '¡Hola desde HelloWorld!');
            $style   = (string) ($cfg['style']   ?? 'alert alert-info');

            // Si está en ALL, siempre aparece
            if (in_array('all', $contexts, true)) {
                return $this->renderMsg($message, $style);
            }

            // Mapa de contexts -> rutas
            $map = [
                'home'     => ['home'],
                'product'  => ['product.show'],
                'category' => ['category.show'],
                'cart'     => ['cart.index'],
                'checkout' => [
                    'front.checkout.index',
                    'front.checkout.payment',
                    'front.checkout.shipment',
                    'front.checkout.personal_data',
                    'front.checkout.complete',
                ],
            ];

            // Ruta actual (null-safe)
            $rname = optional(\Route::current())->getName();

            foreach ($contexts as $ctx) {
                foreach (($map[$ctx] ?? []) as $rn) {
                    if ($rname === $rn || ($rname && str_starts_with($rname, $rn . '.'))) {
                        return $this->renderMsg($message, $style);
                    }
                }
            }

            return '';
        });
        
    }

    /**
     * Normaliza contexts a array de strings.
     *
     * @param mixed $raw
     * @return array
     */
    private function normalizeContexts(mixed $raw): array
    {
        if (is_array($raw)) {
            return array_values(array_filter(array_map('strval', $raw)));
        }

        if (is_string($raw)) {
            // Intenta JSON (["home","cart"]) y si no, lista separada por comas ("home,cart")
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return array_values(array_filter(array_map('strval', $decoded)));
            }

            $parts = array_filter(array_map('trim', explode(',', $raw)));
            return $parts ?: ['home'];
        }

        return ['home'];
    }

    /**
     * Devuelve el HTML del mensaje estilizado.
     */
    private function renderMsg(string $message, string $style): string
    {
        $html = '<div class="container"><div class="row"><div class="col"><div class="' . e($style) . ' container" style="margin:10px 0;">' . e($message) . '</div></div></div></div>';
        return $html;
    }
}