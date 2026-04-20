<?php

namespace Plugins\MercadoPago\Jobs;

use Carbon\Carbon;
use App\Events\PaymentStatusUpdated;
use App\Models\Payment;
use App\Services\EmailTemplateSender;
use App\Services\OrderDownloadService;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Plugins\AFIP\Models\AfipInvoice;

class HandleMpPaymentUpdate
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $paymentId,
        public ?string $oldStatus,
        public string $newStatus
    ) {}

    public function handle(EmailTemplateSender $sender, OrderDownloadService $downloadService): void
    {
        $payment = Payment::with('order')->find($this->paymentId);
        if (! $payment) {
            Log::warning('[MP] HandleMpPaymentUpdate: payment no encontrado', ['payment_id' => $this->paymentId]);
            return;
        }

        $order = $payment->order;
        if (! $order) {
            Log::warning('[MP] HandleMpPaymentUpdate: order no encontrado', [
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
            ]);
            return;
        }

        if ($this->oldStatus !== $this->newStatus) {
            event(new PaymentStatusUpdated(
                payment: $payment,
                order: $order,
                oldStatus: $this->oldStatus,
                newStatus: $this->newStatus
            ));
        }

        if ($this->newStatus !== 'completed') {
            return;
        }

        // Email #1: confirmación de compra (idempotente; evita duplicados por webhook + return + reintentos de MP)
        $lockMinutes = 10;
        $shouldSend = false;
        try {
            DB::transaction(function () use (&$shouldSend, $lockMinutes) {
                $lockedPayment = Payment::query()->whereKey($this->paymentId)->lockForUpdate()->first();
                if (! $lockedPayment) {
                    $shouldSend = false;
                    return;
                }

                $paymentData = $lockedPayment->payment_data;
                if (! is_array($paymentData)) {
                    $decoded = is_string($paymentData) ? json_decode($paymentData, true) : null;
                    $paymentData = is_array($decoded) ? $decoded : [];
                }

                $pixelio = (array) ($paymentData['_pixelio'] ?? []);
                $emails = (array) ($pixelio['emails'] ?? []);
                $meta = (array) ($emails['order_confirmed'] ?? []);

                if (! empty($meta['sent_at'])) {
                    $shouldSend = false;
                    return;
                }

                $sendingAt = $meta['sending_at'] ?? null;
                if ($sendingAt) {
                    try {
                        $sendingDt = Carbon::parse((string) $sendingAt);
                        if ($sendingDt->greaterThan(now()->subMinutes($lockMinutes))) {
                            $shouldSend = false;
                            return;
                        }
                    } catch (\Throwable $e) {
                        // si el timestamp está corrupto, seguimos y reintentamos
                    }
                }

                $meta['sending_at'] = now()->toIso8601String();
                $meta['attempts'] = ((int) ($meta['attempts'] ?? 0)) + 1;
                $meta['last_error'] = null;
                $emails['order_confirmed'] = $meta;
                $pixelio['emails'] = $emails;
                $paymentData['_pixelio'] = $pixelio;

                $lockedPayment->update(['payment_data' => $paymentData]);
                $shouldSend = true;
            });
        } catch (\Throwable $e) {
            Log::error('[MP] HandleMpPaymentUpdate: no se pudo tomar lock idempotente', [
                'payment_id' => $this->paymentId,
                'err' => $e->getMessage(),
            ]);
        }

        if (! $shouldSend) {
            // Igual seguimos con el email #2 (factura) porque puede que el confirmado ya exista.
        }

        if ($shouldSend) {
            $ok = false;
            $sendErr = null;
            try {
                $ok = $sender->send(
                    'order_confirmed',
                    $order,
                    [
                        '%payment_method%' => (string) ($payment->method ?? 'MercadoPago'),
                        '%payment_amount%' => number_format((float) ($payment->amount ?? 0), 2, ',', '.'),
                        '%order_id%'       => (string) ($order->id ?? ''),
                        '%acceso_plataforma%' => $downloadService->buildAccessPlatformHtml($order),
                        '%post_purchase_block%' => $downloadService->buildPostPurchaseHtml($order),
                    ],
                    null,
                    $downloadService->attachmentsForEmail($order)
                );
            } catch (\Throwable $e) {
                $ok = false;
                $sendErr = $e->getMessage();
                Log::error('[MP] HandleMpPaymentUpdate: error enviando order_confirmed', [
                    'order_id' => $order->id,
                    'err' => $sendErr,
                ]);
            }

            if (! $ok && $sendErr === null) {
                $sendErr = 'EmailTemplateSender->send() devolvió false';
            }

            try {
                DB::transaction(function () use ($ok, $sendErr) {
                    $lockedPayment = Payment::query()->whereKey($this->paymentId)->lockForUpdate()->first();
                    if (! $lockedPayment) {
                        return;
                    }

                    $paymentData = $lockedPayment->payment_data;
                    if (! is_array($paymentData)) {
                        $decoded = is_string($paymentData) ? json_decode($paymentData, true) : null;
                        $paymentData = is_array($decoded) ? $decoded : [];
                    }

                    $pixelio = (array) ($paymentData['_pixelio'] ?? []);
                    $emails = (array) ($pixelio['emails'] ?? []);
                    $meta = (array) ($emails['order_confirmed'] ?? []);

                    if ($ok) {
                        $meta['sent_at'] = now()->toIso8601String();
                        $meta['sending_at'] = null;
                        $meta['last_error'] = null;
                    } else {
                        $meta['sending_at'] = null;
                        $meta['last_error'] = $sendErr ?: null;
                    }

                    $emails['order_confirmed'] = $meta;
                    $pixelio['emails'] = $emails;
                    $paymentData['_pixelio'] = $pixelio;

                    $lockedPayment->update(['payment_data' => $paymentData]);
                });
            } catch (\Throwable $e) {
                Log::warning('[MP] HandleMpPaymentUpdate: no se pudo persistir meta de order_confirmed', [
                    'payment_id' => $this->paymentId,
                    'err' => $e->getMessage(),
                ]);
            }
        }

        // Email #2: comprobante AFIP (idempotente; se envía cuando existe `afip_invoices` + `response_payload.pdf.file`)
        $shouldSendInvoice = false;
        try {
            DB::transaction(function () use (&$shouldSendInvoice, $lockMinutes) {
                $lockedPayment = Payment::query()->whereKey($this->paymentId)->lockForUpdate()->first();
                if (! $lockedPayment) {
                    $shouldSendInvoice = false;
                    return;
                }

                $paymentData = $lockedPayment->payment_data;
                if (! is_array($paymentData)) {
                    $decoded = is_string($paymentData) ? json_decode($paymentData, true) : null;
                    $paymentData = is_array($decoded) ? $decoded : [];
                }

                $pixelio = (array) ($paymentData['_pixelio'] ?? []);
                $emails = (array) ($pixelio['emails'] ?? []);
                $meta = (array) ($emails['afip_invoice'] ?? []);

                if (! empty($meta['sent_at'])) {
                    $shouldSendInvoice = false;
                    return;
                }

                $sendingAt = $meta['sending_at'] ?? null;
                if ($sendingAt) {
                    try {
                        $sendingDt = Carbon::parse((string) $sendingAt);
                        if ($sendingDt->greaterThan(now()->subMinutes($lockMinutes))) {
                            $shouldSendInvoice = false;
                            return;
                        }
                    } catch (\Throwable $e) {
                        // timestamp corrupto, seguimos
                    }
                }

                $meta['sending_at'] = now()->toIso8601String();
                $meta['attempts'] = ((int) ($meta['attempts'] ?? 0)) + 1;
                $meta['last_error'] = null;
                $emails['afip_invoice'] = $meta;
                $pixelio['emails'] = $emails;
                $paymentData['_pixelio'] = $pixelio;

                $lockedPayment->update(['payment_data' => $paymentData]);
                $shouldSendInvoice = true;
            });
        } catch (\Throwable $e) {
            Log::error('[MP] HandleMpPaymentUpdate: no se pudo tomar lock idempotente (afip_invoice)', [
                'payment_id' => $this->paymentId,
                'err' => $e->getMessage(),
            ]);
        }

        if (! $shouldSendInvoice) {
            return;
        }

        $afipInv = null;
        $pdfUrl = null;
        $pdfName = null;
        $pdfData = null;
        $lastPdfErr = null;

        for ($i = 0; $i < 10; $i++) {
            try {
                $afipInv = AfipInvoice::query()
                    ->where('order_id', $order->id)
                    ->where('status', 'success')
                    ->latest('id')
                    ->first();

                if ($afipInv) {
                    $pdfUrl = data_get($afipInv->response_payload, 'pdf.file');
                    $pdfName = data_get($afipInv->response_payload, 'pdf.file_name');

                    if (! $pdfName) {
                        $pv  = str_pad((string) ($afipInv->pto_vta ?? 0), 4, '0', STR_PAD_LEFT);
                        $num = $afipInv->cbte_numero ? str_pad((string) $afipInv->cbte_numero, 8, '0', STR_PAD_LEFT) : '00000000';
                        $pdfName = "Factura-{$pv}-{$num}.pdf";
                    }

                    if ($pdfUrl) {
                        if (preg_match('#^https?://#i', (string) $pdfUrl)) {
                            try {
                                $resp = Http::timeout(20)->get($pdfUrl);
                            } catch (\Throwable $e) {
                                // En algunos hosts (self-signed / SSL raro) puede fallar la verificación
                                $resp = Http::withoutVerifying()->timeout(20)->get($pdfUrl);
                            }

                            if ($resp->successful() && $resp->body()) {
                                $pdfData = $resp->body();
                                break;
                            }
                        } elseif (Storage::exists($pdfUrl)) {
                            $pdfData = Storage::get($pdfUrl);
                            break;
                        } else {
                            // Si no es URL ni path existente, igual salimos con link (se enviará sin adjunto).
                            break;
                        }
                    }
                }
            } catch (\Throwable $e) {
                $lastPdfErr = $e->getMessage();
            }

            sleep(1);
        }

        $attachments = [];
        if ($pdfData) {
            $attachments[] = [
                'data' => $pdfData,
                'name' => $pdfName ?: 'Comprobante.pdf',
                'mime' => 'application/pdf',
            ];
        }

        $ok = false;
        $sendErr = null;
        if (! $pdfUrl) {
            $ok = false;
            $sendErr = 'No se encontró PDF en afip_invoices.response_payload.pdf.file';
        } else {
            try {
                $ok = $sender->send(
                    'payment_status_updated',
                    $order,
                    [
                        // Ajuste semántico del template: lo usamos para "comprobante disponible"
                        'payment_status' => 'Comprobante fiscal emitido',
                        // Si la plantilla usa %old_status% / %new_status%, los completamos para evitar placeholders crudos.
                        'old_status'     => 'Pago confirmado',
                        'new_status'     => 'Comprobante fiscal emitido',
                        // Reusamos el link del template para apuntar al PDF
                        'order_link'     => (string) $pdfUrl,
    
                        'afip_cae'       => (string) ($afipInv->cae ?? ''),
                        'afip_num'       => (string) ($afipInv->cbte_numero ?? ''),
                        'afip_pdfurl'    => (string) $pdfUrl,
                    ],
                    null,
                    $attachments
                );
            } catch (\Throwable $e) {
                $ok = false;
                $sendErr = $e->getMessage();
                Log::error('[MP] HandleMpPaymentUpdate: error enviando email AFIP (payment_status_updated)', [
                    'order_id' => $order->id,
                    'err' => $sendErr,
                ]);
            }
        }

        if (! $ok && $sendErr === null) {
            $sendErr = 'EmailTemplateSender->send() devolvió false';
        }

        try {
            DB::transaction(function () use ($ok, $sendErr, $afipInv, $pdfUrl, $pdfData, $lastPdfErr) {
                $lockedPayment = Payment::query()->whereKey($this->paymentId)->lockForUpdate()->first();
                if (! $lockedPayment) {
                    return;
                }

                $paymentData = $lockedPayment->payment_data;
                if (! is_array($paymentData)) {
                    $decoded = is_string($paymentData) ? json_decode($paymentData, true) : null;
                    $paymentData = is_array($decoded) ? $decoded : [];
                }

                $pixelio = (array) ($paymentData['_pixelio'] ?? []);
                $emails = (array) ($pixelio['emails'] ?? []);
                $meta = (array) ($emails['afip_invoice'] ?? []);

                if ($ok) {
                    $meta['sent_at'] = now()->toIso8601String();
                    $meta['sending_at'] = null;
                    $meta['last_error'] = null;
                    $meta['afip_invoice_id'] = $afipInv?->id;
                    $meta['afip_pdf_url'] = $pdfUrl;
                    $meta['attached_pdf'] = (bool) $pdfData;
                } else {
                    $meta['sending_at'] = null;
                    $meta['last_error'] = $sendErr ?: null;
                    if ($lastPdfErr) $meta['last_pdf_error'] = $lastPdfErr;
                }

                $emails['afip_invoice'] = $meta;
                $pixelio['emails'] = $emails;
                $paymentData['_pixelio'] = $pixelio;

                $lockedPayment->update(['payment_data' => $paymentData]);
            });
        } catch (\Throwable $e) {
            Log::warning('[MP] HandleMpPaymentUpdate: no se pudo persistir meta de afip_invoice', [
                'payment_id' => $this->paymentId,
                'err' => $e->getMessage(),
            ]);
        }
    }
}
