@extends('layouts.backend')

@section('content')
<div class="bg-body-light">
    <div class="content content-full d-flex align-items-center justify-content-between">
        <div>
            <h1 class="h3 fw-bold">Detalle del Pedido #{{ $order->id }}</h1>
            <p class="fs-sm text-muted mb-0">Realizado el {{ $order->created_at->format('d/m/Y H:i') }}</p>
        </div>


    </div>
</div>

<div class="content">
    <div class="row">

        <!-- Columna izquierda: Items / Pago / Envío -->
        <div class="col-md-8">
            @php
                // Pedido
                $orderStatusClass = match($order->status) {
                    'pending','cancelled' => 'bg-danger',
                    'paid','shipped','delivered' => 'bg-success',
                    default => 'bg-secondary',
                };
                $orderStatusLabel = [
                    'pending'   => 'Pendiente',
                    'paid'      => 'Pagado',
                    'shipped'   => 'Enviado',
                    'delivered' => 'Entregado',
                    'cancelled' => 'Cancelado',
                ][$order->status] ?? ucfirst($order->status);

                // Pago
                $payment = $order->payments->last();
                $paymentStatus = $payment?->status;
                $paymentStatusClass = match($paymentStatus) {
                    'pending'   => 'bg-warning text-dark',
                    'completed' => 'bg-success',
                    'failed'    => 'bg-danger',
                    default     => 'bg-secondary',
                };
                $paymentStatusLabel = [
                    'pending'   => 'Pendiente',
                    'completed' => 'Completado',
                    'failed'    => 'Fallido',
                ][$paymentStatus] ?? ($paymentStatus ? ucfirst($paymentStatus) : 'Sin estado');

                $filePath   = data_get($payment?->payment_data, 'uploaded_receipt_path');
                $payFee     = (float) (data_get($payment?->payment_data, 'fee', 0) ?: 0);
                $payDisc    = (float) (data_get($payment?->payment_data, 'discount', 0) ?: 0);

                // Envío
                $shipment       = $order->shipment;
                $shipmentMethod = $order->shipmentMethod;   // 👈 AHORA desde Order
                $methodName     = $shipmentMethod?->name;
                $isPickup       = (bool) ($shipmentMethod->is_pickup ?? false); // 👈 flag pickup
                $shipmentStatus = $shipment?->status;

                $shipmentStatusClass = match($shipmentStatus) {
                    'pending'           => 'bg-warning text-dark',
                    'ready_for_pickup'  => 'bg-info',    // 👈 nuevo estado
                    'shipped'           => 'bg-info',
                    'delivered'         => 'bg-success',
                    default             => 'bg-secondary',
                };

                $shipmentStatusLabel = [
                    'pending'          => 'Pendiente',
                    'ready_for_pickup' => 'Disponible para retirar', // 👈 nuevo label
                    'shipped'          => 'Enviado',
                    'delivered'        => 'Entregado',
                ][$shipmentStatus] ?? ($shipmentStatus ? ucfirst($shipmentStatus) : 'Sin estado');

                // Totales / Desgloses
                $subtotal          = (float) $order->subtotal;
                $couponDiscount    = (float) ($order->discount ?? 0);
                $shippingCost      = (float) ($order->shipping_cost ?? 0);
                $shippingDiscount  = (float) ($order->shipping_discount ?? 0);
            @endphp

            {{-- Items --}}
            <div class="block block-rounded">
                <div class="block-header block-header-default d-flex align-items-center justify-content-between">
                    <h3 class="block-title">Items</h3>
                    <span class="badge bg-info">{{ count($order->items) }} items</span>
                </div>
                <div class="block-content block-content-full">
                    <ul class="list-group">
                        @foreach($order->items as $item)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="d-flex flex-inline align-items-center gap-2">
                                    @php
                                        $product = $item->product;
                                        $image = null;
                                        if ($item->attributeValue && $product) {
                                            $pivot = $product->attributeValues()
                                                ->where('attribute_values.id', $item->attribute_value_id)
                                                ->first();
                                            $image = $pivot?->pivot?->image;
                                        }
                                        if (!$image && $product?->featured_image) {
                                            $image = $product->featured_image;
                                        }
                                    @endphp

                                    @if($image)
                                        <div class="d-flex align-items-center img-thumbnail rounded p-2 text-center" style="width:60px; height:60px; background-color:#FFF">
                                            <img src="{{ asset('storage/' . $image) }}" class="w-100" alt="">
                                        </div>
                                    @else
                                        <div class="bg-body-secondary rounded p-2 text-center" style="width:60px; height:60px;">
                                            <i class="fa fa-box"></i>
                                        </div>
                                    @endif

                                    {{ $item->quantity }} x {{ $item->product->name }}
                                    @if ($item->attributeValue)
                                        &nbsp;—&nbsp;{{ $item->attributeValue->value }}
                                    @endif
                                </div>
                                <span>${{ number_format($item->price, 2) }}</span>

                            </li>
                        @endforeach
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Subtotal
                            <b class="text-primary">${{ number_format($subtotal, 2) }}</b>
                        </li>
                    </ul>
            </div>
        </div>



            {{-- Pago (detalle + update) --}}
            @if($payment)
            <div class="block block-rounded mt-3">
                    <div class="block-header block-header-default d-flex align-items-center justify-content-between">
                        <h3 class="block-title">Pago</h3>
                        <span class="badge {{ $paymentStatusClass }}">{{ $paymentStatusLabel }}</span>
                    </div>
                    <div class="block-content block-content-full">
                        <dl class="row mb-3 striped">
                            <dt class="col-5">Método</dt>
                            <dd class="col-7 text-end">{{ $payment->method }}</dd>

                            <dt class="col-5">Importe</dt>
                            <dd class="col-7 text-end">${{ number_format($payment->amount, 2) }}</dd>

                            @if($payFee > 0)
                                <dt class="col-5">Recargo por pago</dt>
                                <dd class="col-7 text-end">+ ${{ number_format($payFee, 2) }}</dd>
                            @endif

                            @if($payDisc > 0)
                                <dt class="col-5">Bonificación por pago</dt>
                                <dd class="col-7 text-end">- ${{ number_format($payDisc, 2) }}</dd>
                            @endif

                            <dt class="col-5">Transacción</dt>
                            <dd class="col-7 text-end">{{ $payment->transaction_id ?: '-' }}</dd>

                            <dt class="col-5">Comprobante</dt>
                            <dd class="col-7 text-end">
                                @if($filePath)
                                    <a href="{{ asset('storage/' . $filePath) }}" target="_blank"><small>Ver comprobante</small></a>
                                @else
                                    -
                                @endif
                            </dd>

                            <dt class="col-5">Fecha de pago</dt>
                            <dd class="col-7 text-end">{{ $payment->created_at->format('d/m/Y H:i') }}</dd>
                        </dl>

                        <form action="{{ route('admin.payments.update', $payment) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <input type="hidden" name="order_id" value="{{ $order->id }}">
                            <input type="hidden" name="method" value="{{ $payment->method }}">
                            <input type="hidden" name="amount" value="{{ $payment->amount }}">
                            <input type="hidden" name="transaction_id" value="{{ $payment->transaction_id }}">
                            @if(!empty($payment->payment_data))
                                <input type="hidden" name="payment_data" value='@json($payment->payment_data)'>
                            @endif

                            <div class="mb-3">
                                <label class="form-label">Estado del pago</label>
                                <select name="status" class="form-select">
                                    <option value="pending"   {{ $payment->status === 'pending' ? 'selected' : '' }}>Pendiente</option>
                                    <option value="completed" {{ $payment->status === 'completed' ? 'selected' : '' }}>Completado</option>
                                    <option value="failed"    {{ $payment->status === 'failed' ? 'selected' : '' }}>Fallido</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">ID de Transacción (opcional)</label>
                                <input type="text" class="form-control" name="transaction_id" value="{{ old('transaction_id', $payment->transaction_id) }}">
                            </div>

                            <button type="submit" class="btn btn-success w-100">Actualizar pago</button>
                        </form>
                    </div>
                </div>
            @endif

            @php
            // Si el plugin AFIP está activo, este hook devolverá HTML; si no, devuelve ''.
            $billingPanel = app(\App\Support\Hooks::class)->render('admin:orders:billing.panel', $order);
            @endphp

            @if (!empty(trim($billingPanel)))
            {!! $billingPanel !!}
            @else
            {{-- === Fallback manual (tu bloque actual de adjuntar comprobantes) === --}}
            <div class="block block-rounded mt-3">
                <div class="block-header block-header-default d-flex align-items-center justify-content-between">
                <h3 class="block-title">Facturación</h3>
                <span class="badge bg-secondary">{{ $order->invoices->count() }} comprobante(s)</span>
                </div>
                <div class="block-content block-content-full">
                @if($order->invoices->isEmpty())
                    <p class="text-muted mb-3">Este pedido aún no tiene comprobantes asociados.</p>
                @else
                    <div class="table-responsive mb-3">
                    <table class="table table-sm table-striped align-middle">
                        <thead>
                        <tr>
                            <th>Proveedor</th>
                            <th>Comprobante</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($order->invoices as $invoice)
                            <tr>
                            <td>{{ ucfirst($invoice->provider) }}</td>
                            <td>
                                <div class="fw-semibold">{{ $invoice->title ?? 'Factura' }}</div>
                                @if($invoice->number)
                                <small class="text-muted">N° {{ $invoice->number }}</small>
                                @endif
                            </td>
                            <td><span class="badge bg-success">{{ ucfirst($invoice->status) }}</span></td>
                            <td>{{ optional($invoice->issued_at)->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="text-end">
                                @if($invoice->file_path)
                                <a href="{{ asset('storage/' . $invoice->file_path) }}" target="_blank" class="btn btn-sm btn-alt-primary"><i class="fa fa-file-pdf"></i></a>
                                @endif
                                @if($invoice->external_url)
                                <a href="{{ $invoice->external_url }}" target="_blank" class="btn btn-sm btn-alt-secondary"><i class="fa fa-external-link-alt"></i></a>
                                @endif
                                <form action="{{ route('admin.orders.invoices.destroy', [$order, $invoice]) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar comprobante?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-alt-danger" type="submit"><i class="fa fa-times"></i></button>
                                </form>
                            </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    </div>
                @endif

                <form action="{{ route('admin.orders.invoices.store', $order) }}" method="POST" enctype="multipart/form-data" class="row g-3">
                    @csrf
                    <div class="col-md-6">
                    <label class="form-label">Título</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="Factura adjunta">
                    </div>
                    <div class="col-md-3">
                    <label class="form-label">Número</label>
                    <input type="text" name="number" class="form-control" value="{{ old('number') }}" placeholder="0001-00000000">
                    </div>
                    <div class="col-md-3">
                    <label class="form-label">Fecha emisión</label>
                    <input type="datetime-local" name="issued_at" class="form-control" value="{{ old('issued_at') }}">
                    </div>
                    <div class="col-md-6">
                    <label class="form-label">Archivo PDF</label>
                    <input type="file" name="file" class="form-control" accept="application/pdf">
                    <small class="text-muted">Tamaño máximo 8 MB.</small>
                    </div>
                    <div class="col-md-6">
                    <label class="form-label">Enlace externo</label>
                    <input type="url" name="external_url" class="form-control" value="{{ old('external_url') }}" placeholder="https://...">
                    <small class="text-muted">Opcional. Se usa si el comprobante lo gestiona un plugin externo.</small>
                    </div>
                    <div class="col-12 text-end">
                    <button class="btn btn-primary" type="submit"><i class="fa fa-plus me-1"></i> Adjuntar comprobante</button>
                    </div>
                </form>
                </div>
            </div>
            @endif
         

            {{-- Envío --}}
            @php
                $shippedVal   = $shipment?->shipped_at;
                $deliveredVal = $shipment?->delivered_at;
            @endphp

            <div class="block block-rounded mt-3">
                <div class="block-header block-header-default d-flex align-items-center justify-content-between">
                    <h3 class="block-title">
                        {{ $isPickup ? 'Retiro / Pickup' : 'Envío' }} {{-- 👈 cambia el título según tipo --}}
                    </h3>
                    <span class="badge {{ $shipmentStatusClass }}">{{ $shipmentStatusLabel }}</span>
                </div>

                <div class="block-content block-content-full">
                    @if($shipment)
                        <form action="{{ route('admin.shipments.update', $shipment) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3 alert alert-info">
                                        <label class="form-label">Seleccionado: </label>
                                        @if($methodName)
                                            <strong>{{ $methodName }}</strong><br>
                                            @if($isPickup)
                                                <small class="text-muted">
                                                    Este método es de <strong>retiro en punto de pickup</strong>.
                                                    No se utiliza la dirección de envío del cliente.
                                                </small>
                                            @else
                                                <small class="text-muted">
                                                    Envío a domicilio utilizando la dirección del cliente.
                                                </small>
                                            @endif
                                        @else
                                            <small class="text-muted">Sin método</small><br>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Para pickup igualmente dejamos carrier/tracking por si lo usás para sucursales,
                                 pero podrías ocultarlos si querés. --}}

                            @if(!$isPickup)
                            <div class="row">
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label">Transportista</label>
                                        <input type="text" name="carrier" class="form-control" value="{{ old('carrier', $shipment->carrier) }}">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label">Número de seguimiento</label>
                                        <input type="text" name="tracking_number" class="form-control" value="{{ old('tracking_number', $shipment->tracking_number) }}">
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div class="mb-3">
                                <label class="form-label">Estado del envío</label>
                                <select name="status" class="form-select">
                                    <option value="pending"           {{ $shipment->status === 'pending' ? 'selected' : '' }}>Pendiente</option>


                                    <option value="ready_for_pickup"  {{ $shipment->status === 'ready_for_pickup' ? 'selected' : '' }}>
                                        Disponible para retirar
                                    </option>

                                    <option value="shipped"           {{ $shipment->status === 'shipped' ? 'selected' : '' }}>Enviado</option>
                                    <option value="delivered"         {{ $shipment->status === 'delivered' ? 'selected' : '' }}>Entregado</option>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label">Fecha de envío</label>
                                        <input type="datetime-local" name="shipped_at" class="form-control" value="{{ $shippedVal }}">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label class="form-label">Fecha de entrega</label>
                                        <input type="datetime-local" name="delivered_at" class="form-control" value="{{ $deliveredVal }}">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-6">
                                    @php
                                        $enviacomLabelUrl = null;
                                        if (!empty($shipment)) {
                                            $sdj = $shipment->shipping_data_json ?? [];
                                            if (is_string($sdj)) {
                                                $dec = json_decode($sdj, true);
                                                $sdj = is_array($dec) ? $dec : [];
                                            } elseif (is_object($sdj)) {
                                                $sdj = json_decode(json_encode($sdj), true) ?: [];
                                            } elseif (!is_array($sdj)) {
                                                $sdj = [];
                                            }

                                            $enviacomLabelUrl = data_get($sdj, 'enviacom.generate.parsed.label')
                                                ?: data_get($sdj, 'enviacom.generate.response.data.label')
                                                ?: (data_get($shipment, 'label_url') ?: null);

                                            $enviacomLabelUrl = is_string($enviacomLabelUrl) ? trim($enviacomLabelUrl) : null;
                                            if ($enviacomLabelUrl === '') $enviacomLabelUrl = null;
                                        }
                                    @endphp

                                    <button
                                        class="btn btn-alt-secondary w-100"
                                        id="btnOpenLabel"
                                        type="button"
                                        data-url="{{ $enviacomLabelUrl ?: route('admin.orders.label', $order) }}">
                                        <i class="fa fa-print"></i> Imprimir etiqueta
                                    </button>                                    
                                </div>
                                <div class="col-6">
                                    <button type="submit" class="btn btn-primary w-100">Guardar envío</button>
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="text-muted">Este pedido no tiene registro de envío.</div>
                    @endif
                </div>
            </div>

            {{-- Hook: detalles extra del envío (plugins) --}}
            {!! app('App\Support\Hooks')->render('admin:orders:shipment.details') !!}

            {{-- Errores --}}
            @if ($errors->any())
                <div class="alert alert-danger mt-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- Columna derecha: Info del pedido/cliente + Cupón + Resumen + Ticket -->
        <div class="col-md-4">

            {{-- Info de pedido/cliente --}}
            <div class="block block-rounded">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Información del Pedido</h3>
                </div>
                <div class="block-content block-content-full">
                    <p><strong>Cliente:</strong> {{ $order->name }}</p>
                    <p><strong>Email:</strong> {{ $order->email }}</p>
                    <p>
                        <strong>Estado del pedido:</strong>
                        <span class="badge {{ $orderStatusClass }}">{{ $orderStatusLabel }}</span>
                    </p>
                </div>
            </div>

            {{-- Cupón (si existe) --}}
            @if($order->coupon)
                <div class="block block-rounded mt-3">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Cupón de Descuento</h3>
                    </div>
                    <div class="block-content block-content-full">
                        <p class="mb-1"><strong>Código:</strong> <code>{{ $order->coupon->code }}</code></p>
                        <p class="mb-0">
                            <strong>Descuento aplicado:</strong>
                            ${{ number_format($couponDiscount, 2) }}
                        </p>
                    </div>
                </div>
            @endif

            {{-- Resumen --}}
            <div class="block block-rounded mt-3">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Resumen</h3>
                </div>
                <div class="block-content block-content-full">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Subtotal</span>
                            <strong>${{ number_format($subtotal, 2) }}</strong>
                        </li>

                        @if($couponDiscount > 0)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Descuento (cupón)</span>
                            <strong class="text-success">- ${{ number_format($couponDiscount, 2) }}</strong>
                        </li>
                        @endif

                        @if($shippingCost > 0)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Envío</span>
                            <strong>${{ number_format($shippingCost, 2) }}</strong>
                        </li>
                        @endif

                        @if($shippingDiscount > 0)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Descuento de envío</span>
                            <strong class="text-success">- ${{ number_format($shippingDiscount, 2) }}</strong>
                        </li>
                        @endif

                        @if($payFee > 0)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Recargo por pago</span>
                            <strong class="text-danger">+ ${{ number_format($payFee, 2) }}</strong>
                        </li>
                        @endif

                        @if($payDisc > 0)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Bonificación por pago</span>
                            <strong class="text-success">- ${{ number_format($payDisc, 2) }}</strong>
                        </li>
                        @endif

                        <li class="list-group-item d-flex justify-content-between mt-2 pt-2">
                            <span>Total</span>
                            <strong class="text-primary">${{ number_format($order->total, 2) }}</strong>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Ticket imprimible (visible sólo al imprimir) --}}
            <div id="print-ticket" class="mt-3">
                <div class="block block-rounded ticket">
                    <div class="block-content">
                        <div class="block-header block-header-default">
                            <strong>Pedido #{{ $order->id }}</strong><br>
                            <small>{{ $order->created_at->format('d/m/Y H:i') }}</small>
                        </div>

                        <div class="block-content block-content-full">
                            <p class="mb-1"><strong>Cliente:</strong> {{ $order->name }}</p>
                            <p class="mb-1"><strong>Email:</strong> {{ $order->email }}</p>

                            @php
                                $addr = $order->shipping_address; // array o null
                            @endphp
                            <p class="mb-1">
                                <strong>{{ $isPickup ? 'Retiro:' : 'Entrega:' }}</strong>
                                @if($isPickup)
                                    Retiro en: {{ $methodName }}
                                    @if(is_array($addr) && !empty($addr['address_line']))
                                        <br>
                                        {{ $addr['address_line'] ?? '' }} - {{ $addr['city'] ?? '' }}<br>
                                        {{ $addr['province'] ?? '' }}
                                    @endif
                                @else
                                    @if(is_array($addr))
                                        {{ $addr['address_line'] ?? '' }} - {{ $addr['city'] ?? '' }}<br>
                                        CP {{ $addr['postal_code'] ?? '' }} - {{ $addr['province'] ?? '' }}
                                    @else
                                        {{ $addr }}
                                    @endif
                                @endif
                            </p>

                            <p class="mb-1"><strong>Items:</strong> {{ count($order->items) }}</p>

                            @if($payment)
                                <p class="mb-0"><strong>Pago:</strong> {{ $payment->method }} — {{ $paymentStatusLabel }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>


{{-- Modal etiqueta --}}
<div class="modal fade" id="labelModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h5 class="modal-title">Etiqueta de envío</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <iframe id="labelFrame" src="about:blank"
                style="width:100%;height:11cm;border:0;"></iframe>
      </div>
      <div class="modal-footer d-print-none">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="btnPrintLabel">
          <i class="fa fa-print me-1"></i> Imprimir
        </button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const btnOpen = document.getElementById('btnOpenLabel');
  const modalEl = document.getElementById('labelModal');
  const iframe   = document.getElementById('labelFrame');
  const btnPrint = document.getElementById('btnPrintLabel');

	  // abrir modal y setear la URL de la etiqueta
	  btnOpen?.addEventListener('click', function () {
	    const url = this.dataset.url;
	    if (!url) return;

    // recargar el iframe cada vez que abrimos
    iframe.src = url;

    const modal = new bootstrap.Modal(modalEl, {backdrop: 'static'});
    modal.show();
  });

  // limpiar iframe al cerrar (opcional)
  modalEl.addEventListener('hidden.bs.modal', () => {
    iframe.src = 'about:blank';
  });

	  // imprimir SOLO el contenido del iframe
	  btnPrint?.addEventListener('click', function () {
	    const url = iframe.getAttribute('src') || '';
	    if (!url || url === 'about:blank') return;
	
	    // En etiquetas externas (PDF remoto) `contentDocument` puede ser inaccesible; imprimimos directo.
	    try {
	      iframe.contentWindow?.focus();
	      iframe.contentWindow?.print();
	    } catch (e) {
	      // Fallback: abrir en pestaña nueva para imprimir desde el visor del navegador
	      window.open(url, '_blank');
	    }
	  });
	});
	</script>
@endsection
