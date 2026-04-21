@extends('layouts.front')

@section('title', 'Mi carrito')
@section('body_class', 'page-hero checkout-page')
@section('inline_flash_messages', '1')

@section('content')
  @include('front.checkout.partials.page-header', [
    // 'title' => 'Mi carrito',
    'subtitle' => 'Revisá los productos antes de avanzar al checkout.',
    // 'eyebrow' => 'Checkout',
    'breadcrumbs' => [
      ['label' => 'Inicio', 'url' => route('home')],
      ['label' => 'Carrito'],
    ],
  ])

  <section class="checkout-flow-section">
    <div class="checkout-flow-shell">
      <div class="container">
        @include('front.partials.flash-messages', ['wrapperClass' => 'mb-4'])


      @if($items->count())
        <div class="row g-4">
          <div class="col-lg-8">
            <div class="card shadow-sm front-form-card">
              <div class="table-responsive">
                <table class="table align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>Producto</th>
                      <th>Cantidad</th>
                      <th>Precio unitario</th>
                      <th>Total</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($items as $item)
                      @php
                        $attributes = json_decode($item->attribute_values_json);
                        $stock = $item->product->stock;
                        $resolvedMinQuantity = $item->product ? $item->product->resolveMinQuantity(auth('customer')->user()) : 1;
                        $isWholesalePricing = $item->product ? $item->product->usesWholesalePricing(auth('customer')->user()) : false;

                        if (!empty($attributes) && count($attributes) === 1) {
                          $valueId = $attributes[0]->value_id;
                          $value = $item->product->attributeValues()->where('attribute_value_id', $valueId)->first();
                          if ($value && $value->pivot && $value->pivot->stock !== null) {
                            $stock = $value->pivot->stock;
                          }
                        }
                      @endphp

                      <tr>
                        <td>
                          <strong>{{ $item->name }}</strong>
                          @if (!empty($attributes))
                            <ul class="small text-body-secondary mb-0 mt-2">
                              @foreach ($attributes as $attribute)
                                @php
                                  $hexColor = \App\Models\AttributeValue::normalizeHexColor($attribute->value_name ?? null);
                                @endphp
                                <li>
                                  {{ $attribute->attribute_name }}:
                                  @if($hexColor)
                                    <span class="attribute-inline-swatch ms-1" style="background-color: {{ $hexColor }};" title="{{ $attribute->value_name }}" aria-hidden="true"></span>
                                    <span class="visually-hidden">{{ $attribute->value_name }}</span>
                                  @else
                                    {{ $attribute->value_name }}
                                  @endif
                                </li>
                              @endforeach
                            </ul>
                          @endif
                        </td>
                        <td>
                          <div class="d-flex align-items-center gap-2">
                            <input
                              type="number"
                              data-itemid="{{ $item->id }}"
                              data-stock="{{ $stock }}"
                              data-min="{{ $resolvedMinQuantity }}"
                              class="form-control form-control-sm quantity-input"
                              value="{{ $item->quantity }}"
                              min="{{ $resolvedMinQuantity }}"
                              max="{{ max((int) $stock, 1) }}"
                            >
                            <button type="button" class="btn btn-sm btn-outline-primary update-cart-btn" data-itemid="{{ $item->id }}">
                              Actualizar
                            </button>
                          </div>
                          <div class="small text-body-secondary mt-1">
                            Stock: {{ $stock }}
                            @if($isWholesalePricing || $resolvedMinQuantity > 1)
                              · mínimo {{ $resolvedMinQuantity }} {{ \Illuminate\Support\Str::plural('unidad', $resolvedMinQuantity) }}
                              @if($isWholesalePricing)
                                · precio mayorista
                              @endif
                            @endif
                          </div>
                        </td>
                        <td>${{ number_format($item->price, 2, ',', '.') }}</td>
                        <td>${{ number_format($item->price * $item->quantity, 2, ',', '.') }}</td>
                        <td>
                          <form method="POST" action="{{ route('cart.remove') }}">
                            @csrf
                            <input type="hidden" name="item_id" value="{{ $item->id }}">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Quitar</button>
                          </form>
                        </td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="col-lg-4">
            @php
              $discount = 0;
              $coupon = session('discount_coupon');
              if ($coupon) {
                $discount = $coupon['discount_type'] === 'percentage'
                  ? $total * ($coupon['discount_value'] / 100)
                  : $coupon['discount_value'];
              }
              $productsTotal = max(0, $total - $discount);
              $isFreeCheckout = !($requiresShipping ?? false) && round((float) $productsTotal, 2) <= 0.0;
            @endphp

            <div class="card shadow-sm">
              <div class="card-body">
                <h2 class="h5">Resumen</h2>

                <div class="d-flex justify-content-between mb-2">
                  <span>Subtotal</span>
                  <strong>${{ number_format($total, 2, ',', '.') }}</strong>
                </div>

                @if($discount > 0)
                  <div class="d-flex justify-content-between mb-2 text-success">
                    <span>Descuento ({{ $coupon['code'] }})</span>
                    <strong>- ${{ number_format($discount, 2, ',', '.') }}</strong>
                  </div>
                @endif

                <div class="d-flex justify-content-between border-top pt-3 mt-3">
                  <span>{{ ($requiresShipping ?? false) ? 'Total productos' : 'Total' }}</span>
                  <strong>{{ $isFreeCheckout ? 'GRATIS' : '$' . number_format($productsTotal, 2, ',', '.') }}</strong>
                </div>

                @if(!$coupon)
                  <form method="POST" action="{{ route('cart.applyCoupon') }}" class="mt-4">
                    @csrf
                    <label for="coupon_code" class="form-label">Cupón</label>
                    <div class="input-group">
                      <input type="text" id="coupon_code" name="coupon_code" class="form-control" placeholder="Código de cupón" required>
                      <button type="submit" class="btn btn-outline-secondary">Aplicar</button>
                    </div>
                    @if(session('coupon_error'))
                      <div class="small text-danger mt-2">{{ session('coupon_error') }}</div>
                    @endif
                    @if(session('coupon_success'))
                      <div class="small text-success mt-2">{{ session('coupon_success') }}</div>
                    @endif
                  </form>
                @else
                  <form action="{{ route('cart.remove-coupon') }}" method="POST" class="mt-4">
                    @csrf
                    <button class="btn btn-outline-danger w-100">Quitar cupón</button>
                  </form>
                @endif

                @if(($requiresShipping ?? false) === true)
                  <div class="small text-body-secondary mt-3">
                    El costo de envío se calculará en checkout según el método seleccionado.
                  </div>
                @elseif($isFreeCheckout)
                  <div class="small text-success fw-semibold mt-3">Este pedido no tiene costo. Vas a continuar sin pago.</div>
                @endif

                <div class="d-grid gap-2 mt-4">
                  <a href="{{ route('front.checkout.index') }}" class="btn btn-primary">{{ (!$requiresShipping && $isFreeCheckout) ? 'Continuar gratis' : 'Iniciar compra' }}</a>
                  <a href="{{ route('category.show', 'todas') }}" class="btn btn-outline-secondary">Seguir comprando</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      @else
        <div class="alert alert-info">
          Tu carrito está vacío. <a href="{{ route('category.show', 'todas') }}">Volver a la tienda</a>.
        </div>
      @endif
      </div>
    </div>
  </section>
@endsection

@section('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('.update-cart-btn').forEach(function (button) {
        button.addEventListener('click', function () {
          const itemId = this.dataset.itemid;
          const input = document.querySelector('.quantity-input[data-itemid="' + itemId + '"]');
          const quantity = parseInt(input?.value || '1', 10);
          const stock = parseInt(input?.dataset.stock || '1', 10);
          const min = parseInt(input?.dataset.min || input?.min || '1', 10);

          if (!input || Number.isNaN(quantity) || quantity < min || quantity > stock) {
            alert('La cantidad seleccionada no es válida para este acceso.');
            return;
          }

          const formData = new FormData();
          formData.append('item_id', itemId);
          formData.append('quantity', String(quantity));

          fetch(@json(route('cart.update')), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: formData
          })
            .then(function (response) { return response.json(); })
            .then(function (payload) {
              if (payload.success !== true) {
                throw new Error('No se pudo actualizar el carrito.');
              }

              window.frontStore?.updateCartBadge?.();
              window.location.reload();
            })
            .catch(function () {
              alert('No se pudo actualizar la cantidad.');
            });
        });
      });
    });
  </script>
@endsection
