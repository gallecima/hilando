@if ($items->isEmpty())
  <div class="alert alert-secondary" role="alert">
    Tu carrito está vacío.
  </div>
@else
  @php
    $tribunoSub = (array) session('tribuno.subscription', []);
  @endphp

  @if(($tribunoSub['applied'] ?? false) === true)
    <div class="alert alert-success py-2 px-3 small mb-2">
      <strong>Club El Tribuno:</strong> descuento aplicado
      @if((float)($tribunoSub['savings'] ?? 0) > 0)
        — Ahorrás ${{ number_format((float)$tribunoSub['savings'], 2) }}
      @endif
    </div>
  @endif

  @php
    $total = $items->sum(fn($item) => $item->price * $item->quantity);
  @endphp

  <div>
    @foreach ($items as $item)
      <div class="border-bottom pb-2 mb-2">
        <div class="d-flex justify-content-between mb-2">
                  <div class="d-flex justify-content-between">
                    <img src="{{ $item->product->featured_image ? asset('storage/' . $item->product->featured_image) : asset('images/logo.svg') }}" width="50" height="50" class="me-2 rounded" alt="{{ $item->name }}">
                    <div>
                      <strong>{{ $item->quantity }} x {{ $item->name }}</strong><br>
                      ${{ number_format($item->price * $item->quantity, 2) }}

                      @php
                          $atributos = json_decode($item->attribute_values_json);
                      @endphp
                      @if (!empty($atributos))
                        <ul class="mb-1 small text-muted">
                            @foreach ($atributos as $attr)
                                @php
                                  $hexColor = \App\Models\AttributeValue::normalizeHexColor($attr->value_name ?? null);
                                @endphp
                                <li>
                                  {{ $attr->attribute_name }}:
                                  @if($hexColor)
                                    <span class="attribute-inline-swatch ms-1" style="background-color: {{ $hexColor }};" title="{{ $attr->value_name }}" aria-hidden="true"></span>
                                    <span class="visually-hidden">{{ $attr->value_name }}</span>
                                  @else
                                    {{ $attr->value_name }}
                                  @endif
                                </li>
                            @endforeach
                        </ul>
                      @endif                      
                    </div>
                  </div>
                  <form method="POST" action="{{ route('cart.remove') }}">
                    @csrf
                    <input type="hidden" name="item_id" value="{{ $item->id }}">
                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                      <i class="fa-solid fa-x"></i>
                    </button>
                  </form>        
        </div>





      </div>
    @endforeach
  </div>

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

  <div class="pt-2">
    <div class="d-flex justify-content-between">
      <span>Subtotal:</span>
      <span>${{ number_format($total, 2) }}</span>
    </div>

    @if($coupon)
      <div class="d-flex justify-content-between text-success">
        <span>Descuento ({{ $coupon['code'] }})</span>
        <span>– ${{ number_format($discount, 2) }}</span>
      </div>
    @endif

    <div class="d-flex justify-content-between fw-bold border-top pt-2">
      <span>{{ ($requiresShipping ?? false) ? 'Total productos:' : 'Total:' }}</span>
      <span>{{ $isFreeCheckout ? 'GRATIS' : '$' . number_format($productsTotal, 2) }}</span>
    </div>
  </div>

  <div class="mt-3 d-grid gap-2 text-center">
    @if(($requiresShipping ?? false) === true)
      <div class="small text-body-secondary">El envío se calculará en checkout.</div>
    @elseif($isFreeCheckout)
      <div class="small text-success fw-semibold">Pedido sin cargo: no se solicitará pago.</div>
    @endif
    <a href="{{ route('front.checkout.index') }}" class="btn btn-primary w-100">{{ (!$requiresShipping && $isFreeCheckout) ? 'Continuar gratis' : 'Iniciar compra' }}</a>
    <a href="{{ route('cart.index') }}" class="fw-light"><small>Ver detalle del carrito</small></a>
  </div>
@endif
