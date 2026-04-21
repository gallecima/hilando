@extends('layouts.front')

@section('title', 'Método de envío')
@section('body_class', 'page-hero checkout-page')

@section('content')
  @include('front.checkout.partials.page-header', [
    // 'title' => 'Seleccionar método de envío',
    'subtitle' => 'Elegí la opción de envío disponible para este pedido.',
    // 'eyebrow' => 'Checkout',
    'breadcrumbs' => [
      ['label' => 'Inicio', 'url' => route('home')],
      ['label' => 'Productos', 'url' => route('category.show', 'todas')],
      ['label' => 'Método de envío'],
    ],
  ])

  <section class="checkout-flow-section">
    <div class="checkout-flow-shell">
      <div class="container">
        <div class="row g-4">
          <div class="col-lg-6">
          @php
            $selectedShipmentId = (int) old('shipment_method_id', (int) session('checkout.shipment_method_id'));
            if ($selectedShipmentId <= 0 && $shipmentMethods->count() === 1) {
              $selectedShipmentId = (int) $shipmentMethods->first()->id;
            }
            $openShippingData = ($selectedShipmentId > 0) || $errors->any();
            $sessionShip = (object) (session('guest_checkout.shipping') ?? []);
            $destCity = $sessionShip->city ?? (optional(optional($customer ?? null)->address)->city ?? '');
            $destProv = $sessionShip->province ?? (optional(optional($customer ?? null)->address)->province ?? '');
            $destPc = $sessionShip->postal_code ?? (optional(optional($customer ?? null)->address)->postal_code ?? '');
            $destLabel = trim(implode(', ', array_filter([$destCity, $destProv])));
          @endphp



            <div class="card shadow-sm front-form-card">
              <div class="card-header" style="background-color: rgba(128, 128, 128, 1) !important; color: #FFF;">
                <strong>Método de Envío</strong>
              </div>              
              <div class="card-body">


            @if($destLabel || $destPc)
              <div class="alert alert-info rounded-pill py-2 px-3 small" style="color:#000; background-color:rgba(128,128,128,.1); border: #ece6e0">
                <strong>Destino:</strong> {{ $destLabel ?: '—' }} @if($destPc) (CP {{ $destPc }}) @endif
                — <a href="{{ route('front.checkout.index') }}" class="alert-link">Modificar</a>
              </div>
            @endif

                <form id="shipment-form" method="POST" action="{{ route('front.checkout.shipment.store') }}">
                  @csrf

                  {!! hook('front:checkout:shipment.fields') !!}

                  <div class="list-group mb-4">
                    @forelse($shipmentMethods as $method)
                      <label class="list-group-item">
                        <div class="form-check">
                          <input
                            type="radio"
                            name="shipment_method_id"
                            value="{{ $method->id }}"
                            class="form-check-input shipment-radio"
                            data-method-name="{{ $method->name }}"
                            {{ (int) $selectedShipmentId === (int) $method->id ? 'checked' : '' }}
                            required
                          >
                          <label class="form-check-label w-100">
                            <span class="fw-semibold">{{ $method->name }}</span>
                            @if($method->amount == 0)
                              <span class="text-success ms-2">Envío gratis</span>
                            @else
                              <span class="text-body-secondary ms-2">${{ number_format($method->amount, 2, ',', '.') }}</span>
                            @endif

                            @php
                              $baseAmount = $productsTotal;
                              $shippingDiscount = 0;
                              if (in_array((string) $method->discount_type, ['percent', 'percentage'], true)) {
                                $shippingDiscount = round($baseAmount * ($method->discount_value / 100), 2);
                              } elseif (in_array((string) $method->discount_type, ['fixed', 'amount'], true)) {
                                $shippingDiscount = round($method->discount_value, 2);
                              }
                            @endphp

                            @if($shippingDiscount > 0)
                              <div class="small text-success mt-1">Descuento aplicado: -${{ number_format($shippingDiscount, 2, ',', '.') }}</div>
                            @endif
                            @if(($method->destination_match_type ?? 'exact') === 'nearby')
                              <div class="small text-warning mt-1">
                                Sugerido por cercanía
                                @if($method->destination_point_name)
                                  · Punto: {{ $method->destination_point_name }}
                                @endif
                                @if($method->destination_distance_km !== null)
                                  · {{ number_format((float) $method->destination_distance_km, 1, ',', '.') }} km
                                @endif
                              </div>
                            @elseif($method->destination_point_name)
                              <div class="small text-body-secondary mt-1">Punto asociado: {{ $method->destination_point_name }}</div>
                            @endif
                            @if((int) data_get($method, 'shipping_package_plan.package_count', 0) > 0)
                              <div class="small text-body-secondary mt-1">
                                Empaquetado estimado: {{ (int) data_get($method, 'shipping_package_plan.package_count', 0) }}
                                {{ (int) data_get($method, 'shipping_package_plan.package_count', 0) === 1 ? 'caja' : 'cajas' }}
                              </div>
                            @endif
                            @if($method->delay)
                              <div class="small text-body-secondary mt-1">Demora estimada: {{ $method->delay }}</div>
                            @endif
                          </label>
                        </div>
                      </label>
                    @empty
                      <div class="list-group-item text-body-secondary">
                        No hay métodos de envío configurados para este destino.
                      </div>
                    @endforelse
                  </div>

                  {!! hook('front:checkout:shipment.widgets') !!}

                  <div id="shipping-data-wrapper" class="{{ $openShippingData ? '' : 'd-none' }}">
                    <hr class="my-4">
                    <h2 class="h5 mb-3">Datos de envío</h2>
                    @include('front.checkout.partials.shipping-billing-fields')
                  </div>

                  <div class="row mt-4 g-2">
                    <div class="col-sm-6">
                      <a href="{{ route('front.checkout.index') }}" class="btn btn-outline-secondary rounded-pill w-100">Atrás</a>
                    </div>
                    <div class="col-sm-6">
                      <button type="submit" class="btn btn-secondary rounded-pill w-100" id="btn-continue" {{ $openShippingData ? '' : 'disabled' }}>Continuar</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <div class="col-lg-6">
            @include('front.checkout.partials.cart-summary')
          </div>
        </div>

        {!! hook('front:checkout:shipment.scripts') !!}
      </div>
    </div>
  </section>
@endsection

@section('scripts')
  @include('front.checkout.partials.shipping-billing-scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const form = document.getElementById('shipment-form');
      const wrapper = document.getElementById('shipping-data-wrapper');
      const button = document.getElementById('btn-continue');

      if (!form || !wrapper) return;

      function setEnabled(isEnabled) {
        wrapper.classList.toggle('d-none', !isEnabled);
        wrapper.querySelectorAll('input, select, textarea, button').forEach(function (element) {
          if (element.closest('#billing-section')) return;
          element.disabled = !isEnabled;
        });
        if (button) button.disabled = !isEnabled;

        const billingToggle = document.getElementById('modify_billing');
        if (isEnabled && billingToggle) {
          billingToggle.dispatchEvent(new Event('change'));
        }
      }

      function hasShipmentSelected() {
        return !!form.querySelector('input[name="shipment_method_id"]:checked');
      }

      setEnabled(hasShipmentSelected());

      form.addEventListener('change', function (event) {
        if (event.target && event.target.matches('input[name="shipment_method_id"]')) {
          setEnabled(true);
        }
      });

      let tries = 0;
      const timer = setInterval(function () {
        tries++;
        if (hasShipmentSelected()) {
          setEnabled(true);
          clearInterval(timer);
        }
        if (tries >= 25) clearInterval(timer);
      }, 300);
    });
  </script>
@endsection
