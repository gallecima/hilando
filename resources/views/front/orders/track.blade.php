@extends('layouts.front')

@section('title', 'Detalle del pedido')

@section('content')
@include('front.partials.page-header', [
  'title' => 'Detalle del pedido',
  'subtitle' => 'Seguimiento y descargas del pedido realizado.',
  'breadcrumbs' => [
    ['label' => 'Inicio', 'url' => route('home')],
    ['label' => 'Pedido #' . $order->id],
  ],
])

<div class="container pb-5">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card shadow-sm">

        @php
          $payment        = $order->payments->last();
          $paymentStatus  = $payment?->status;
          $billingData    = (array) ($order->billing_data_json ?? []);

          // Badge pago
          $paymentBadgeClass = match($paymentStatus) {
              'completed', 'paid' => 'success',
              'failed'            => 'danger',
              default             => 'secondary',
          };
          $paymentLabel = $paymentStatus
              ? ($paymentStatus === 'completed' ? 'Completado' : ucfirst($paymentStatus))
              : 'Pendiente';
        @endphp

        <div class="card-header bg-dark text-white">
          <h5 class="mb-0">Pedido #{{ $order->id }}</h5>
          <small class="text-light">Realizado el {{ $order->created_at->format('d/m/Y H:i') }}</small>
        </div>

        <div class="card-body">
          <p class="mb-2">
            <strong>Nombre:</strong> {{ $order->name ?? $order->customer->name ?? 'Cliente' }}<br>
            <strong>Email:</strong> {{ $order->email ?? $order->customer->email ?? '-' }}<br>
            <strong>Documento:</strong> {{ $billingData['document_number'] ?? '-' }}
          </p>

          <hr>

          <h6 class="fw-bold mb-3">Resumen del pedido</h6>
          <div class="table-responsive">
            <table class="table table-sm align-middle">
              <thead>
                <tr>
                  <th>Producto</th>
                  <th class="text-end">Cant.</th>
                  <th class="text-end">Precio</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($order->items as $item)
                  <tr>
                    <td>{{ $item->product->name ?? 'Producto eliminado' }}</td>
                    <td class="text-end">{{ $item->quantity }}</td>
                    <td class="text-end">${{ number_format($item->price, 2) }}</td>
                  </tr>
                @endforeach
                <tr>
                  <td colspan="2" class="text-end fw-bold">Subtotal</td>
                  <td class="text-end">${{ number_format($order->subtotal, 2) }}</td>
                </tr>
                <tr>
                  <td colspan="2" class="text-end fw-bold">Descuento</td>
                  <td class="text-end">-${{ number_format($order->discount ?? 0, 2) }}</td>
                </tr>
                <tr>
                  <td colspan="2" class="text-end fw-bold">Total</td>
                  <td class="text-end fw-bold h5">${{ number_format($order->total, 2) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <hr>

          <h6 class="fw-bold mb-3">Estado</h6>
          <p class="mb-1">
            <strong>Pago:</strong> 
            <span class="badge bg-{{ $paymentBadgeClass }}">
              {{ $paymentLabel }}
            </span>
          </p>

          <hr>

          <h6 class="fw-bold mb-3">Descargas</h6>
          @if (!$orderPaid)
            <div class="alert alert-warning">
              Tu pago aún está pendiente. Las descargas se habilitan cuando se acredita.
            </div>
          @elseif(($downloads ?? collect())->isEmpty())
            <div class="alert alert-info">
              Este pedido no tiene archivos descargables.
            </div>
          @else
            <ul class="list-group mb-3">
              @foreach ($downloads as $download)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <div>
                    <strong>{{ $download['product_name'] }}</strong><br>
                    <small class="text-muted">{{ $download['original_name'] }}</small>
                  </div>
                  @if ($download['exists'])
                    <a href="{{ route('orders.download', ['token' => $order->public_token, 'product' => $download['product_id'], 'file' => $download['file_id']]) }}"
                       class="btn btn-sm btn-primary">Descargar</a>
                  @else
                    <span class="badge text-bg-warning">Archivo no disponible</span>
                  @endif
                </li>
              @endforeach
            </ul>
          @endif

          <hr>

          <div class="text-center">
            <a href="{{ url('/') }}" class="btn btn-dark">
              <i class="bi bi-house-door"></i> Volver a la tienda
            </a>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection
