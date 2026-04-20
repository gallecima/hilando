<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\ShipmentMethod;
use Illuminate\Support\Facades\DB;

class CartService
{
    private const DIGITAL_ONLY_CHECKOUT = false;

    /**
     * Devuelve SIEMPRE el carrito activo del usuario/sesión.
     * - Prioriza cart_id guardado en sesión
     * - Si no está, busca por session_id (invitado) o customer_id (logueado)
     * - Si no existe, crea uno nuevo
     * - Siempre persiste session(['cart_id' => ...])
     */
    public function getCart(): Cart
    {
        // 1) Si hay cart_id en sesión, usalo
        if ($id = session('cart_id')) {
            $cart = Cart::with('items.product')->find($id);
            if ($cart && (int)$cart->is_active === 1) {
                return $cart;
            }
            session()->forget('cart_id');
        }

        $sid         = session()->getId();
        $isLogged    = auth('customer')->check();
        $customerId  = $isLogged ? auth('customer')->id() : null;

        // 2) Si está logueado, intentá por customer_id
        if ($customerId) {
            $cart = Cart::with('items.product')
                ->where('customer_id', $customerId)
                ->where('is_active', 1)
                ->latest('id')
                ->first();

            // Si no hay cart del cliente, intentá adoptar el de la sesión actual
            if (! $cart) {
                $sessionCart = Cart::with('items.product')
                    ->where('session_id', $sid)
                    ->where('is_active', 1)
                    ->latest('id')
                    ->first();

                if ($sessionCart) {
                    // Adoptar el carrito de sesión al cliente
                    $sessionCart->customer_id = $customerId;
                    $sessionCart->save();
                    $cart = $sessionCart->fresh('items.product');
                }
            }
        } else {
            // Invitado: buscar por session_id
            $cart = Cart::with('items.product')
                ->where('session_id', $sid)
                ->where('is_active', 1)
                ->latest('id')
                ->first();
        }

        // 3) Si no hay carrito, crear uno
        if (! $cart) {
            $cart = Cart::create([
                'session_id' => $sid,
                'customer_id'=> $customerId,
                'is_active'  => 1,
            ]);
            $cart->load('items.product');
        }

        session(['cart_id' => $cart->id]);

        $this->synchronizeCartPricingContext($cart);

        return $cart->fresh('items.product') ?? $cart;
    }

    /**
     * Forza el merge cuando un usuario se loguea:
     * - si hay carrito de sesión Y carrito del cliente, combina líneas.
     * - al final queda un único carrito del cliente.
     */
    public function mergeGuestCartIntoCustomerCart(): void
    {
        if (! auth('customer')->check()) return;

        $sid        = session()->getId();
        $customerId = auth('customer')->id();

        $guestCart = Cart::with('items')->where('session_id', $sid)->where('is_active', 1)->latest('id')->first();
        $userCart  = Cart::with('items')->where('customer_id', $customerId)->where('is_active', 1)->latest('id')->first();

        if ($guestCart && $userCart && $guestCart->id !== $userCart->id) {
            DB::transaction(function () use ($guestCart, $userCart) {
                foreach ($guestCart->items as $gItem) {
                    // Consolidar por producto + firma de atributos
                    $signature = $this->attributesSignature(
                        json_decode((string)$gItem->attribute_values_json, true) ?: []
                    );

                    $match = $userCart->items()
                        ->where('product_id', $gItem->product_id)
                        ->where('attribute_values_json', $gItem->attribute_values_json) // mismo json normalizado
                        ->lockForUpdate()
                        ->first();

                    if ($match) {
                        $match->increment('quantity', (int)$gItem->quantity);
                        $gItem->delete();
                    } else {
                        $gItem->cart_id = $userCart->id;
                        $gItem->save();
                    }
                }

                // Inactivar/eliminar carrito huésped si quedó vacío
                if ($guestCart->items()->count() === 0) {
                    $guestCart->is_active = 0;
                    $guestCart->save();
                }
            });

            // Refrescar cache de sesión
            session(['cart_id' => $userCart->id]);
        } elseif ($guestCart && ! $userCart) {
            // Adoptar carrito de sesión como carrito del cliente
            $guestCart->customer_id = $customerId;
            $guestCart->save();
            session(['cart_id' => $guestCart->id]);
        } elseif ($userCart) {
            session(['cart_id' => $userCart->id]);
        }
    }

    // ======================
    // Operaciones principales
    // ======================

    public function isEmpty(): bool
    {
        return $this->getCount() === 0;
    }

    public function getCount(): int
    {
        return (int) $this->getCart()->items()->sum('quantity');
    }

    public function syncCountToSession(): int
    {
        $count = $this->getCount();
        session(['cart_item_count' => $count]);
        return $count;
    }

    /**
     * Agrega un producto (consolidando por producto+atributos).
     * $selectedAttributes: array de ['attribute_id','attribute_name','value_id','value_name']
     */
    public function addProduct(Product $product, int $quantity, float $price, array $selectedAttributes = []): int
    {
        return DB::transaction(function () use ($product, $quantity, $price, $selectedAttributes) {
            $cart = $this->getCart();
            session(['cart_id' => $cart->id]); // asegurar persistencia
            $isDigitalProduct = (bool) ($product->is_digital ?? false);
            $minQuantity = $product->resolveMinQuantity(auth('customer')->user());
            $normalizedQty = $isDigitalProduct ? 1 : max($minQuantity, (int) $quantity);

            // Normalizar firma para comparar (mismo orden)
            $targetSig      = $this->attributesSignature($selectedAttributes);
            $normalizedJson = $this->normalizeAttributesJson($selectedAttributes);

            // Buscar líneas del mismo producto
            $rows = $cart->items()
                ->where('product_id', $product->id)
                ->lockForUpdate()
                ->get();

            $matched = null;
            foreach ($rows as $row) {
                $sig = $this->attributesSignature(json_decode((string)$row->attribute_values_json, true) ?: []);
                if ($sig === $targetSig) { $matched = $row; break; }
            }

            if ($matched) {
                if ($isDigitalProduct) {
                    $matched->quantity = 1;
                } else {
                    $matched->quantity += $normalizedQty;
                }
                // Si querés actualizar el precio unitario a la última selección:
                $matched->price = $price;
                $matched->save();
            } else {
                $cart->items()->create([
                    'product_id'            => $product->id,
                    'name'                  => $product->name,
                    'image'                 => $product->featured_image,
                    'quantity'              => $normalizedQty,
                    'price'                 => $price,
                    'attribute_values_json' => $normalizedJson,
                ]);
            }

            return $this->syncCountToSession();
        });
    }

    public function updateItem(int $itemId, int $quantity): void
    {
        $cart = $this->getCart();
        $item = $cart->items()->findOrFail($itemId);
        $item->loadMissing('product');
        $isDigitalProduct = (bool) ($item->product->is_digital ?? false);
        $minQuantity = $item->product ? $item->product->resolveMinQuantity(auth('customer')->user()) : 1;
        $normalizedQty = $isDigitalProduct ? 1 : max($minQuantity, (int) $quantity);

        $item->update(['quantity' => $normalizedQty]);
        $this->syncCountToSession();
    }

    public function removeItem(int $itemId): void
    {
        $cart = $this->getCart();
        $cart->items()->where('id', $itemId)->delete();
        $this->syncCountToSession();
    }

    public function clear(): void
    {
        $cart = $this->getCart();
        $cart->items()->delete();
        $this->syncCountToSession();
    }

    public function getCartItems()
    {
        return $this->getCart()->items()->with('product')->get();
    }

    private function synchronizeCartPricingContext(Cart $cart): void
    {
        $cart->loadMissing('items.product');
        $customer = auth('customer')->user();
        $itemsUpdated = false;

        foreach ($cart->items as $item) {
            if (!$item->product) {
                continue;
            }

            $selectedAttributes = json_decode((string) $item->attribute_values_json, true) ?: [];
            $resolvedPrice = $item->product->resolveUnitPrice($customer, $selectedAttributes);
            $resolvedMinQuantity = (bool) ($item->product->is_digital ?? false)
                ? 1
                : $item->product->resolveMinQuantity($customer);
            $resolvedQuantity = max((int) $item->quantity, $resolvedMinQuantity);

            $payload = [];

            if (round((float) $item->price, 2) !== round((float) $resolvedPrice, 2)) {
                $payload['price'] = $resolvedPrice;
            }

            if ((int) $item->quantity !== (int) $resolvedQuantity) {
                $payload['quantity'] = $resolvedQuantity;
            }

            if ($payload !== []) {
                $item->update($payload);
                $itemsUpdated = true;
            }
        }

        if ($itemsUpdated) {
            $cart->load('items.product');
            session([
                'cart_item_count' => (int) $cart->items->sum('quantity'),
            ]);
        }
    }

    // ======================
    // Totales / descuentos / envío
    // ======================

    public function getSubtotal(): float
    {
        return (float) $this->getCartItems()
            ->sum(fn($i) => (float)$i->price * (int)$i->quantity);
    }

    public function getTotal(): float
    {
        return (float) $this->getCartItems()
            ->sum(fn($i) => (float)$i->price * (int)$i->quantity);
    }

    public function getCoupon(): ?array
    {
        return session('discount_coupon');
    }

    public function getDiscountTotal(): float
    {
        $coupon = session('discount_coupon');
        if (!$coupon) return 0.0;

        $total = $this->getSubtotal();
        if (($coupon['discount_type'] ?? null) === 'percentage') {
            return round($total * ((float)$coupon['discount_value'] / 100), 2);
        }
        return round(min((float)$coupon['discount_value'], $total), 2);
    }

    protected function getSelectedShipmentMethod(): ?ShipmentMethod
    {
        $methodId = (int) session('checkout.shipment_method_id');
        if ($methodId <= 0) return null;

        $method = ShipmentMethod::available()->find($methodId);
        if ($method) return $method;

        // Limpia estado viejo (plugin eliminado/inactivo o método ya no válido).
        session()->forget(['checkout.shipment_method_id', 'shipping.override', 'shipping.package_plan']);
        return null;
    }

    protected function isDigitalOnlyCart(): bool
    {
        if (self::DIGITAL_ONLY_CHECKOUT) {
            return true;
        }

        $items = $this->getCartItems();
        if ($items->isEmpty()) return true;

        foreach ($items as $item) {
            $product = $item->product;
            if (!$product) continue;

            $isDigitalProduct = (bool) ($product->is_digital ?? false);
            $hasDownloadFile  = (bool) ($product->has_downloadable_files ?? false);

            if (!$isDigitalProduct && !$hasDownloadFile) {
                return false;
            }
        }

        return true;
    }

    public function requiresShipping(): bool
    {
        return !$this->isDigitalOnlyCart();
    }

    protected function getValidShippingOverride(?ShipmentMethod $method): ?array
    {
        $ov = session('shipping.override');
        if (!is_array($ov)) return null;
        if (($ov['source'] ?? null) !== 'enviacom') return null;
        if (!array_key_exists('amount', $ov)) return null;

        // El override de Envia sólo es válido si el método seleccionado activo es de Envia.
        if (!$method || ($method->plugin_key ?? null) !== 'enviacom') {
            session()->forget(['shipping.override', 'shipping.package_plan']);
            return null;
        }

        return $ov;
    }

    public function getShippingCost(): float
    {
        if ($this->isDigitalOnlyCart()) {
            session()->forget(['checkout.shipment_method_id', 'shipping.override', 'shipping.package_plan']);
            return 0.0;
        }

        $method = $this->getSelectedShipmentMethod();
        $ov = $this->getValidShippingOverride($method);
        if ($ov) {
            return (float) $ov['amount'];
        }

        return (float) ($method?->amount ?? 0);
    }

    public function getShippingDiscount(): float
    {
        if ($this->isDigitalOnlyCart()) {
            return 0.0;
        }

        $method = $this->getSelectedShipmentMethod();
        $ov = $this->getValidShippingOverride($method);
        if ($ov) {
            return 0.0;
        }

        if (! $method) return 0.0;

        if (in_array((string) $method->discount_type, ['percent', 'percentage'], true)) {
            return round(($this->getSubtotal() - $this->getDiscountTotal()) * ((float)$method->discount_value / 100), 2);
        }
        if (in_array((string) $method->discount_type, ['fixed', 'amount'], true)) {
            return round((float)$method->discount_value, 2);
        }
        return 0.0;
    }

    public function getTotalWithDiscount(): float
    {
        return max(0, $this->getSubtotal()
            + $this->getShippingCost()
            - $this->getDiscountTotal()
            - $this->getShippingDiscount());
    }

    // ======================
    // Helpers de atributos
    // ======================

    /**
     * Firma determinística de atributos para comparar líneas:
     * sólo usa attribute_id y value_id, ordenados asc.
     */
    protected function attributesSignature(array $attrs): string
    {
        $pairs = array_map(function ($a) {
            return [
                'attribute_id' => (int) ($a['attribute_id'] ?? 0),
                'value_id'     => (int) ($a['value_id'] ?? 0),
            ];
        }, $attrs);

        usort(
            $pairs,
            fn($a, $b) => ($a['attribute_id'] <=> $b['attribute_id'])
                ?: ($a['value_id'] <=> $b['value_id'])
        );

        return json_encode($pairs);
    }

    /**
     * Normaliza el JSON de atributos (mismo orden/shape) para guardar en DB.
     */
    protected function normalizeAttributesJson(array $attrs): string
    {
        usort(
            $attrs,
            fn($a, $b) => ((int)($a['attribute_id'] ?? 0) <=> (int)($b['attribute_id'] ?? 0))
                ?: ((int)($a['value_id'] ?? 0) <=> (int)($b['value_id'] ?? 0))
        );

        return json_encode(array_values($attrs));
    }
}
