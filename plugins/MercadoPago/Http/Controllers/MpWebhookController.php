<?php

namespace Plugins\MercadoPago\Http\Controllers;

use App\Models\Plugin;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class MpWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Opcional: validar firma con webhook_secret si la configurás
        $p = Plugin::where('slug','mercadopago')->first();
        $cfg = $p?->config ?? [];
        $secret = $cfg['webhook_secret'] ?? null;

        // TODO: si tenés secret propio, validalo acá (cabeceras MP + firma)
        Log::info('[MP] Webhook recibido', ['payload' => $request->all()]);

        // Acá deberías:
        // - Buscar el pago preferencia/collection en MP con la API,
        // - Actualizar tu Payment/Order según estado (approved/pending/rejected).

        return response()->json(['ok' => true]);
    }
}