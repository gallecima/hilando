@extends('layouts.front')

@section('title', 'Finalizar pago')
@section('body_class', 'page-hero checkout-page')

@section('content')
  @include('front.checkout.partials.page-header', [
    // 'title' => 'Finalizar pago',
    'subtitle' => 'Completá el último paso para registrar tu pedido.',
    // 'eyebrow' => 'Checkout',
    'breadcrumbs' => [
      ['label' => 'Inicio', 'url' => route('home')],
      ['label' => 'Productos', 'url' => route('category.show', 'todas')],
      ['label' => 'Finalizar pago'],
    ],
  ])

  <section class="checkout-flow-section">
    <div class="checkout-flow-shell">
      <div class="container">
        <div class="row g-4">
          <div class="col-lg-6">
            <div class="card shadow-sm front-form-card">
              <div class="card-body">
                <h2 class="h5">{{ $paymentMethod->name }}</h2>

                @if ($paymentMethod->instructions)
                  <p class="text-body-secondary">{!! nl2br(e($paymentMethod->instructions)) !!}</p>
                @endif

                <form method="POST" action="{{ route('front.checkout.finalize') }}" enctype="multipart/form-data" class="js-checkout-finalize-form">
                  @csrf
                  <input type="hidden" name="payment_method_id" value="{{ $paymentMethod->id }}">

                  @php $config = $paymentMethod->config ?? []; @endphp

                  @if (($config['file-upload'] ?? 'false') === 'true')
                    <div class="mb-3">
                      <label for="comprobante_{{ $paymentMethod->id }}" class="form-label">Subí tu comprobante de pago</label>
                      <input type="file" class="form-control" name="comprobante" id="comprobante_{{ $paymentMethod->id }}" required>
                    </div>
                  @endif

                  <div class="row mt-4 g-2">
                    <div class="col-sm-6">
                      <a href="{{ route('front.checkout.payment') }}" class="btn btn-outline-secondary w-100">Atrás</a>
                    </div>
                    <div class="col-sm-6">
                      <button type="submit" class="btn btn-primary w-100 js-checkout-finalize-btn" data-loading-text="Procesando pago...">
                        <span class="spinner-border spinner-border-sm me-2 js-btn-spinner d-none" role="status" aria-hidden="true"></span>
                        <span class="js-btn-label">Finalizar</span>
                      </button>
                    </div>
                  </div>

                  @if ($errors->any())
                    <div class="alert alert-danger mt-4 mb-0">
                      <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                          <li>{{ $error }}</li>
                        @endforeach
                      </ul>
                    </div>
                  @endif
                </form>
              </div>
            </div>
          </div>

          <div class="col-lg-6">
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
      document.querySelectorAll('.js-checkout-finalize-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
          const button = form.querySelector('.js-checkout-finalize-btn');
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
    });
  </script>
@endsection
