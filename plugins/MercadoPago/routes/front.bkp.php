<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Plugin;
use App\Models\Shipment;

use Plugins\MercadoPago\Services\MpClient;
use App\Services\CartService;

// ------------------------
// Grupo web: iniciar pago y return (usa sesión)
// ------------------------
Route::middleware(['web'])->group(function () {

    /**
     * Inicia pago con MercadoPago usando SIEMPRE la orden de sesión.
     */
    Route::post('/checkout/pay/mercadopago', function (Request $request) {
        $orderId = (int) session('checkout.order_id');
        if (!$orderId) {
            return back()->with('error', 'No hay pedido en curso.');
        }

        $order = Order::findOrFail($orderId);

        // Asegurar PaymentMethod “MercadoPago”
        $pmId = PaymentMethod::where(function ($q) {
            $q->where('slug', 'mercadopago')
              ->orWhere('name', 'LIKE', '%MercadoPago%');
        })->value('id');

        if (!$pmId) {
            $pm = PaymentMethod::updateOrCreate(
                ['slug' => 'mercadopago'],
                [
                    'name'         => 'MercadoPago',
                    'type'         => 'plugin',
                    'active'       => 1,
                    'config'       => ['source' => 'plugin.mercadopago'],
                    'instructions' => 'Serás redirigido a MercadoPago para completar el pago.',
                ]
            );
            $pmId = $pm->id;
        }

        // Registrar/asegurar Payment pending SOLO para ESTA orden + método
        $payment = Payment::firstOrCreate(
            ['order_id' => $order->id, 'payment_method_id' => $pmId],
            ['method' => 'MercadoPago', 'amount' => $order->total, 'status' => 'pending']
        );

        // Config del plugin (webhook); las back_urls las forzamos al return interno
        $cfg = (Plugin::where('slug', 'mercadopago')->first()?->config) ?? [];

        $toHttps = function ($pathOrUrl) {
            $val = (string) $pathOrUrl;
            if (preg_match('#^https?://#i', $val)) return preg_replace('#^http://#i', 'https://', $val);
            return secure_url(ltrim($val, '/'));
        };

        $success = $toHttps('/checkout/mercadopago/return?status=success');
        $failure = $toHttps('/checkout/mercadopago/return?status=failure');
        $pending = $toHttps('/checkout/mercadopago/return?status=pending');

        // Webhook absoluto https
        $webhook = trim((string)($cfg['webhook_url'] ?? ''));
        if ($webhook === '') $webhook = secure_url('webhooks/mercadopago');

        try {
            $mp = new MpClient();

            $pref = $mp->createPreference([
                'amount'             => (float) $order->total,
                'success'            => $success,
                'failure'            => $failure,
                'pending'            => $pending,
                'webhook'            => $webhook,
                'external_reference' => 'order:' . $order->id . '|payment:' . $payment->id,
                'items'              => [[
                    'title'       => 'Pedido #' . $order->id,
                    'quantity'    => 1,
                    'unit_price'  => round((float) $order->total, 2),
                    'currency_id' => 'ARS',
                ]],
            ]);

            Log::debug('[MP] preferencia creada', $pref);

            $url = $pref['init_point'] ?? $pref['sandbox_init_point'] ?? null;
            if (!$url) {
                return back()->with('error', 'No se pudo obtener la URL de pago de MercadoPago.');
            }

            // Importante: evitamos tocar la orden acá (ya está pending); MP/return/webhook la completan
            return redirect()->away($url);

        } catch (\Throwable $e) {
            Log::error('[MP] error creando preferencia', ['err' => $e->getMessage()]);
            return back()->with('error', 'Ocurrió un error al crear la preferencia de MercadoPago.');
        }
    })->name('mp.pay');

/**
 * Return desde MP: sincroniza Payment/Order y limpia sesión si corresponde.
 */
Route::get('/checkout/mercadopago/return', function (Request $request) {
    $statusParam    = strtolower((string) $request->query('status', ''));
    $paymentIdQS    = $request->query('payment_id') ?? $request->query('collection_id');
    $collectionStat = strtolower((string) $request->query('collection_status', ''));
    $externalQuery  = $request->query('external_reference');

    Log::info('[MP] Return URL', ['query' => $request->query()]);

    $orderId    = null;
    $localPayId = null;

    // 1) Parseo directo del external_reference si vino en la URL
    if (!empty($externalQuery) && $externalQuery !== 'null') {
        foreach (explode('|', (string)$externalQuery) as $chunk) {
            if (str_contains($chunk, ':')) {
                [$k, $v] = explode(':', $chunk, 2);
                $k = trim($k); $v = trim($v);
                if ($k === 'order')   $orderId    = (int)$v;
                if ($k === 'payment') $localPayId = (int)$v;
            }
        }
    }

    $mpPayment = null;

    // 2) Si faltan IDs, intentamos resolver con la API usando payment_id
    if ((!$orderId || !$localPayId) && $paymentIdQS) {
        try {
            $mp = new \Plugins\MercadoPago\Services\MpClient();
            $mpPayment = $mp->getPayment($paymentIdQS);

            $ext = (string)($mpPayment['external_reference'] ?? '');
            if ($ext) {
                foreach (explode('|', $ext) as $chunk) {
                    if (str_contains($chunk, ':')) {
                        [$k, $v] = explode(':', $chunk, 2);
                        $k = trim($k); $v = trim($v);
                        if ($k === 'order')   $orderId    = (int)$v;
                        if ($k === 'payment') $localPayId = (int)$v;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('[MP] Return: fallo getPayment()', ['payment_id' => $paymentIdQS, 'err' => $e->getMessage()]);
        }
    }

    if (!$orderId) {
        return redirect()->route('front.checkout.payment')
            ->with('error', 'No pudimos identificar el pedido al volver de MercadoPago.');
    }

    $order        = \App\Models\Order::find($orderId);
    $localPayment = $localPayId ? \App\Models\Payment::find($localPayId) : null;

    // 3) Determinar estado final por la query (y usamos MP API si la tenemos)
    //    - Preferimos el status de MP API cuando está disponible; si no, usamos la query.
    $statusFromQuery = $statusParam ?: $collectionStat; // approved | in_process | pending | failure/rejected
    $statusMp        = $mpPayment['status'] ?? null;

    $finalMpStatus = $statusMp ?: $statusFromQuery;

    $newStatus = match ($finalMpStatus) {
        'approved', 'success'        => 'completed',
        'rejected', 'failure'        => 'failed',
        'in_process', 'pending', ''  => 'pending',
        default                      => 'pending',
    };

    // 4) Actualizar Payment local si lo tenemos identificado
    if ($localPayment) {
        $update = [
            'status'         => $newStatus,
            'transaction_id' => $paymentIdQS ? (string)$paymentIdQS : ($localPayment->transaction_id ?? null),
        ];
        if ($mpPayment) {
            $update['payment_data'] = $mpPayment;
        }
        $localPayment->update($update);
    }

    // 5) Actualizar Order en base al outcome
    if ($order) {
        if ($newStatus === 'completed') {
            $order->update(['status' => 'paid']);

            // Asegurar Shipment (para que thankyou muestre datos)
            if (!$order->shipment) {
                \App\Models\Shipment::firstOrCreate(
                    ['order_id' => $order->id],
                    [
                        'shipment_method_id' => $order->shipment_method_id,
                        'address'            => $order->shipping_address,
                        'status'             => 'pending',
                    ]
                );
            }

            /** @var \App\Services\CartService $cart */
            $cart = app(\App\Services\CartService::class);
            $cart->clear();

            session()->forget([
                'checkout.shipment_method_id',
                'payment_method_id',
                'guest_checkout',
                'discount_coupon',
                'checkout.order_id',
                'checkout.amount',
                'checkout.cart_sig',
            ]);
            session(['last_order_id' => $order->id]);

            return redirect()->route('front.checkout.complete')
                ->with('success', '¡Pago aprobado! Gracias por tu compra.');
        }

        if (in_array($newStatus, ['failed'], true)) {
            $order->update(['status' => 'payment_failed']);
            return redirect()->route('front.checkout.payment')
                ->with('error', 'El pago fue rechazado o cancelado.');
        }

        // pending / in_process
        // Si te interesa, podés dejar la orden en "pending" (ya lo está) y solo avisar:
        return redirect()->route('front.checkout.payment')
            ->with('info', 'Pago en proceso. Se actualizará automáticamente cuando se confirme.');
    }

    // Fallback si no encontramos la orden (raro a esta altura)
    return redirect()->route('front.checkout.payment')
        ->with('info', 'Estamos procesando el pago. Si ya se aprobó, en breve se reflejará.');
})->name('mp.return');

}); // fin grupo web

// ------------------------
// Webhook sin estado (stateless)
// ------------------------
Route::post('/webhooks/mercadopago', function (Request $request) {
    Log::info('[MP] Webhook recibido', [
        'query' => $request->query(),
        'body'  => $request->all()
    ]);

    $paymentId = $request->input('data.id')
        ?? $request->input('id')
        ?? $request->query('id');

    $type = $request->input('type')
        ?? $request->query('type')
        ?? $request->query('topic')
        ?? $request->input('action'); // ej: payment.created

    // Aceptamos payment y merchant_order (MP puede enviar ambos)
    $accepted = ['payment', 'merchant_order', 'payment.created', 'payment.updated'];
    if ($type && !in_array($type, $accepted, true)) {
        Log::warning('[MP] Webhook type no soportado', compact('type'));
        return response()->json(['ok' => true]);
    }

    if (!$paymentId) {
        Log::warning('[MP] Webhook sin payment id', compact('type', 'paymentId'));
        return response()->json(['ok' => true]);
    }

    try {
        $mp = new \Plugins\MercadoPago\Services\MpClient();
        $mpPayment = $mp->getPayment($paymentId);

        Log::debug('[MP] Webhook MP Payment', $mpPayment);

        $external = (string) ($mpPayment['external_reference'] ?? '');
        $map = [];
        foreach (explode('|', $external) as $chunk) {
            if (str_contains($chunk, ':')) {
                [$k, $v] = explode(':', $chunk, 2);
                $map[trim($k)] = trim($v);
            }
        }

        if (empty($external)) {
            Log::warning('[MP] Webhook: external_reference vacío o null', [
                'paymentId' => $paymentId,
                'mpStatus'  => $mpPayment['status'] ?? null,
            ]);
        }

        $localPayment = null;
        if (!empty($map['payment'])) {
            $localPayment = \App\Models\Payment::find((int) $map['payment']);
            if (!$localPayment) {
                Log::warning('[MP] Webhook: payment id en external_reference no existe localmente', $map);
            }
        }
        if (!$localPayment && !empty($map['order'])) {
            $localPayment = \App\Models\Payment::where('order_id', (int) $map['order'])
                ->orderByDesc('id')
                ->first();
            if (!$localPayment) {
                Log::warning('[MP] Webhook: no hallé payment por order_id', $map);
            }
        }

        if (!$localPayment) {
            return response()->json(['ok' => true]);
        }

        $newStatus = match ($mpPayment['status'] ?? null) {
            'approved'              => 'completed',
            'rejected'              => 'failed',
            'in_process', 'pending' => 'pending',
            default                 => 'pending',
        };

        $localPayment->update([
            'status'         => $newStatus,
            'transaction_id' => (string) $paymentId,
            'payment_data'   => $mpPayment,
        ]);

        $order = $localPayment->order;
        if ($order) {
            if ($newStatus === 'completed') {
                $order->update(['status' => 'paid']);

                // Asegurar shipment
                if (!$order->shipment) {
                    \App\Models\Shipment::firstOrCreate(
                        ['order_id' => $order->id],
                        [
                            'shipment_method_id' => $order->shipment_method_id,
                            'address'            => $order->shipping_address,
                            'status'             => 'pending',
                        ]
                    );
                }
            } elseif ($newStatus === 'failed') {
                $order->update(['status' => 'payment_failed']);
            }
        }

        Log::info('[MP] Payment actualizado por webhook', [
            'payment_local_id' => $localPayment->id,
            'status'           => $newStatus,
            'order_id'         => $order?->id
        ]);

    } catch (\Throwable $e) {
        Log::error('[MP] Error procesando webhook', ['err' => $e->getMessage()]);
        return response()->json(['error' => $e->getMessage()], 500);
    }

    return response()->json(['ok' => true]);
})->name('mp.webhook');

/**
 * Helper local para actualizar Payment/Order/Shipment desde un objeto pago de MP normalizado por MpClient.
 */
if (!function_exists('processLocalPaymentFromMp')) {
    function processLocalPaymentFromMp(array $mpPayment, string $mpPaymentId): void
    {
        $external = (string)($mpPayment['external_reference'] ?? '');
        $map = [];
        foreach (explode('|', $external) as $chunk) {
            if (str_contains($chunk, ':')) {
                [$k, $v] = explode(':', $chunk, 2);
                $map[trim($k)] = trim($v);
            }
        }

        $localPayment = null;
        if (!empty($map['payment'])) {
            $localPayment = \App\Models\Payment::find((int)$map['payment']);
        }
        if (!$localPayment && !empty($map['order'])) {
            $localPayment = \App\Models\Payment::where('order_id', (int)$map['order'])
                ->orderByDesc('id')
                ->first();
        }
        if (!$localPayment) {
            Log::warning('[MP] Webhook: no se encontró Payment local', compact('external', 'map'));
            return;
        }

        $newStatus = match ($mpPayment['status'] ?? null) {
            'approved'              => 'completed',
            'rejected'              => 'failed',
            'in_process', 'pending' => 'pending',
            default                 => 'pending',
        };

        $localPayment->update([
            'status'         => $newStatus,
            'transaction_id' => (string)$mpPaymentId,
            'payment_data'   => $mpPayment,
        ]);

        $order = $localPayment->order;
        if ($order) {
            if ($newStatus === 'completed') {
                $order->update(['status' => 'paid']);

                // Asegurar Shipment para thankyou
                if (!$order->shipment) {
                    \App\Models\Shipment::firstOrCreate(
                        ['order_id' => $order->id],
                        [
                            'shipment_method_id' => $order->shipment_method_id,
                            'address'            => $order->shipping_address,
                            'status'             => 'pending',
                        ]
                    );
                }
            } elseif ($newStatus === 'failed') {
                $order->update(['status' => 'payment_failed']);
            }
        }

        Log::info('[MP] Payment actualizado por webhook', [
            'payment_local_id' => $localPayment->id,
            'status'           => $newStatus,
        ]);
    }
}

// Ping (diagnóstico)
Route::get('/webhooks/mercadopago/ping', fn() =>
    response()->json(['ok' => true, 'ts' => now()->toIso8601String()])
)->name('mp.webhook.ping');