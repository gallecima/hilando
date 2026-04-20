<?php

namespace App\Http\Controllers;

use App\Models\SiteInfo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Plugins\SMTP\Services\PluginMailer;

class NewsletterController extends Controller
{
    public function subscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $site = SiteInfo::query()->first();
        $recipientEmail = $site?->support_email;

        if (!$recipientEmail) {
            return response()->json([
                'ok' => false,
                'message' => 'No hay un email de soporte configurado en la información del sitio.',
            ], 422);
        }

        $subscriberEmail = (string) $data['email'];

        try {
            app(PluginMailer::class)->send(
                $recipientEmail,
                'Suscripcion al newsletter',
                implode('<br>', [
                    'Nueva suscripción al newsletter.',
                    '',
                    'Email: ' . e($subscriberEmail),
                    'Fecha: ' . e(now()->toDateTimeString()),
                    'IP: ' . e((string) $request->ip()),
                ]),
                [],
            );
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'ok' => false,
                'message' => 'No se pudo enviar el email. Intentá nuevamente más tarde.',
            ], 500);
        }

        return response()->json(['ok' => true]);
    }
}
