<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrderInvoiceController extends Controller
{
    public function store(Request $request, Order $order)
    {
        $data = $request->validate([
            'title'       => ['nullable', 'string', 'max:255'],
            'number'      => ['nullable', 'string', 'max:50'],
            'issued_at'   => ['nullable', 'date'],
            'file'        => ['nullable', 'file', 'mimes:pdf', 'max:8192'],
            'external_url'=> ['nullable', 'url', 'max:500'],
        ]);

        if (empty($data['file']) && empty($data['external_url'])) {
            return back()->withErrors(['file' => 'Adjuntá un PDF o proporcioná un enlace.'])->withInput();
        }

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('order-invoices', 'public');
        }

        $invoice = $order->invoices()->create([
            'provider'     => 'manual',
            'title'        => $data['title'] ?? ('Factura ' . ($data['number'] ?? 'manual')),
            'number'       => $data['number'] ?? null,
            'status'       => 'issued',
            'issued_at'    => $data['issued_at'] ?? now(),
            'file_path'    => $filePath,
            'external_url' => $data['external_url'] ?? null,
            'meta'         => null,
        ]);

        return back()->with('success', 'Comprobante adjuntado correctamente.');
    }

    public function destroy(Order $order, OrderInvoice $invoice)
    {
        $this->authorizeInvoice($order, $invoice);

        if ($invoice->file_path) {
            Storage::disk('public')->delete($invoice->file_path);
        }

        $invoice->delete();

        return back()->with('success', 'Comprobante eliminado.');
    }

    protected function authorizeInvoice(Order $order, OrderInvoice $invoice): void
    {
        if ($invoice->order_id !== $order->id) {
            abort(404);
        }
    }
}
