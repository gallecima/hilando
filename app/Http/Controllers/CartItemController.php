<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use Illuminate\Http\Request;

class CartItemController extends Controller
{
    public function index()
    {
        $items = CartItem::with(['cart', 'product'])->paginate(20);
        return view('cart_items.index', compact('items'));
    }

    public function create()
    {
        return view('cart_items.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'session_id' => 'required|string',
            'is_active' => 'boolean',
            'completed_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        CartItem::create($data);

        return redirect()->route('cart_items.index')->with('success', 'Item agregado al carrito.');
    }

    public function edit(CartItem $cartItem)
    {
        return view('cart_items.edit', compact('cartItem'));
    }

    public function update(Request $request, CartItem $cartItem)
    {
        $data = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'session_id' => 'required|string',
            'is_active' => 'boolean',
            'completed_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $cartItem->update($data);

        return redirect()->route('cart_items.index')->with('success', 'Item actualizado.');
    }

    public function destroy(CartItem $cartItem)
    {
        $cartItem->delete();
        return redirect()->route('cart_items.index')->with('success', 'Item eliminado del carrito.');
    }

    public function myCart()
    {
        $sessionId = session()->getId();

        $cart = Cart::where('session_id', $sessionId)
                    ->where('is_active', true)
                    ->with('items.product')
                    ->first();

        if (!$cart) {
            $cart = Cart::create([
                'session_id' => $sessionId,
                'is_active' => true,
            ]);
        }

        return view('front.cart.show', compact('cart'));
    }    
}