<div class="card mt-4 mt-lg-0 front-form-card">
  <div class="card-header bg-light">
    <strong>Resumen del carrito</strong>
  </div>
  <div class="card-body">
    @php
      /** @var \App\Services\CartService $cartService */
      $cartService = app(\App\Services\CartService::class);

      $cart  = $cartService->getCart();
      $items = $cart->items;
      $coupon = session('discount_coupon');

      $subtotal = (float) $cartService->getSubtotal();
      $discount = (float) $cartService->getDiscountTotal();
      $requiresShipping = $cartService->requiresShipping();

      $selectedShipmentMethodId = (int) session('checkout.shipment_method_id');
      $selectedShipmentMethod = $selectedShipmentMethodId > 0
        ? \App\Models\ShipmentMethod::available()->find($selectedShipmentMethodId)
        : null;

      $shippingCost = $selectedShipmentMethod ? (float) $cartService->getShippingCost() : 0.0;
      $shippingDiscount = $selectedShipmentMethod ? (float) $cartService->getShippingDiscount() : 0.0;
      $productsTotal = max(0, $subtotal - $discount);
      $finalTotal = $selectedShipmentMethod ? (float) $cartService->getTotalWithDiscount() : $productsTotal;
      $isFreeCheckout = (!$requiresShipping || $selectedShipmentMethod) && round((float) $finalTotal, 2) <= 0.0;
      $packagePlan = (array) session('shipping.package_plan', []);
    @endphp

    @php
      $tribunoSub = (array) session('tribuno.subscription', []);
      $tribunoEnabled = false;
      if (\Illuminate\Support\Facades\Schema::hasTable('plugins')) {
        $tribunoEnabled = \Illuminate\Support\Facades\DB::table('plugins')
          ->whereIn('slug', ['tribunosubscription', 'tribuno-subscription', 'tribuno'])
          ->where('is_active', 1)
          ->exists();
      }
      if (!$tribunoEnabled) {
        $tribunoSub = [];
      }
    @endphp

    @if(($tribunoSub['applied'] ?? false) === true)
      <div class="alert alert-success py-2 px-3 small mb-3">
        <strong>Club El Tribuno:</strong> descuento aplicado
        @if((float)($tribunoSub['savings'] ?? 0) > 0)
          — Ahorrás ${{ number_format((float)$tribunoSub['savings'], 2) }}
        @endif
      </div>
    @endif

    {{-- Items del carrito --}}
    @forelse ($items as $item)
      <div class="d-flex justify-content-between mb-2">
        <div>
          <strong>{{ $item->name }}</strong>
          @if($item->attribute_values_json)
            <div class="small text-muted">
              @foreach(json_decode($item->attribute_values_json, true) as $attr)
                @php
                  $hexColor = \App\Models\AttributeValue::normalizeHexColor($attr['value_name'] ?? null);
                @endphp
                {{ $attr['attribute_name'] }}:
                @if($hexColor)
                  <span class="attribute-inline-swatch ms-1" style="background-color: {{ $hexColor }};" title="{{ $attr['value_name'] }}" aria-hidden="true"></span>
                  <span class="visually-hidden">{{ $attr['value_name'] }}</span>
                @else
                  {{ $attr['value_name'] }}
                @endif
                <br>
              @endforeach
            </div>
          @endif
        </div>
        <div>
          {{ $item->quantity }} × ${{ number_format($item->price, 2) }}
        </div>
      </div>
    @empty
      <p>No hay productos en el carrito.</p>
    @endforelse

    {{-- Subtotales y descuentos --}}
    <div class="cart-total border-top pt-2">
      <div class="d-flex justify-content-between">
        <span>Subtotal:</span>
        <span>${{ number_format($subtotal, 2) }}</span>
      </div>

      @if($coupon)
        <div class="d-flex justify-content-between align-items-center text-success">
          <div>
            <span>Cupón de Descuento ({{ $coupon['code'] }})</span>
            <form action="{{ route('cart.remove-coupon') }}" method="POST" class="d-inline">
              @csrf
              <button type="submit" class="btn btn-link btn-sm text-danger p-0 ms-2">[X]</button>
            </form>
          </div>
          <span>– ${{ number_format($discount, 2) }}</span>
        </div>
      @endif

      @if($requiresShipping && $selectedShipmentMethod)
        <div class="d-flex justify-content-between mt-2">
          <span>Envío ({{ $selectedShipmentMethod->name }})</span>
          <span>{{ (float) $shippingCost <= 0 ? 'Gratis' : '$' . number_format($shippingCost, 2) }}</span>
        </div>
      @elseif($requiresShipping)
        <div class="d-flex justify-content-between mt-2 text-body-secondary">
          <span>Envío</span>
          <span>Se calcula al seleccionar un método</span>
        </div>
      @endif

      @if($requiresShipping && $selectedShipmentMethod && $shippingDiscount > 0)
        <div class="d-flex justify-content-between text-success">
          <span>Descuento en envío</span>
          <span>– ${{ number_format($shippingDiscount, 2) }}</span>
        </div>
      @endif

      @if($requiresShipping && $selectedShipmentMethod && (int) ($packagePlan['package_count'] ?? 0) > 0)
        <div class="d-flex justify-content-between text-body-secondary">
          <span>Paquetes estimados</span>
          <span>{{ (int) $packagePlan['package_count'] }}</span>
        </div>
      @endif

      <div class="d-flex justify-content-between fw-bold border-top pt-2 mt-2">
        <span>{{ $requiresShipping && !$selectedShipmentMethod ? 'Total productos' : 'Total' }}:</span>
        <span>{{ $isFreeCheckout ? 'GRATIS' : '$' . number_format($finalTotal, 2) }}</span>
      </div>

      @if($requiresShipping && !$selectedShipmentMethod)
        <div class="small text-body-secondary mt-2">
          El total final puede variar según el método de envío elegido.
        </div>
      @elseif($isFreeCheckout)
        <div class="small text-success fw-semibold mt-2">
          No se solicitará pago para este pedido.
        </div>
      @endif
    </div>

      {{-- Formulario de cupón --}}
      @if(!$coupon)
      <div class="mt-3">
        <a class="text-muted" data-bs-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
          ¿Tenés un cupón de descuento?
        </a>
        
        <div class="collapse pt-4" id="collapseExample">
          <form method="POST" action="{{ route('cart.applyCoupon') }}">
            @csrf
            <div class="input-group">
              <input type="text" name="coupon_code" class="form-control" placeholder="Ingresá el código de cupón" required>
              <button type="submit" class="btn btn-primary">Usar</button>
            </div>
            @if(session('coupon_error'))
              <small class="text-danger">{{ session('coupon_error') }}</small>
            @endif
            @if(session('coupon_success'))
              <small class="text-success">{{ session('coupon_success') }}</small>
            @endif
          </form>
        </div> 
      </div>
      @endif    

  </div>
</div>
