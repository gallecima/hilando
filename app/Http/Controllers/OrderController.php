<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\SiteInfo;
use Illuminate\Http\Request;

class OrderController extends Controller
{
public function index(Request $request)
{
    $orders = \App\Models\Order::withoutGlobalScopes() // ignora scopes globales
        ->with('customer')
        ->orderByDesc('id')                            // más recientes primero
        ->paginate(20)
        ->withQueryString();

    return view('admin.orders.index', compact('orders'));
}

    public function create()
    {
        return view('orders.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'status' => 'required|in:pending,paid,shipped,delivered,cancelled',
            'total' => 'required|numeric',
            'shipping_address' => 'required|string',
            'billing_data_json' => 'required|json',
            'notes' => 'nullable|string',
            'coupon_id' => 'nullable|exists:discount_coupons,id',
        ]);

        Order::create($data);

        return redirect()->route('admin.orders.index')->with('success', 'Pedido creado.');
    }

    public function edit(Order $order)
    {
        return view('orders.edit', compact('order'));
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'status' => 'required|in:pending,paid,shipped,delivered,cancelled',
            'total' => 'required|numeric',
            'shipping_address' => 'required|string',
            'billing_data_json' => 'required|json',
            'notes' => 'nullable|string',
            'coupon_id' => 'nullable|exists:discount_coupons,id',
        ]);

        $order->update($data);

        return redirect()->route('admin.orders.index')->with('success', 'Pedido actualizado.');
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'items.product', 'payments', 'shipment', 'invoices']); // ajustá las relaciones necesarias
        return view('admin.orders.show', compact('order'));
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return redirect()->route('admin.orders.index')->with('success', 'Pedido eliminado.');
    }
    public function label(Order $order)
    {
        $site = SiteInfo::query()->first();
        $order->load(['shipment', 'payments', 'customer.address']);
        return view('admin.orders.label', compact('order','site'));
    }    
}
