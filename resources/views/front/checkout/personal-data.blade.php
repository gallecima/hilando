@extends('layouts.front')

@section('title', 'Datos de envío')
@section('body_class', 'page-hero checkout-page')

@section('content')
  @include('front.checkout.partials.page-header', [
    // 'title' => 'Datos de envío y facturación',
    'subtitle' => 'Completá la información necesaria para seguir con el checkout.',
    // 'eyebrow' => 'Checkout',
    'breadcrumbs' => [
      ['label' => 'Inicio', 'url' => route('home')],
      ['label' => 'Productos', 'url' => route('category.show', 'todas')],
      ['label' => 'Datos de envío'],
    ],
  ])

  <section class="checkout-flow-section">
    <div class="checkout-flow-shell">
      <div class="container">
        <div class="row g-4">
          <div class="col-lg-6">
            <div class="card shadow-sm front-form-card">
              <div class="card-body">
                <form method="POST" action="{{ route('front.checkout.personal_data.store') }}">
                  @csrf
                  @include('front.checkout.partials.shipping-billing-fields')
                  <button type="submit" class="btn btn-primary w-100 mt-3">Seleccionar envío</button>
                </form>
              </div>
            </div>
          </div>

          <div class="col-lg-6">
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
  @include('front.checkout.partials.shipping-billing-scripts')
@endsection
