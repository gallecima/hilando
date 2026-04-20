@extends('layouts.front')

@section('title', 'Mis pedidos')
@section('body_class', 'page-hero account-page')

@section('content')
  @include('front.partials.page-header', [
    'variant' => 'hero',
    'title' => 'Mis pedidos',
    'subtitle' => 'Revisá el historial de compras y el estado de cada pedido.',
    'breadcrumbs' => [
      ['label' => 'Inicio', 'url' => route('home')],
      ['label' => 'Mi cuenta', 'url' => route('front.mi-cuenta.index')],
      ['label' => 'Mis pedidos'],
    ],
    'slides' => $checkoutHeroSlides ?? collect(),
    'backgroundImage' => $checkoutHeroBackgroundImage ?? null,
    'heroId' => 'accountOrdersHeroCarousel',
  ])

  <section class="checkout-flow-section">
    <div class="checkout-flow-shell">
      <div class="container">
      <div class="row g-4">
        <div class="col-lg-3">
          @include('front.partials.account-nav')
        </div>

        <div class="col-lg-9">
          <div class="card shadow-sm front-form-card">
            <div class="card-body">
              @if($orders->count())
                <div class="table-responsive">
                  <table class="table align-middle mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Total</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($orders as $order)
                        <tr>
                          <td>#{{ $order->id }}</td>
                          <td>{{ $order->created_at->format('d/m/Y') }}</td>
                          <td>{{ ucfirst($order->status) }}</td>
                          <td>${{ number_format($order->total, 2, ',', '.') }}</td>
                          <td><a href="{{ route('front.mi-cuenta.pedido', $order) }}" class="btn btn-sm btn-primary">Ver</a></td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                  {{ $orders->links('front.partials.pagination-bootstrap') }}
                </div>
              @else
                <p class="mb-0">No hay pedidos aún.</p>
              @endif
            </div>
          </div>
        </div>
      </div>
      </div>
    </div>
  </section>
@endsection
