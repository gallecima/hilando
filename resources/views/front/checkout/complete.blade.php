@extends('layouts.front')

@section('title', 'Compra finalizada')
@section('body_class', 'page-hero checkout-page')

@section('content')
  @include('front.checkout.partials.page-header', [
    'title' => 'Gracias por tu compra',
    'subtitle' => 'Tu pedido fue registrado correctamente.',
    'eyebrow' => 'Checkout',
    'breadcrumbs' => [
      ['label' => 'Inicio', 'url' => route('home')],
      ['label' => 'Compra finalizada'],
    ],
  ])

  <section class="checkout-flow-section">
    <div class="checkout-flow-shell">
      <div class="container">
        <div class="card shadow-sm">
          <div class="card-header">
            Pedido #{{ $order->id }} - {{ $order->created_at->format('d/m/Y H:i') }}
          </div>
          <div class="card-body">
            <div class="row g-4">
              <div class="col-lg-6">
                <h2 class="h5">Datos del cliente</h2>
                <p class="mb-1"><strong>Nombre:</strong> {{ $order->name }}</p>
                <p class="mb-1"><strong>Email:</strong> {{ $order->email }}</p>
                <p class="mb-0"><strong>Teléfono:</strong> {{ $order->phone ?? '-' }}</p>
              </div>

              <div class="col-lg-6">
                @if ($order->payment)
                  <h2 class="h5">Pago</h2>
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
                  <h2 class="h5">Pago</h2>
                  <p class="mb-0">Sin datos de pago.</p>
                @endif
              </div>

              <div class="col-lg-6">
                @php
                  $shippingAddress = (array) ($order->shipping_address ?? []);
                  $shipmentMethodName = $order->shipment?->method?->name ?? optional($order->shipmentMethod)->name;
                  $packagePlan = (array) data_get($order->shipment, 'shipping_data_json.package_plan', []);
                  $shippingLocation = trim(implode(', ', array_filter([
                    data_get($shippingAddress, 'city'),
                    data_get($shippingAddress, 'province'),
                  ])));
                @endphp

                <h2 class="h5">Envío</h2>
                @if($shipmentMethodName || !empty($shippingAddress))
                  <p class="mb-1"><strong>Método:</strong> {{ $shipmentMethodName ?? 'A confirmar' }}</p>
                  <p class="mb-1"><strong>Dirección:</strong> {{ data_get($shippingAddress, 'address_line') ?: '-' }}</p>
                  <p class="mb-1"><strong>Ubicación:</strong> {{ $shippingLocation !== '' ? $shippingLocation : '-' }}</p>
                  <p class="mb-1"><strong>Código postal:</strong> {{ data_get($shippingAddress, 'postal_code') ?: '-' }}</p>
                  @if((int) ($packagePlan['package_count'] ?? 0) > 0)
                    <p class="mb-1"><strong>Paquetes estimados:</strong> {{ (int) $packagePlan['package_count'] }}</p>
                  @endif
                  <p class="mb-0"><strong>Costo:</strong> {{ (float) ($order->shipping_cost ?? 0) <= 0 ? 'Gratis' : '$' . number_format((float) $order->shipping_cost, 2, ',', '.') }}</p>
                @else
                  <p class="mb-0">Este pedido no requiere envío.</p>
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
              @if((float) ($order->shipping_cost ?? 0) > 0 || $order->shipment_method_id)
                <li class="list-group-item d-flex justify-content-between"><span>Envío</span><strong>{{ (float) ($order->shipping_cost ?? 0) <= 0 ? 'Gratis' : '$' . number_format((float) $order->shipping_cost, 2, ',', '.') }}</strong></li>
              @endif
              @if((float) ($order->shipping_discount ?? 0) > 0)
                <li class="list-group-item d-flex justify-content-between text-success"><span>Descuento en envío</span><strong>- ${{ number_format((float) $order->shipping_discount, 2, ',', '.') }}</strong></li>
              @endif
              <li class="list-group-item d-flex justify-content-between"><span>Total</span><strong class="text-success">${{ number_format($order->total, 2, ',', '.') }}</strong></li>
            </ul>

            <h2 class="h5 mt-4">Descargas</h2>
            @php
              $accessDocument = $order->customer->document
                ?? data_get($order->billing_data_json, 'document_number')
                ?? data_get($order->billing_data_json, 'document');
            @endphp

            @if (!$orderPaid)
              <div class="alert alert-warning mb-3">Tu pago todavía está pendiente. Las descargas se habilitan automáticamente cuando se acredita.</div>
            @elseif(($downloads ?? collect())->isEmpty())
              <div class="alert alert-info mb-3">No encontramos archivos descargables asociados a este pedido.</div>
            @else
              <ul class="list-group mb-3">
                @foreach ($downloads as $download)
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                      <strong>{{ $download['product_name'] }}</strong><br>
                      <small class="text-body-secondary">{{ $download['original_name'] }}</small>
                    </div>
                    @if ($download['exists'])
                      <a href="{{ route('orders.download', ['token' => $order->public_token, 'product' => $download['product_id'], 'file' => $download['file_id']]) }}" class="btn btn-sm btn-primary">Descargar</a>
                    @else
                      <span class="badge text-bg-warning">Archivo no disponible</span>
                    @endif
                  </li>
                @endforeach
              </ul>
            @endif

            <div class="alert alert-light border">
              Podés volver a tus descargas iniciando sesión con:
              <br><strong>Email:</strong> {{ $order->email }}
              <br><strong>Contraseña (DNI):</strong> {{ $accessDocument ?: 'el DNI que usaste en la compra' }}
            </div>

            <div class="text-center mt-4">
              <a href="{{ route('home') }}" class="btn btn-outline-primary">Volver al inicio</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
