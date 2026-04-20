<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Plugin;
use App\Models\Shipment;
use Plugins\MercadoPago\Services\MpClient;
use App\Services\CartService;
use Illuminate\Support\Facades\DB;
use Plugins\MercadoPago\Jobs\HandleMpPaymentUpdate;

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
        if ((float) ($order->total ?? 0) <= 0) {
            return redirect()->route('front.checkout.payment')
                ->with('info', 'Este pedido no requiere pago. Podés finalizar la compra directamente.');
        }

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

	        // Registrar/asegurar Payment pending SOLO para ESTA orden
	        $payment = Payment::firstOrCreate(
	            ['order_id' => $order->id, 'payment_method_id' => $pmId],
	            ['method' => 'MercadoPago', 'amount' => $order->total, 'status' => 'pending']
	        );

	        // Persistir override de envío en payment_data para que el webhook (stateless) pueda generar el envío.
	        $shippingOverride = (array) session('shipping.override', []);
	        if (!empty($shippingOverride)) {
	            try {
	                $paymentData = $payment->payment_data;
	                if (!is_array($paymentData)) {
	                    $decoded = is_string($paymentData) ? json_decode($paymentData, true) : null;
	                    $paymentData = is_array($decoded) ? $decoded : [];
	                }
	
	                $pixelioMeta = (array) data_get($paymentData, '_pixelio', []);
	                $pixelioMeta['shipping_override'] = $shippingOverride;
	                $paymentData['_pixelio'] = $pixelioMeta;
	                $payment->update(['payment_data' => $paymentData]);
	
	                Log::info('[MP] shipping.override persistido en payment_data', [
	                    'payment_id' => $payment->id,
	                    'order_id'   => $order->id,
	                    'source'     => $shippingOverride['source'] ?? null,
	                ]);
	            } catch (\Throwable $e) {
	                Log::warning('[MP] No se pudo persistir shipping.override en payment_data', [
	                    'payment_id' => $payment->id,
	                    'order_id'   => $order->id,
	                    'err'        => $e->getMessage(),
	                ]);
	            }
	        }

	        // Config del plugin (solo para webhook; las back_urls las forzamos al return interno)
	        $cfg = (Plugin::where('slug', 'mercadopago')->first()?->config) ?? [];

        $toHttps = function ($pathOrUrl) {
            $val = (string) $pathOrUrl;
            if (preg_match('#^https?://#i', $val)) return preg_replace('#^http://#i', 'https://', $val);
            return secure_url(ltrim($val, '/'));
        };

        $success = $toHttps('/checkout/mercadopago/return?status=success');
        $failure = $toHttps('/checkout/mercadopago/return?status=failure');
        $pending = $toHttps('/checkout/mercadopago/return?status=pending');

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

            return redirect()->away($url);

        } catch (\Throwable $e) {
            Log::error('[MP] error creando preferencia', ['err' => $e->getMessage()]);
            return back()->with('error', 'Ocurrió un error al crear la preferencia de MercadoPago.');
        }
    })->name('mp.pay');

    /**
     * Return desde MP: sincroniza Payment/Order, descuenta stock una vez y limpia sesión si corresponde.
     */
    Route::get('/checkout/mercadopago/return', function (Request $request) {
        $statusParam    = strtolower((string) $request->query('status', ''));
        $paymentIdQS    = $request->query('payment_id') ?? $request->query('collection_id');
        $collectionStat = strtolower((string) $request->query('collection_status', ''));
        $externalQuery  = $request->query('external_reference');

        Log::info('[MP] Return URL', ['query' => $request->query()]);

        $orderId    = null;
        $localPayId = null;

        // 1) Intentamos con external_reference de la query
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

        // 2) Si no hay external_reference, resolvemos vía API con payment_id
        $mpPayment = null;
        if ((!$orderId || !$localPayId) && $paymentIdQS) {
            try {
                $mp = new MpClient();
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
                Log::warning('[MP] Return: no se pudo resolver external_reference desde payment_id', [
                    'payment_id' => $paymentIdQS,
                    'err'        => $e->getMessage(),
                ]);
            }
        }

        if (!$orderId) {
            return redirect()->route('front.checkout.payment')
                ->with('error', 'No pudimos identificar el pedido al volver de MercadoPago.');
        }

        $order        = Order::with('items')->find($orderId);
        $localPayment = $localPayId ? Payment::find($localPayId) : null;

        $statusFromQuery = $statusParam ?: $collectionStat; // approved | in_process | pending | failure/rejected
        $statusMp        = $mpPayment['status'] ?? null;
        $finalMpStatus   = $statusMp ?: $statusFromQuery;

        $newStatus = match ($finalMpStatus) {
            'approved', 'success'        => 'completed',
            'rejected', 'failure'        => 'failed',
            'in_process', 'pending', ''  => 'pending',
            default                      => 'pending',
        };

        $oldStatus = $localPayment?->status;

	        // Actualizar Payment local
	        if ($localPayment) {
	            $update = [
	                'status'         => $newStatus,
	                'transaction_id' => $paymentIdQS ? (string)$paymentIdQS : ($localPayment->transaction_id ?? null),
	            ];
	            if ($mpPayment) {
	                $existingPaymentData = (array) ($localPayment->payment_data ?? []);
	                $pixelioMeta = (array) data_get($existingPaymentData, '_pixelio', []);
	
	                $mergedPaymentData = is_array($mpPayment) ? $mpPayment : [];
	                $mergedPaymentData['_pixelio'] = $pixelioMeta;
	                $update['payment_data'] = $mergedPaymentData;
	            }
	            $localPayment->update($update);

	            HandleMpPaymentUpdate::dispatch($localPayment->id, $oldStatus, $newStatus)->afterResponse();
	        }

        if ($order) {
            if ($newStatus === 'completed') {
                $shouldFinalizeOrder = ($order->status !== 'paid');
                $order->loadMissing('items.product');

	                $isDigitalOrder = $order->items->every(function ($it) {
	                    $product = $it->product;
	                    if (!$product) {
	                        return false;
	                    }

	                    $hasDownloadFile = (bool) ($product->has_downloadable_files ?? false);

	                    return (bool) ($product->is_digital ?? false) || $hasDownloadFile;
	                });

                if ($shouldFinalizeOrder) {
                    foreach ($order->items as $it) {
                        $attrId = $it->attribute_value_id;
                        if ($attrId) {
                            DB::table('attribute_product')
                                ->where('product_id', $it->product_id)
                                ->where('attribute_value_id', $attrId)
                                ->decrement('stock', $it->quantity);
                        } else {
                            \App\Models\Product::where('id', $it->product_id)->decrement('stock', $it->quantity);
                        }
                    }

                    $order->update(['status' => 'paid']);

                    // Solo pedidos físicos necesitan shipment.
                    if (!$isDigitalOrder && $order->shipment_method_id && !$order->shipment) {
                        Shipment::firstOrCreate(
                            ['order_id' => $order->id],
                            [
                                'shipment_method_id' => $order->shipment_method_id,
                                'address'            => $order->shipping_address,
                                'status'             => 'pending',
                            ]
                        );
                    }

                    // Notificar a plugins (EnviaCom, etc.) también en flujo MercadoPago.
                    try {
                        Event::dispatch('checkout.order.finalized', $order);
                        Log::info('[MP] Event checkout.order.finalized dispatched', ['order_id' => $order->id]);
                    } catch (\Throwable $e) {
                        Log::warning('[MP] Error dispatching checkout.order.finalized', [
                            'order_id' => $order->id,
                            'err' => $e->getMessage(),
                        ]);
                    }
                } else {
                    Log::info('[MP] Finalización omitida porque el pedido ya estaba pago', [
                        'order_id' => $order->id,
                    ]);
                }

                /** @var CartService $cart */
                $cart = app(CartService::class);
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

            return redirect()->route('front.checkout.payment')
                ->with('info', 'Pago en proceso. Se actualizará automáticamente cuando se confirme.');
        }

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
        ?? $request->query('topic');

    if (!$paymentId || ($type && $type !== 'payment')) {
        Log::warning('[MP] Webhook sin payment id o type≠payment', compact('paymentId', 'type'));
        return response()->json(['ok' => true]);
    }

    try {
        $mp = new MpClient();
        $mpPayment = $mp->getPayment($paymentId);

        $external = (string) ($mpPayment['external_reference'] ?? '');
        $map = [];
        foreach (explode('|', $external) as $chunk) {
            if (str_contains($chunk, ':')) {
                [$k, $v] = explode(':', $chunk, 2);
                $map[trim($k)] = trim($v);
            }
        }

        $localPayment = null;
        if (!empty($map['payment'])) {
            $localPayment = Payment::find((int) $map['payment']);
        }
        if (!$localPayment && !empty($map['order'])) {
            $localPayment = Payment::where('order_id', (int) $map['order'])
                ->orderByDesc('id')
                ->first();
        }

        if (!$localPayment) {
            Log::warning('[MP] Webhook: no se encontró Payment local', compact('external', 'map'));
            return response()->json(['ok' => true]);
        }

	        $newStatus = match ($mpPayment['status'] ?? null) {
	            'approved'              => 'completed',
	            'rejected'              => 'failed',
	            'in_process', 'pending' => 'pending',
	            default                 => 'pending',
	        };

	        $oldStatus = $localPayment->status;

		        $existingPaymentData = (array) ($localPayment->payment_data ?? []);
		        $pixelioMeta = (array) data_get($existingPaymentData, '_pixelio', []);
	
		        $mergedPaymentData = is_array($mpPayment) ? $mpPayment : [];
		        $mergedPaymentData['_pixelio'] = $pixelioMeta;
	
		        $localPayment->update([
		            'status'         => $newStatus,
		            'transaction_id' => (string) $paymentId,
		            'payment_data'   => $mergedPaymentData,
		        ]);
		        HandleMpPaymentUpdate::dispatch($localPayment->id, $oldStatus, $newStatus)->afterResponse();

	        $order = $localPayment->order()->with('items')->first();
		        if ($order) {
		            if ($newStatus === 'completed') {
		                $shouldFinalizeOrder = ($order->status !== 'paid');
		                $order->loadMissing('items.product');

		                $isDigitalOrder = $order->items->every(function ($it) {
		                    $product = $it->product;
		                    if (!$product) {
		                        return false;
		                    }

		                    $hasDownloadFile = (bool) ($product->has_downloadable_files ?? false);

		                    return (bool) ($product->is_digital ?? false) || $hasDownloadFile;
		                });

		                if ($shouldFinalizeOrder) {
		                    foreach ($order->items as $it) {
		                        $attrId = $it->attribute_value_id;
		                        if ($attrId) {
		                            DB::table('attribute_product')
		                                ->where('product_id', $it->product_id)
		                                ->where('attribute_value_id', $attrId)
		                                ->decrement('stock', $it->quantity);
		                        } else {
		                            \App\Models\Product::where('id', $it->product_id)->decrement('stock', $it->quantity);
		                        }
		                    }

		                    $order->update(['status' => 'paid']);

		                    if (!$isDigitalOrder && $order->shipment_method_id && !$order->shipment) {
		                        Shipment::firstOrCreate(
		                            ['order_id' => $order->id],
		                            [
		                                'shipment_method_id' => $order->shipment_method_id,
		                                'address'            => $order->shipping_address,
		                                'status'             => 'pending',
		                            ]
		                        );
		                    }

		                    try {
		                        Event::dispatch('checkout.order.finalized', $order);
		                        Log::info('[MP] Event checkout.order.finalized dispatched (webhook)', ['order_id' => $order->id]);
		                    } catch (\Throwable $e) {
		                        Log::warning('[MP] Error dispatching checkout.order.finalized (webhook)', [
		                            'order_id' => $order->id,
		                            'err' => $e->getMessage(),
		                        ]);
		                    }
		                } else {
		                    Log::info('[MP] Finalización omitida porque el pedido ya estaba pago (webhook)', [
		                        'order_id' => $order->id,
		                    ]);
		                }
	            } elseif ($newStatus === 'failed') {
		                $order->update(['status' => 'payment_failed']);
		            }
	        }

        Log::info('[MP] Payment actualizado por webhook', [
            'payment_local_id' => $localPayment->id,
            'status'           => $newStatus,
        ]);

    } catch (\Throwable $e) {
        Log::error('[MP] Error procesando webhook', ['err' => $e->getMessage()]);
        return response()->json(['error' => $e->getMessage()], 500);
    }

    return response()->json(['ok' => true]);
})->name('mp.webhook');

// Ping
Route::get('/webhooks/mercadopago/ping', fn() =>
    response()->json(['ok' => true, 'ts' => now()->toIso8601String()])
)->name('mp.webhook.ping');
