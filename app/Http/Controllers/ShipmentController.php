<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Services\EmailTemplateSender;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    public function index()
    {
        $shipments = Shipment::with('order')->paginate(20);
        return view('shipments.index', compact('shipments'));
    }

    public function create()
    {
        return view('shipments.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'tracking_number' => 'nullable|string',
            'carrier' => 'nullable|string',
            'status' => 'required|in:pending,shipped,delivered,ready_for_pickup',
            'shipped_at' => 'nullable|date',
            'delivered_at' => 'nullable|date',
        ]);


        $shipment = Shipment::create($data);
        $shipment->order->updateStatus();

        return redirect()->route('shipments.index')->with('success', 'Envío registrado.');
    }

    public function edit(Shipment $shipment)
    {
        return view('shipments.edit', compact('shipment'));
    }

    public function update(Request $request, Shipment $shipment)
    {
        $data = $request->validate([
            'tracking_number' => 'nullable|string',
            'carrier' => 'nullable|string',
            'status' => 'required|in:pending,shipped,delivered,ready_for_pickup',
            'shipped_at' => 'nullable|date',
            'delivered_at' => 'nullable|date',
        ]);

        // Valores previos (antes de mutar)
        $oldStatus   = $shipment->getOriginal('status');
        $oldTracking = $shipment->getOriginal('tracking_number');


        $shipment->update($data);
        $shipment->order->updateStatus();

        // Detectar cambios relevantes
        $changedStatus   = $oldStatus   !== $shipment->status;
        $changedTracking = $oldTracking !== $shipment->tracking_number;

        if ($changedStatus || $changedTracking) {
            $order = $shipment->order; // relación Shipment -> Order

            // Si usás Envia.com, intentamos trackUrl del JSON guardado
            $trackUrl = data_get($shipment->shipping_data_json, 'enviacom.generate.parsed.trackUrl', '');

            /*
            app(EmailTemplateSender::class)->send(
                'shipment_status_updated',
                $order,
                [
                    '%old_status%'      => ucfirst((string)($oldStatus ?? '-')),
                    '%new_status%'      => ucfirst((string)($shipment->status ?? '-')),
                    '%tracking_number%' => (string)($shipment->tracking_number ?? ''),
                    '%tracking_url%'    => (string)$trackUrl,
                ]
            );
            */



            /** @var \App\Services\EmailTemplateSender $sender */
            $sender = app(\App\Services\EmailTemplateSender::class);

            $isPickup = static function ($order, $shipment): bool {
                // Ajustá estas heurísticas a tu modelo/datos
                if (isset($order->shipping_method) && str_contains(strtolower((string)$order->shipping_method), 'retiro')) return true;
                $mode = data_get($shipment->shipping_data_json, 'mode') ?: data_get($order->shipping_data_json ?? [], 'mode');
                if ($mode && in_array(strtolower($mode), ['pickup','retiro','local'], true)) return true;
                return (bool) ($order->is_pickup ?? false);
            };

            // Estado de “listo para retirar” (ajustá a tus valores reales)
            $isReadyForPickupStatus = in_array(strtolower((string)$shipment->status), ['ready_for_pickup','ready','disponible'], true);

            $templateKey = ($isReadyForPickupStatus && $isPickup($order, $shipment))
                ? 'order_ready_for_pickup'
                : 'shipment_status_updated';


            $statusTranslations = [
                'pending'           => 'Pendiente',
                'shipped'           => 'Enviado',
                'delivered'         => 'Entregado',
                'ready_for_pickup'  => 'Listo para retirar',
            ];

            $translatedStatus = $statusTranslations[$shipment->status]
                ?? ucfirst($shipment->status);

            $sender->send(
                $templateKey,
                $order,
                [
                    '%shipment_status%' => $translatedStatus,
                    '%tracking_number%'  => (string)($shipment->tracking_number ?? ''),
                    '%tracking_url%'     => (string)($trackingUrl ?? ''),

                ],
                $order->email ?: optional($order->customer)->email
            );            








        }


        return redirect()->back()->with('success', 'Datos de envío actualizados.');
    }

    public function destroy(Shipment $shipment)
    {
        $shipment->delete();
        return redirect()->route('shipments.index')->with('success', 'Envío eliminado.');
    }
}