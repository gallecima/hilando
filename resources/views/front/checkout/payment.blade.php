@extends('layouts.front')

@section('title', 'Método de pago')
@section('body_class', 'page-hero checkout-page')

@section('content')
  @include('front.checkout.partials.page-header', [
    // 'title' => ($isFreeCheckout ?? false) ? 'Finalizar compra' : 'Seleccionar método de pago',
    'subtitle' => ($isFreeCheckout ?? false) ? 'El pedido no requiere pago adicional.' : 'Elegí cómo querés abonar este pedido.',
    // 'eyebrow' => 'Checkout',
    'breadcrumbs' => [
      ['label' => 'Inicio', 'url' => route('home')],
      ['label' => 'Productos', 'url' => route('category.show', 'todas')],
      ['label' => 'Pago'],
    ],
  ])

  <section class="checkout-flow-section">
    <div class="checkout-flow-shell">
      <div class="container">
        <div class="row g-4">
          <div class="col-lg-6 order-2 order-lg-1">
            <div class="card shadow-sm front-form-card">
              <div class="card-body">
                @if (($isFreeCheckout ?? false) === true)
                  <div class="alert alert-success">
                    Tu cupón cubre el 100% del pedido. No hace falta seleccionar un medio de pago.
                  </div>

                  <form method="POST" action="{{ route('front.checkout.finalize') }}" class="js-checkout-finalize-form">
                    @csrf
                    <button type="submit" class="btn btn-primary w-100 js-checkout-finalize-btn" data-loading-text="Procesando compra...">
                      <span class="spinner-border spinner-border-sm me-2 js-btn-spinner d-none" role="status" aria-hidden="true"></span>
                      <span class="js-btn-label">Finalizar compra</span>
                    </button>
                  </form>

                  <a href="{{ $paymentBackRoute }}" class="btn btn-outline-secondary w-100 mt-3">Atrás</a>
                @else
                  @if ($paymentMethods->isEmpty())
                    <div class="alert alert-warning mb-0">No hay métodos de pago disponibles.</div>
                  @else
                    {!! hook('front:checkout:payment.methods') !!}

                    @if($paymentMethodsCount > 0)
                      <form method="POST" action="{{ route('front.checkout.payment.choose') }}">
                        @csrf
                        <div class="list-group">
                          @foreach ($paymentMethods as $method)
                            @if($method->type != 'plugin')
                              <label class="list-group-item js-payment-method-item">
                                <div class="form-check">
                                  <input
                                    type="radio"
                                    name="payment_method_id"
                                    value="{{ $method->id }}"
                                    class="form-check-input js-payment-method-radio"
                                    required
                                    {{ old('payment_method_id') == $method->id ? 'checked' : '' }}
                                  >
                                  <label class="form-check-label">
                                    <span class="fw-semibold">{{ $method->name }}</span>
                                    @if ($method->instructions)
                                      <div class="small text-body-secondary mt-1">{!! nl2br(e($method->instructions)) !!}</div>
                                    @endif
                                  </label>
                                </div>
                              </label>
                            @endif
                          @endforeach
                        </div>

                        <div class="row mt-4 g-2">
                          <div class="col-sm-6">
                            <a href="{{ $paymentBackRoute }}" class="btn btn-outline-secondary w-100">Atrás</a>
                          </div>
                          <div class="col-sm-6">
                            <button type="submit" class="btn btn-primary w-100">Continuar</button>
                          </div>
                        </div>
                      </form>
                    @else
                      <a href="{{ $paymentBackRoute }}" class="btn btn-outline-secondary w-100">Atrás</a>
                    @endif
                  @endif
                @endif

                @if ($errors->any())
                  <div class="alert alert-danger mt-4 mb-0">
                    <ul class="mb-0">
                      @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                      @endforeach
                    </ul>
                  </div>
                @endif
              </div>
            </div>
          </div>

          <div class="col-lg-6 order-1 order-lg-2">
            @include('front.checkout.partials.cart-summary')
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection

@section('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      function bindLoadingSubmit(formSelector, buttonSelector) {
        document.querySelectorAll(formSelector).forEach(function (form) {
          form.addEventListener('submit', function (event) {
            const button = form.querySelector(buttonSelector);
            if (!button) return;

            if (button.dataset.submitting === '1') {
              event.preventDefault();
              return;
            }

            button.dataset.submitting = '1';
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');

            const spinner = button.querySelector('.js-btn-spinner');
            if (spinner) spinner.classList.remove('d-none');

            const label = button.querySelector('.js-btn-label');
            if (label) label.textContent = button.dataset.loadingText || 'Procesando...';
          });
        });
      }

      bindLoadingSubmit('.js-checkout-finalize-form', '.js-checkout-finalize-btn');
      bindLoadingSubmit('.js-mp-pay-form', '.js-mp-pay-btn');
    });
  </script>
@endsection
