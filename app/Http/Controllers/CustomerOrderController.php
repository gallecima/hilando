<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderDownloadService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CustomerOrderController extends Controller
{
    public function index()
    {
        $customer = Auth::guard('customer')->user();
        $orders = $customer->orders()->latest()->paginate(10);

        return view('front.mi-cuenta.pedidos', compact('orders'));
    }

    public function show(Order $order, OrderDownloadService $downloadService)
    {
        $customer = Auth::guard('customer')->user();

        if ($order->customer_id !== $customer->id) {
            abort(403); // No puede ver órdenes de otros
        }

        $downloads = $downloadService->downloadableItems($order);
        $orderPaid = $downloadService->isPaid($order);

        return view('front.mi-cuenta.pedido-detalle', compact('order', 'downloads', 'orderPaid'));
    }

    public function download(Order $order, int $product, ?string $file = null)
    {
        /** @var OrderDownloadService $downloadService */
        $downloadService = app(OrderDownloadService::class);

        $customer = Auth::guard('customer')->user();

        if ($order->customer_id !== $customer->id) {
            abort(403);
        }

        if (!$downloadService->isPaid($order)) {
            return back()->with('error', 'Tu pago aún no está acreditado. La descarga estará disponible cuando se confirme.');
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
