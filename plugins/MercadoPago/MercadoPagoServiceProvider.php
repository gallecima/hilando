<?php

namespace Plugins\MercadoPago;

use App\Models\Plugin;
use App\Models\PaymentMethod;
use App\Support\Hooks;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class MercadoPagoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Rutas y vistas del plugin
        $this->loadRoutesFrom(__DIR__ . '/routes/admin.php');
        $this->loadRoutesFrom(__DIR__ . '/routes/front.php');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'mp');
    }

    public function boot(): void
    {
        // Si aún no existe la tabla plugins, salimos
        if (!Schema::hasTable('plugins')) return;

        // Verificamos que el plugin esté instalado y activo
        $row = Plugin::where('slug', 'mercadopago')->first();
        if (!$row || !$row->is_installed || !$row->is_active) return;

        // Aseguramos el método de pago "MercadoPago"
        PaymentMethod::updateOrCreate(
            ['slug' => 'mercadopago'],
            [
                'name'         => 'MercadoPago',
                'type'         => 'plugin', // o lo que uses en tu app
                'active'       => 1,
                'config'       => ['source' => 'plugin.mercadopago'],
                'instructions' => 'Serás redirigido a MercadoPago para completar el pago.',
            ]
        );

        // Agregamos el método de pago al hook del checkout
        app(Hooks::class)->add('front:checkout:payment.methods', function () {
            $orderId = (int) (session('checkout.order_id') ?? 0);
            
            $amount  = (float) (session('checkout.amount') ?? 0.0);

            // Si no hay contexto válido o el pedido quedó en $0, no mostramos el botón.
            if ($orderId <= 0 || $amount <= 0) {
                return '';
            }

            $action = $this->url('mp.pay');
            $token  = $this->csrf();

            $mpLogo = asset('images/mp-logo-horizontal.png');
            return <<<HTML
        <div class="mb-3 mt-4 mt-lg-0 js-mp-payment-block">

            <div class="rounded p-4 p-lg-4 d-lg-flex d-flex justify-content-between flex-column flex-lg-row text-center text-lg-start">
                <div style="width:50%">
                    <img src="{$mpLogo}" alt="" class="me-3" style="width:100%; max-width:200px">
                </div>
                <div style="width:50%">
                        <strong>MercadoPago</strong><br>
                        <small>Pago con tarjeta, débito o transferencia</small>
                        
                                            <form method="POST" action="{$action}" class="mt-4 js-mp-pay-form">
                                            <input type="hidden" name="_token" value="{$token}">
                                            <input type="hidden" name="amount" value="{$amount}">
                                            <button class="btn btn-primary rounded-pill w-100 js-mp-pay-btn" data-loading-text="Redirigiendo a MercadoPago...">
                                                <span class="spinner-border spinner-border-sm me-2 js-btn-spinner d-none" role="status" aria-hidden="true"></span>
                                                <span class="js-btn-label">Pagar</span>
                                            </button>
                                            </form>
                </div>
            </div>        
        </div>


HTML;
        });
    }

    private function csrf(): string
    {
        return csrf_token();
    }

    private function url(string $name): string
    {
        // Asume que la ruta existe; si querés ser ultra defensivo podés chequear Route::has($name)
        return route($name);
    }
}
