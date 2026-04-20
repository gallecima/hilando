<?php

namespace App\Http\Controllers;

use App\Events\PaymentStatusUpdated;
use App\Models\Payment;
use App\Services\EmailTemplateSender;
use App\Services\OrderDownloadService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $from   = $request->input('from', now()->startOfMonth()->toDateString());
        $to     = $request->input('to',   now()->endOfMonth()->toDateString());
        $status = $request->input('status'); // pending|completed|failed|null
        $method = $request->input('method'); // string|null

        $baseQuery = \App\Models\Payment::with('order')
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);

        if ($status) {
            $baseQuery->where('status', $status);
        }
        if ($method) {
            $baseQuery->where('method', $method);
        }

        $payments = (clone $baseQuery)
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $availableMethods = \App\Models\Payment::query()
            ->select('method')
            ->whereNotNull('method')
            ->distinct()
            ->pluck('method');

        $summaryQuery = \App\Models\Payment::query()
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to);
        if ($status) $summaryQuery->where('status', $status);

        $summaryByMethod = $summaryQuery
            ->selectRaw('method, COUNT(*) as cnt, SUM(amount) as total')
            ->groupBy('method')
            ->get()
            ->mapWithKeys(function($row){
                return [$row->method ?? 'N/D' => [
                    'count'  => (int)$row->cnt,
                    'amount' => (float)$row->total,
                ]];
            });

        return view('admin.payments.index', [
            'payments'         => $payments,
            'summaryByMethod'  => $summaryByMethod,
            'availableMethods' => $availableMethods,
            'filters'          => compact('from','to','status','method'),
        ]);
    }

    public function create()
    {
        return view('payments.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'order_id'       => 'required|exists:orders,id',
            'method'         => 'required|string',
            'status'         => 'required|in:pending,completed,failed',
            'amount'         => 'required|numeric',
            'transaction_id' => 'nullable|string',
            'payment_data'   => 'nullable|json',
        ]);

        $payment = Payment::create($data);
        $payment->order->updateStatus();

        return redirect()->route('payments.index')->with('success', 'Pago registrado.');
    }

    public function edit(Payment $payment)
    {
        return view('payments.edit', compact('payment'));
    }

    public function update(Request $request, Payment $payment)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,completed,failed',
        ]);

        $oldStatus = $payment->getOriginal('status');

        $payment->update($data);
        $payment->order->updateStatus();

        if ($oldStatus !== $payment->status) {
            $order = $payment->order;

            // Disparar evento de dominio
            event(new PaymentStatusUpdated(
                payment: $payment,
                order: $order,
                oldStatus: $oldStatus,
                newStatus: $payment->status
            ));

            // 1) Intento de email unificado por plugin (AFIP) — maneja "completed" y adjunta PDF
            try {
                /** @var \App\Support\Hooks $hooks */
                $hooks = app(\App\Support\Hooks::class);

                $handled = (string) $hooks->render('email:payment_status_updated.override', $order, $payment, $oldStatus);

                // Log opcional de diagnóstico
                \Log::info('[PaymentController] override hook returned', [
                    'handled' => $handled,
                    'matched' => (bool) preg_match('/\bhandled\b/i', $handled),
                ]);

                if (preg_match('/\bhandled\b/i', $handled)) {
                    return redirect()->back()->with('success', 'Estado de pago actualizado y email enviado (unificado).');
                }
            } catch (\Throwable $e) {
                \Log::error('[AFIP] override hook exception (controller): '.$e->getMessage(), [
                    'order_id'   => $order->id ?? null,
                    'payment_id' => $payment->id ?? null,
                ]);
                // Si el hook falló, continuamos con el envío estándar según estado
            }

            // 2) Si NO fue handled por el hook, enviamos plantilla según el nuevo estado (sin AFIP)
            $status = strtolower((string) $payment->status);

            /** @var EmailTemplateSender $sender */
            $sender = app(EmailTemplateSender::class);

            if ($status === 'completed') {
                /** @var OrderDownloadService $downloadService */
                $downloadService = app(OrderDownloadService::class);

                $sender->send(
                    'order_confirmed',
                    $order,
                    [
                        '%payment_method%' => (string)($payment->method ?? '-'),
                        '%payment_amount%' => number_format((float)($payment->amount ?? 0), 2, ',', '.'),
                        '%order_id%'       => (string)($order->id),
                        '%acceso_plataforma%' => $downloadService->buildAccessPlatformHtml($order),
                        '%post_purchase_block%' => $downloadService->buildPostPurchaseHtml($order),
                    ],
                    $order->email ?: optional($order->customer)->email,
                    $downloadService->attachmentsForEmail($order)
                );

                return redirect()->back()->with('success', 'Estado de pago actualizado y compra notificada.');
            }

            $templateKey = match ($status) {
                'failed'   => 'payment_status_updated_failed',
                default    => 'payment_status_updated_other',
            };

            $sender->send(
                $templateKey,
                $order,
                [
                    '%old_status%'       => ucfirst((string)($oldStatus ?? '-')),
                    '%new_status%'       => ucfirst((string)$payment->status),
                    '%payment_method%'   => (string)($payment->method ?? '-'),
                    '%payment_amount%'   => number_format((float)($payment->amount ?? 0), 2, ',', '.'),
                    '%order_id%'         => (string)($order->id),

                ],
                $order->email ?: optional($order->customer)->email
            );

            return redirect()->back()->with('success', 'Estado de pago actualizado y notificado.');
        }

        return redirect()->back()->with('success', 'Estado de pago actualizado.');
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();
        return redirect()->route('payments.index')->with('success', 'Pago eliminado.');
    }
}
