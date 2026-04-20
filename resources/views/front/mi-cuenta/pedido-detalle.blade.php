@extends('layouts.front')

@section('title', 'Detalle del pedido')
@section('body_class', 'page-hero account-page')

@section('content')
  @include('front.partials.page-header', [
    'variant' => 'hero',
    'title' => 'Detalle del pedido',
    'subtitle' => 'Resumen completo del pedido seleccionado.',
    'breadcrumbs' => [
      ['label' => 'Inicio', 'url' => route('home')],
      ['label' => 'Mi cuenta', 'url' => route('front.mi-cuenta.index')],
      ['label' => 'Mis pedidos', 'url' => route('front.mi-cuenta.pedidos')],
      ['label' => 'Pedido #' . $order->id],
    ],
    'slides' => $checkoutHeroSlides ?? collect(),
    'backgroundImage' => $checkoutHeroBackgroundImage ?? null,
    'heroId' => 'accountOrderDetailHeroCarousel',
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
            <div class="card-header">
              Pedido #{{ $order->id }} - {{ $order->created_at->format('d/m/Y H:i') }}
            </div>
            <div class="card-body">
              <div class="row g-4">
                <div class="col-lg-4">
                  <h2 class="h5">Cliente</h2>
                  <p class="mb-1"><strong>Nombre:</strong> {{ $order->name }}</p>
                  <p class="mb-1"><strong>Email:</strong> {{ $order->email }}</p>
                  <p class="mb-0"><strong>Teléfono:</strong> {{ $order->phone ?? '-' }}</p>
                </div>

                <div class="col-lg-4">
                  @php $billingData = (array) ($order->billing_data_json ?? []); @endphp
                  <h2 class="h5">Facturación</h2>
                  <p class="mb-1"><strong>Razón social / Nombre:</strong> {{ $billingData['business_name'] ?? $order->name ?? '-' }}</p>
                  <p class="mb-1"><strong>Documento:</strong> {{ $billingData['document_number'] ?? '-' }}</p>
                  <p class="mb-0"><strong>Condición fiscal:</strong> {{ $billingData['tax_status'] ?? '-' }}</p>
                </div>

                <div class="col-lg-4">
                  <h2 class="h5">Pago</h2>
                  @if ($order->payment)
                    <p class="mb-1"><strong>Método:</strong> {{ $order->payment->method }}</p>
                    @if($order->payment->status)
                      <p class="mb-1"><strong>Estado:</strong> {{ ucfirst($order->payment->status) }}</p>
                    @endif
                    @if(!empty($order->payment->notes))
                      <p class="mb-1"><strong>Instrucciones:</strong> {{ $order->payment->notes }}</p>
                    @endif
                    @if(!empty($order->payment->payment_data['file']))
                      <p class="mb-0"><strong>Comprobante:</strong> <a href="{{ asset('storage/' . $order->payment->payment_data['file']) }}" target="_blank">Ver archivo</a></p>
                    @endif
                  @else
                    <p class="mb-0">Sin datos de pago.</p>
                  @endif
                </div>
              </div>

              <h2 class="h5 mt-4">Productos comprados</h2>
              <div class="table-responsive">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th>Producto</th>
                      <th>Atributo</th>
                      <th>Precio</th>
                      <th>Cantidad</th>
                      <th>Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($order->items as $item)
                      <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->attributeValue ? $item->attributeValue->value : '-' }}</td>
                        <td>${{ number_format($item->price, 2, ',', '.') }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>${{ number_format($item->total, 2, ',', '.') }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>

              <h2 class="h5 mt-4">Resumen</h2>
              <ul class="list-group">
                <li class="list-group-item d-flex justify-content-between"><span>Subtotal</span><strong>${{ number_format($order->subtotal, 2, ',', '.') }}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span>Descuento</span><strong>- ${{ number_format($order->discount ?? 0, 2, ',', '.') }}</strong></li>
                <li class="list-group-item d-flex justify-content-between"><span>Total</span><strong class="text-success">${{ number_format($order->total, 2, ',', '.') }}</strong></li>
              </ul>

              <h2 class="h5 mt-4">Descargas</h2>
              @if (!$orderPaid)
                <div class="alert alert-warning">Tu pago aún está pendiente. Las descargas estarán disponibles cuando se acredite.</div>
              @elseif(($downloads ?? collect())->isEmpty())
                <div class="alert alert-info">Este pedido no tiene archivos descargables.</div>
              @else
                <ul class="list-group mb-3">
                  @foreach ($downloads as $download)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                      <div>
                        <strong>{{ $download['product_name'] }}</strong><br>
                        <small class="text-body-secondary">{{ $download['original_name'] }}</small>
                      </div>
                      @if ($download['exists'])
                        <a href="{{ route('front.mi-cuenta.pedido.download', ['order' => $order->id, 'product' => $download['product_id'], 'file' => $download['file_id']]) }}" class="btn btn-sm btn-primary">Descargar</a>
                      @else
                        <span class="badge text-bg-warning">Archivo no disponible</span>
                      @endif
                    </li>
                  @endforeach
                </ul>
              @endif

              <a href="{{ route('front.mi-cuenta.pedidos') }}" class="btn btn-outline-secondary mt-3">Volver al listado</a>
            </div>
          </div>
        </div>
      </div>
      </div>
    </div>
  </section>
@endsection
