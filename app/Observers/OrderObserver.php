<?php

namespace App\Observers;

use App\Models\Order;

class OrderObserver
{
    public function updated(Order $order): void
    {
        // 🔹 Mantener tu lógica actual
        $order->updateStatus();

        // 🔹 Detectar cambios y registrar actividad
        $changes  = $order->getChanges();
        $original = $order->getOriginal();

        unset($changes['updated_at']); // no nos interesa
        if (empty($changes)) {
            return;
        }

        // Determinar tipo de evento
        $type = 'pedido_actualizado';
        if (array_key_exists('status', $changes)) {
            switch ($changes['status']) {
                case 'cancelled':
                    $type = 'pedido_cancelado';
                    break;
                case 'paid':
                    $type = 'pedido_pagado';
                    break;
                case 'shipped':
                    $type = 'pedido_enviado';
                    break;
                case 'delivered':
                    $type = 'pedido_entregado';
                    break;
            }
        }

        // Armar diff de cambios relevantes
        $diff = [];
        foreach ($changes as $field => $newValue) {
            $diff[$field] = [
                'old' => $original[$field] ?? null,
                'new' => $newValue,
            ];
        }

        // Registrar actividad comercial
        activity()->log([
            'category'     => 'comerciales',
            'type'         => $type,
            'description'  => "Pedido #{$order->id} actualizado ({$order->status})",
            'subject_type' => Order::class,
            'subject_id'   => $order->id,
            'meta'         => ['diff' => $diff],
        ]);
    }

    public function created(Order $order): void
    {
        // Nuevo pedido
        activity()->log([
            'category'     => 'comerciales',
            'type'         => 'pedido_creado',
            'description'  => "Se creó el pedido #{$order->id} para el cliente {$order->name}",
            'subject_type' => Order::class,
            'subject_id'   => $order->id,
            'meta'         => [
                'total'  => $order->total,
                'status' => $order->status,
                'email'  => $order->email,
            ],
        ]);
    }

    public function deleted(Order $order): void
    {
        activity()->log([
            'category'     => 'comerciales',
            'type'         => 'pedido_eliminado',
            'description'  => "Se eliminó el pedido #{$order->id} ({$order->name})",
            'subject_type' => Order::class,
            'subject_id'   => $order->id,
        ]);
    }
}
