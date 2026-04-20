<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrderDownloadService
{
    public function isPaid(Order $order): bool
    {
        $order->loadMissing('payments');

        $paymentStatus = strtolower((string) optional($order->payments->last())->status);
        $orderStatus = strtolower((string) ($order->status ?? ''));

        return in_array($paymentStatus, ['completed', 'paid'], true)
            || in_array($orderStatus, ['paid', 'delivered'], true);
    }

    /**
     * @return Collection<int, array{
     *   product_id:int,
     *   item_id:int,
     *   file_id:string,
     *   product_name:string,
     *   path:string,
     *   exists:bool,
     *   original_name:string,
     *   download_name:string
     * }>
     */
    public function downloadableItems(Order $order): Collection
    {
        $order->loadMissing('items.product');

        return collect($order->items ?? [])
            ->flatMap(function ($item) {
                $product = $item->product;
                if (!$product) {
                    return [];
                }

                $paths = collect((array) ($product->downloadable_files ?? []))
                    ->map(fn ($path) => trim((string) $path))
                    ->filter(fn (string $path) => $path !== '')
                    ->values();

                if ($paths->isEmpty()) {
                    return [];
                }

                return $paths->map(function (string $path, int $index) use ($item, $product) {
                    $exists = false;
                    try {
                        $exists = Storage::disk('public')->exists($path);
                    } catch (\Throwable) {
                        $exists = false;
                    }

                    $originalName = basename($path);
                    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                    $productNameSlug = Str::slug((string) ($product->name ?? 'descarga')) ?: 'descarga';
                    $originalSlug = Str::slug((string) pathinfo($originalName, PATHINFO_FILENAME)) ?: ('archivo-' . ($index + 1));
                    $downloadName = $productNameSlug . '-' . $originalSlug;
                    if ($extension !== '') {
                        $downloadName .= '.' . $extension;
                    }

                    return [
                        'product_id' => (int) $product->id,
                        'item_id' => (int) $item->id,
                        'file_id' => substr(sha1($path), 0, 16),
                        'product_name' => (string) ($product->name ?? 'Producto digital'),
                        'path' => $path,
                        'exists' => $exists,
                        'original_name' => $originalName,
                        'download_name' => $downloadName,
                    ];
                })->all();
            })
            ->filter()
            ->unique(fn (array $row) => $row['product_id'] . '|' . $row['path'])
            ->values();
    }

    /**
     * @return array<int, array{data:string,name:string,mime:string}>
     */
    public function attachmentsForEmail(Order $order): array
    {
        $attachments = [];

        foreach ($this->downloadableItems($order) as $download) {
            if (!($download['exists'] ?? false)) {
                continue;
            }

            try {
                $binary = Storage::disk('public')->get($download['path']);
                $mime = Storage::disk('public')->mimeType($download['path']) ?: $this->guessMimeType($download['path']);
            } catch (\Throwable) {
                continue;
            }

            if (!is_string($binary) || $binary === '') {
                continue;
            }

            $attachments[] = [
                'data' => $binary,
                'name' => $download['original_name'],
                'mime' => (string) $mime,
            ];
        }

        return $attachments;
    }

    /**
     * Bloque opcional para anexar al email de confirmación.
     */
    public function buildPostPurchaseHtml(Order $order): string
    {
        $downloads = $this->downloadableItems($order)
            ->filter(fn (array $row) => $row['exists'] === true)
            ->values();

        $downloadsHtml = '';
        if ($downloads->isNotEmpty()) {
            $downloadsHtml .= '<p><strong>Archivos adjuntos en este email:</strong></p><ul>';
            foreach ($downloads as $download) {
                $downloadsHtml .= '<li>' . e($download['product_name']) . ' (' . e($download['original_name']) . ')</li>';
            }
            $downloadsHtml .= '</ul>';
        } else {
            $downloadsHtml .= '<p><strong>Descargas:</strong> este pedido no tiene archivos adjuntos disponibles.</p>';
        }

        return '<hr>' . $downloadsHtml . $this->buildAccessPlatformHtml($order);
    }

    public function buildAccessPlatformHtml(Order $order): string
    {
        $email = trim((string) ($order->email ?? ''));
        $dni = trim((string) ($order->customer?->document ?? data_get($order->billing_data_json, 'document_number') ?? ''));

        return '<p><strong>Acceso a la plataforma:</strong><br>'
            . 'Usuario: ' . e($email !== '' ? $email : '-') . '<br>'
            . 'Contraseña (DNI): ' . e($dni !== '' ? $dni : '-') . '</p>';
    }

    private function guessMimeType(string $path): string
    {
        return match (strtolower((string) pathinfo($path, PATHINFO_EXTENSION))) {
            'pdf' => 'application/pdf',
            'zip' => 'application/zip',
            default => 'application/octet-stream',
        };
    }
}
