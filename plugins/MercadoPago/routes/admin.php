<?php

use App\Models\Plugin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Plugins\MercadoPago\Services\MpClient;

Route::middleware(['web','auth'])
    ->prefix('admin/plugins/mercadopago')
    ->name('admin.plugins.mercadopago.')
    ->group(function () {

        Route::get('/', function () {
            $plugin = Plugin::where('slug','mercadopago')->firstOrFail();
            return view('mp::settings', compact('plugin'));
        })->name('edit');

        Route::post('/', function (Request $request) {
            $plugin = Plugin::where('slug','mercadopago')->firstOrFail();

            $data = $request->validate([
                'mode'          => 'required|in:test,live',
                'public_key'    => 'required|string',
                'access_token'  => 'required|string',
                'integrator_id' => 'nullable|string',
                'webhook_url'   => 'nullable|url',
                'webhook_secret'=> 'nullable|string',
                'success_url'   => 'nullable|string',
                'failure_url'   => 'nullable|string',
                'pending_url'   => 'nullable|string',
            ]);

            $plugin->update([
                'config' => array_merge($plugin->config ?? [], $data),
            ]);

            return back()->with('success','Configuración guardada.');
        })->name('update');

        Route::post('/test', function () {
            try {
                $toHttps = function ($pathOrUrl) {
                    $u = (string)$pathOrUrl;
                    if (preg_match('#^https?://#i', $u)) {
                        return preg_replace('#^http://#i', 'https://', $u);
                    }
                    // si es ruta relativa, generamos https absoluto
                    return secure_url(ltrim($u, '/'));
                };                
                $success = $toHttps($pluginCfg['success_url'] ?? '/checkout/complete?status=success');
                $failure = $toHttps($pluginCfg['failure_url'] ?? '/checkout/complete?status=failure');
                $pending = $toHttps($pluginCfg['pending_url'] ?? '/checkout/complete?status=pending');  
                $webhook = trim((string)($pluginCfg['webhook_url'] ?? ''));
                if ($webhook === '') {
                    $webhook = route('mp.webhook'); // tu ruta interna
                }
                $webhook = $toHttps($webhook);                              
                $mp = new \Plugins\MercadoPago\Services\MpClient();
                $pref = $mp->createPreference([
                    'amount'  => 123.45,
                    'success' => $success,
                    'failure' => $failure,
                    'pending' => $pending,
                    'webhook' => $webhook,
                ]);

                return back()->with('success', 'Preferencia creada: '.$pref['id']);
            } catch (\RuntimeException $e) {
                return back()->with('error', $e->getMessage());
            }
        })->name('test');
    });