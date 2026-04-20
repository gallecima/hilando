<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\DiscountCoupon;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    // -----------------------------
    // Frontend público - Vista de carrito
    // -----------------------------

    public function index()
    {
        $cart  = $this->cartService->getCart();
        $items = $this->cartService->getCartItems();
        $total = $this->cartService->getTotal();
        $requiresShipping = $this->cartService->requiresShipping();

        return view('front.cart.index', compact('cart', 'items', 'total', 'requiresShipping'));
    }

    public function add(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity'   => 'required|integer|min:1',
                'attributes' => 'nullable|array',
            ]);

            $product = Product::findOrFail($request->product_id);
            $qty     = (int) $request->quantity;
            $selectedAttributes = $this->normalizeSelectedAttributes((array) $request->input('attributes', []));
            $price = $product->resolveUnitPrice(auth('customer')->user(), $selectedAttributes);
            $qty = $product->is_digital ? 1 : max($product->resolveMinQuantity(auth('customer')->user()), $qty);

            if (!$product->is_digital) {
                $availableStock = $this->resolveAvailableStock($product, $selectedAttributes);
                $minimumQuantity = $product->resolveMinQuantity(auth('customer')->user());

                if ($availableStock < $minimumQuantity || $qty > $availableStock) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No hay stock suficiente para este acceso.',
                    ], 422);
                }
            }

            \Log::info('[🛒 CART DEBUG] Intentando agregar producto', [
                'session_id'         => session()->getId(),
                'product_id'         => $product->id,
                'product_name'       => $product->name,
                'base_price'         => $product->price,
                'final_price'        => $price,
                'quantity'           => $qty,
                'selected_attributes'=> $selectedAttributes,
            ]);

            $count = $this->cartService->addProduct($product, $qty, (float) $price, $selectedAttributes);
            $cart = $this->cartService->getCart();
            $items = $this->cartService->getCartItems();

            \Log::info('[🛒 CART STATE] Carrito actualizado', [
                'session_id' => session()->getId(),
                'cart_id'    => $cart->id ?? null,
                'total_items'=> $items->count(),
                'total_qty'  => $items->sum('quantity'),
                'items'      => $items->map(fn ($i) => [
                    'id'        => $i->id,
                    'product'   => $i->product->name ?? null,
                    'qty'       => $i->quantity,
                    'price'     => $i->price,
                    'subtotal'  => $i->price * $i->quantity,
                    'attributes'=> $i->attributes ?? [],
                ]),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Producto agregado al carrito.',
                'count'   => $count,
            ]);
        } catch (\Throwable $e) {
            \Log::error('🛑 Error al agregar al carrito', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno',
                'error'   => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ], 500);
        }
    }


    public function update(Request $request)
    {
        $data = $request->validate([
            'item_id'  => 'required|exists:cart_items,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = $this->cartService->getCart();
        $item = $cart->items()->with('product.attributeValues')->findOrFail($data['item_id']);

        if ($item->product && !$item->product->is_digital) {
            $selectedAttributes = json_decode((string) $item->attribute_values_json, true) ?: [];
            $availableStock = $this->resolveAvailableStock($item->product, $selectedAttributes);
            $minimumQuantity = $item->product->resolveMinQuantity(auth('customer')->user());

            if ($availableStock < $minimumQuantity || (int) $data['quantity'] > $availableStock || (int) $data['quantity'] < $minimumQuantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay stock suficiente para la cantidad seleccionada.',
                ], 422);
            }
        }

        $this->cartService->updateItem($data['item_id'], $data['quantity']);

        return response()->json([
            'success' => true,
            'message' => 'Cantidad actualizada.',
            'count'   => $this->cartService->getCount(),
        ]);
    }

    public function remove(Request $request)
    {
        $data = $request->validate([
            'item_id' => 'required|exists:cart_items,id',
        ]);

        $this->cartService->removeItem($data['item_id']);

        // Si usás redirección, dejalo así; si usás fetch, podés devolver JSON con count.
        return redirect()->route('cart.index')->with('success', 'Producto eliminado del carrito.');
    }

    private function normalizeSelectedAttributes(array $attributesInput): array
    {
        $selectedAttributes = [];

        foreach ($attributesInput as $attributeId => $valueId) {
            $attribute = \App\Models\Attribute::find($attributeId);
            $value = \App\Models\AttributeValue::find($valueId);

            if ($attribute && $value) {
                $selectedAttributes[] = [
                    'attribute_id'   => $attribute->id,
                    'attribute_name' => $attribute->name,
                    'value_id'       => $value->id,
                    'value_name'     => $value->value,
                ];
            }
        }

        return $selectedAttributes;
    }

    private function resolveAvailableStock(Product $product, array $selectedAttributes = []): int
    {
        $product->loadMissing('attributeValues');

        if (count($selectedAttributes) === 1) {
            $valueId = (int) data_get($selectedAttributes, '0.value_id', 0);
            $attributeValue = $product->attributeValues->firstWhere('id', $valueId);

            if ($attributeValue && $attributeValue->pivot && $attributeValue->pivot->stock !== null) {
                return (int) $attributeValue->pivot->stock;
            }
        }

        return (int) $product->stock;
    }

    public function partial()
    {
        // 1) Intentá recuperar por cart_id guardado en sesión
        $sid      = session()->getId();
        $cartId   = session('cart_id');

        if ($cartId) {
            $cart = \App\Models\Cart::with('items.product')->find($cartId);
            if (! $cart) {
                // cart_id roto en sesión, lo limpiamos
                session()->forget('cart_id');
            }
        }

        // 2) Si no hay cart_id válido en sesión, buscá por session_id (mismo SID de tus logs)
        if (! isset($cart) || ! $cart) {
            $cart = \App\Models\Cart::with('items.product')
                ->where('session_id', $sid)
                ->where('is_active', 1)
                ->latest('id')
                ->first();

            if ($cart) {
                session(['cart_id' => $cart->id]); // ¡persistilo para los próximos requests!
            }
        }

        // 3) Si sigue sin haber carrito, NO crees uno nuevo para el offcanvas (evitás un cart “fantasma”)
        if ($cart) {
            // ✅ Usar el service para que aplique beneficios/ajustes de plugins (ej: Club El Tribuno)
            // y obtener el estado real del carrito (sin crear uno nuevo, porque ya tenemos cart_id en sesión).
            $items = $this->cartService->getCartItems();
            $total = $items->sum(fn($i) => $i->price * $i->quantity);
            $requiresShipping = $this->cartService->requiresShipping();
        } else {
            $items = collect();
            $total = 0;
            $requiresShipping = false;
        }

        \Log::info('[cart.partial FIX]', [
            'sid'          => $sid,
            'session_cart' => session('cart_id'),
            'resolved_id'  => $cart?->id,
            'items'        => $items->count(),
            'total'        => $total,
        ]);

        return view('front.cart.partials.offcanvas', compact('items', 'total', 'requiresShipping'));
    }



    // -----------------------------
    // Backend - Administración de carritos
    // -----------------------------

    public function adminIndex()
    {
        $carts = \App\Models\Cart::with('customer')->paginate(20);
        return view('carts.index', compact('carts'));
    }

    public function create()
    {
        return view('carts.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'session_id'  => 'required|string',
            'is_active'   => 'boolean',
        ]);

        \App\Models\Cart::create($data);

        return redirect()->route('carts.index')->with('success', 'Carrito creado.');
    }

    public function edit(\App\Models\Cart $cart)
    {
        return view('carts.edit', compact('cart'));
    }

    public function adminUpdate(Request $request, \App\Models\Cart $cart)
    {
        $data = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'session_id'  => 'required|string',
            'is_active'   => 'boolean',
        ]);

        $cart->update($data);

        return redirect()->route('carts.index')->with('success', 'Carrito actualizado.');
    }

    public function destroy(\App\Models\Cart $cart)
    {
        $cart->delete();
        return redirect()->route('carts.index')->with('success', 'Carrito eliminado.');
    }

    public function applyCoupon(Request $request)
    {
        $code   = $request->input('coupon_code');
        $coupon = DiscountCoupon::where('code', $code)->where('is_active', true)->first();

        if (!$coupon) {
            return back()->with('coupon_error', 'Cupón inválido.');
        }

        if ($coupon->valid_from && now()->lt($coupon->valid_from)) {
            return back()->with('coupon_error', 'El cupón aún no está disponible.');
        }

        if ($coupon->valid_until && now()->gt($coupon->valid_until)) {
            return back()->with('coupon_error', 'El cupón ha expirado.');
        }

        if ($coupon->max_uses !== null && $coupon->uses >= $coupon->max_uses) {
            return back()->with('coupon_error', 'El cupón ha alcanzado su límite de usos.');
        }

        session()->put('discount_coupon', $coupon->toArray());

        return back()->with('coupon_success', 'Cupón aplicado correctamente.');
    }
}
