@extends('layouts.front')

@section('title', 'Checkout')
@section('body_class', 'page-hero checkout-page')

@section('content')
  @include('front.checkout.partials.page-header', [
    // 'title' => ($isDigitalCheckout ?? true) ? 'Iniciar compra' : 'Datos de envío y facturación',
    'subtitle' => ($isDigitalCheckout ?? true)
      ? 'Completá tus datos para continuar al pago.'
      : 'Completá la información necesaria para calcular el envío y seguir al pago.',
    // 'eyebrow' => 'Checkout',
    'breadcrumbs' => [
      ['label' => 'Inicio', 'url' => route('home')],
      ['label' => 'Productos', 'url' => route('category.show', 'todas')],
      ['label' => ($isDigitalCheckout ?? true) ? 'Checkout' : 'Datos de envío'],
    ],
  ])

  <section class="checkout-flow-section">
    <div class="checkout-flow-shell">
      <div class="container">
      @php
        $isCustomer = isset($customer) && $customer;
        $gs = (object) ($guestShipping ?? []);
        $gb = (object) (session('guest_checkout.billing') ?? []);

        $prefillEmail = old('email', $isCustomer ? ($customer->email ?? '') : ($gs->email ?? ''));
        $prefillDni = old('document_number', $isCustomer ? ($customer->document ?? '') : ($gs->document_number ?? ''));
        $prefillName = old('name', $isCustomer ? ($customer->name ?? '') : ($gs->name ?? ''));
        $prefillPhone = old('phone', $isCustomer ? ($customer->phone ?? '') : ($gs->phone ?? ''));

        $billingOpen =
          (bool) old('modify_billing', false) ||
          $errors->has('billing_name') ||
          $errors->has('document') ||
          $errors->has('tax_status');

        $prefillBillingName = old(
          'billing_name',
          $isCustomer
            ? optional($customer->billingData)->business_name ?? ($customer->name ?? '')
            : ($gb->business_name ?? $prefillName ?? '')
        );
        $prefillBillingDoc = old(
          'document',
          $isCustomer
            ? optional($customer->billingData)->document_number ?? ($customer->document ?? '')
            : ($gb->document_number ?? $prefillDni ?? '')
        );
        $prefillTaxStatus = old(
          'tax_status',
          $isCustomer
            ? optional($customer->billingData)->tax_status ?? 'Consumidor Final'
            : ($gb->tax_status ?? 'Consumidor Final')
        );
      @endphp

        <div class="row g-4">
        <div class="col-lg-6 order-2 order-lg-1">
          <div class="card shadow-sm front-form-card">
            <div class="card-body">
              @if(($isDigitalCheckout ?? true) === true)
                @if (session('error'))
                  <div class="alert alert-danger py-2 px-3 small">{{ session('error') }}</div>
                @endif

                <div class="alert alert-info py-2 px-3 small">
                  Esta compra corresponde a productos descargables. Al finalizar, podrás ingresar con tu email y DNI.
                </div>

                @if(($isFreeCheckout ?? false) === true)
                  <div class="alert alert-success py-2 px-3 small">
                    Tu total actual es <strong>GRATIS</strong>. Al continuar, finalizarás sin pago.
                  </div>
                @endif

                <form method="POST" action="{{ route('front.checkout.guest') }}" id="checkout-start-form">
                  @csrf

                  <h2 class="h5 mb-3">Datos personales</h2>

                  <div class="mb-3">
                    <label for="checkout_email" class="form-label">Correo electrónico</label>
                    <input type="email" name="email" id="checkout_email" class="form-control" value="{{ $prefillEmail }}" required>
                    @error('email') <div class="small text-danger mt-1">{{ $message }}</div> @enderror
                  </div>

                  <div class="mb-3">
                    <label for="checkout_document_number" class="form-label">DNI</label>
                    <input
                      type="text"
                      name="document_number"
                      id="checkout_document_number"
                      class="form-control js-dni"
                      value="{{ $prefillDni }}"
                      required
                      inputmode="numeric"
                      placeholder="12.345.678"
                      maxlength="10"
                      pattern="\d{2}\.\d{3}\.\d{3}"
                    >
                    @error('document_number') <div class="small text-danger mt-1">{{ $message }}</div> @enderror
                  </div>

                  <div class="mb-3">
                    <label for="name" class="form-label">Nombre completo</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ $prefillName }}" required>
                    @error('name') <div class="small text-danger mt-1">{{ $message }}</div> @enderror
                  </div>

                  <div class="mb-3">
                    <label for="phone" class="form-label">Teléfono</label>
                    <input type="text" name="phone" id="phone" class="form-control" value="{{ $prefillPhone }}">
                    @error('phone') <div class="small text-danger mt-1">{{ $message }}</div> @enderror
                  </div>

                  <div class="form-check mt-4 mb-2">
                    <input class="form-check-input" type="checkbox" value="1" id="modify_billing" name="modify_billing" {{ $billingOpen ? 'checked' : '' }}>
                    <label class="form-check-label" for="modify_billing">
                      Modificar datos de facturación
                    </label>
                  </div>

                  <div id="billing-section" class="{{ $billingOpen ? '' : 'd-none' }}">
                    <h2 class="h5 mt-3 mb-3">Datos fiscales</h2>

                    <div class="mb-3">
                      <label for="billing_name" class="form-label">Razón social / Nombre</label>
                      <input type="text" name="billing_name" id="billing_name" class="form-control" value="{{ $prefillBillingName }}" {{ $billingOpen ? '' : 'disabled' }}>
                      @error('billing_name') <div class="small text-danger mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                      <label for="document" class="form-label">CUIT / DNI</label>
                      <input type="text" name="document" id="document" class="form-control js-fiscal-document" value="{{ $prefillBillingDoc }}" {{ $billingOpen ? '' : 'disabled' }}>
                      @error('document') <div class="small text-danger mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                      <label for="tax_status" class="form-label">Condición fiscal</label>
                      <select name="tax_status" id="tax_status" class="form-select" {{ $billingOpen ? '' : 'disabled' }}>
                        <option value="Consumidor Final" {{ $prefillTaxStatus === 'Consumidor Final' ? 'selected' : '' }}>Consumidor Final</option>
                        <option value="Monotributista" {{ $prefillTaxStatus === 'Monotributista' ? 'selected' : '' }}>Monotributista</option>
                        <option value="Responsable Inscripto" {{ $prefillTaxStatus === 'Responsable Inscripto' ? 'selected' : '' }}>Responsable Inscripto</option>
                        <option value="Exento" {{ $prefillTaxStatus === 'Exento' ? 'selected' : '' }}>Exento</option>
                      </select>
                      @error('tax_status') <div class="small text-danger mt-1">{{ $message }}</div> @enderror
                    </div>
                  </div>

                  <button type="submit" class="btn btn-primary w-100 mt-4" id="btn-continue">
                    {{ ($isFreeCheckout ?? false) ? 'Continuar sin pago' : 'Continuar al pago' }}
                  </button>
                </form>
              @else
                @if (session('error'))
                  <div class="alert alert-danger py-2 px-3 small">{{ session('error') }}</div>
                @endif

                <div class="alert alert-info py-2 px-3 small">
                  Completá tu dirección para calcular el envío disponible y continuar al pago.
                </div>

                <form method="POST" action="{{ route('front.checkout.personal_data.store') }}" id="checkout-start-form">
                  @csrf
                  @include('front.checkout.partials.shipping-billing-fields')
                  <button type="submit" class="btn btn-primary w-100 mt-4">Seleccionar envío</button>
                </form>
              @endif
            </div>
          </div>
        </div>

        <div class="col-lg-6 order-1 order-lg-2">
          <div id="checkout-cart-summary">
            @include('front.checkout.partials.cart-summary')
          </div>
        </div>
        </div>
      </div>
    </div>
  </section>
@endsection

@section('scripts')
  @if(($isDigitalCheckout ?? true) === true)
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const modifyBillingToggle = document.getElementById('modify_billing');
        const billingSection = document.getElementById('billing-section');

        function setBillingOpen(isOpen) {
          if (!billingSection) return;
          billingSection.classList.toggle('d-none', !isOpen);
          billingSection.querySelectorAll('input, select, textarea').forEach(function (element) {
            element.disabled = !isOpen;
          });
        }

        if (modifyBillingToggle && billingSection) {
          setBillingOpen(!!modifyBillingToggle.checked);
          modifyBillingToggle.addEventListener('change', function () {
            setBillingOpen(!!this.checked);
          });
        }

        const dniInputs = document.querySelectorAll('input.js-dni');
        const dniRegex = /^\d{2}\.\d{3}\.\d{3}$/;

        function formatDni(value) {
          const digits = String(value || '').replace(/\D/g, '').slice(0, 8);
          if (digits.length <= 2) return digits;
          if (digits.length <= 5) return digits.slice(0, 2) + '.' + digits.slice(2);
          return digits.slice(0, 2) + '.' + digits.slice(2, 5) + '.' + digits.slice(5);
        }

        dniInputs.forEach(function (input) {
          function sync() {
            const formatted = formatDni(input.value);
            if (input.value !== formatted) input.value = formatted;
          }

          input.addEventListener('input', function () {
            input.setCustomValidity('');
            sync();
          });

          input.addEventListener('blur', function () {
            if (!input.value) return;
            if (!dniRegex.test(input.value)) {
              input.setCustomValidity('Ingresá el DNI con formato NN.NNN.NNN');
            } else {
              input.setCustomValidity('');
            }
          });

          input.addEventListener('invalid', function () {
            if (input.validity.patternMismatch) {
              input.setCustomValidity('Ingresá el DNI con formato NN.NNN.NNN');
            } else {
              input.setCustomValidity('');
            }
          });

          sync();
        });
      });
    </script>
  @else
    @include('front.checkout.partials.shipping-billing-scripts')
  @endif
@endsection
