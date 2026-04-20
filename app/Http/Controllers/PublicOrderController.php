<?php

// app/Http/Controllers/PublicOrderController.php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderDownloadService;
use Illuminate\Support\Facades\Storage;

class PublicOrderController extends Controller
{
    public function show(string $token, OrderDownloadService $downloadService)
    {
        $order = Order::where('public_token', $token)->with(['items.product', 'shipment', 'payments'])->firstOrFail();
        $downloads = $downloadService->downloadableItems($order);
        $orderPaid = $downloadService->isPaid($order);

        return view('front.orders.track', compact('order', 'downloads', 'orderPaid'));
    }

    public function download(string $token, int $product, ?string $file = null)
    {
        /** @var OrderDownloadService $downloadService */
        $downloadService = app(OrderDownloadService::class);

        $order = Order::where('public_token', $token)->with(['items.product', 'payments'])->firstOrFail();

        if (!$downloadService->isPaid($order)) {
            return redirect()
                ->route('orders.track', ['token' => $token])
                ->with('error', 'Tu pago aún no está acreditado. La descarga estará disponible cuando se confirme.');
        }

        $downloads = $downloadService->downloadableItems($order)
            ->filter(fn (array $row) => (int) $row['product_id'] === (int) $product)
            ->values();

        $download = $file
            ? $downloads->first(fn (array $row) => (string) ($row['file_id'] ?? '') === $file)
            : $downloads->first();

        if (!$download || !($download['exists'] ?? false)) {
            abort(404);
        }

        return Storage::disk('public')->download($download['path'], $download['download_name']);
    }
}
