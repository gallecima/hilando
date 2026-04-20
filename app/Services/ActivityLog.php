<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Support\Facades\Auth;

class ActivityLog
{
    /**
     * Registra una actividad en el sistema.
     *
     * Campos esperados en $data:
     * - category (string): plataforma | administrativos | comerciales
     * - description (string)
     * - type (string|null): alta | baja | modificacion | venta | emision_factura | envio | ...
     * - occurred_at (datetime|string|null)
     * - user_id (int|null)  // SOLO para users (admins). Si no se pasa, se infiere del guard 'web'
     * - subject_type (class-string|null)
     * - subject_id (int|null)
     * - meta (array|null)
     */
    public function log(array $data): Activity
    {
        // Detectar actor según guard
        $admin    = Auth::guard('web')->user();        // App\Models\User (backend)
        $customer = Auth::guard('customer')->user();   // App\Models\Customer (frontend)

        // user_id solo si es un USER (admin/staff).
        // Prioriza $data['user_id'] si fue proporcionado explícitamente.
        $resolvedUserId = array_key_exists('user_id', $data)
            ? $data['user_id']
            : ($admin?->id); // si no hay admin logueado, queda null

        // Construir meta base + actor
        $meta = $data['meta'] ?? [];
        if (!isset($meta['actor'])) {
            if ($admin) {
                $meta['actor'] = [
                    'type' => 'user',
                    'user_id' => $admin->id,
                    'email'   => $admin->email ?? null,
                    'name'    => $admin->name  ?? null,
                ];
            } elseif ($customer) {
                $meta['actor'] = [
                    'type'        => 'customer',
                    'customer_id' => $customer->id,
                    'email'       => $customer->email ?? null,
                    'name'        => $customer->name  ?? null,
                ];
            }
        }

        return Activity::create([
            'occurred_at'  => $data['occurred_at'] ?? now(),
            'user_id'      => $resolvedUserId,       // ⚠️ NUNCA el id de customer acá
            'category'     => $data['category'],
            'type'         => $data['type'] ?? null,
            'description'  => $data['description'],
            'subject_type' => $data['subject_type'] ?? null,
            'subject_id'   => $data['subject_id'] ?? null,
            'meta'         => $meta ?: null,
            'ip'           => request()?->ip(),
            'user_agent'   => request()?->userAgent(),
        ]);
    }
}