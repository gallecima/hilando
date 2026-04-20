@extends('layouts.backend')

@section('content')
@php
    // Mapas de estados -> clases y etiquetas ES
    $orderStatusLabels = [
        'pending'   => 'Pendiente',
        'paid'      => 'Pagado',
        'shipped'   => 'Enviado',
        'delivered' => 'Entregado',
        'cancelled' => 'Cancelado',
    ];
    $orderStatusClass = [
        'pending'   => 'bg-warning text-white',
        'paid'      => 'bg-info text-white',
        'shipped'   => 'bg-primary text-white',
        'delivered' => 'bg-success',
        'cancelled' => 'bg-danger',
    ];
    $paymentStatusLabels = [
        'pending'   => 'Pendiente',
        'completed' => 'Completado',
        'failed'    => 'Fallido',
        'paid'      => 'Pagado',
    ];
    $paymentStatusClass = [
        'pending'   => 'bg-warning text-white',
        'completed' => 'bg-success',
        'failed'    => 'bg-danger',
        'paid'      => 'bg-success',
    ];
@endphp

<div class="bg-body-light">
    <div class="content content-full d-flex align-items-center justify-content-between gap-2">
        <div>
            <h1 class="h3 fw-bold mb-0">Cliente: {{ $customer->name }}</h1>
            <p class="fs-sm text-muted mb-0">Alta: {{ optional($customer->created_at)->format('d/m/Y') }}</p>
        </div>
        <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-primary">
            <i class="fa fa-pen me-1"></i> Editar cliente
        </a>
    </div>
</div>

<div class="content">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="row">

        {{-- Columna izquierda: datos personales --}}
        <div class="col-md-4">

            {{-- Tarjetas resumen --}}
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <div class="block block-rounded text-center">
                        <div class="block-content block-content-full">
                            <div class="fs-sm text-muted mb-1">Operaciones</div>
                            <div class="h3 mb-0">{{ (int)($customer->tx_count ?? 0) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="block block-rounded text-center">
                        <div class="block-content block-content-full">
                            <div class="fs-sm text-muted mb-1">Operaciones pagas</div>
                            <div class="h3 mb-0">{{ (int)($customer->paid_count ?? 0) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    @php $paidSum = (float)($customer->paid_sum ?? 0); @endphp
                    <div class="block block-rounded text-center">
                        <div class="block-content block-content-full">
                            <div class="fs-sm text-muted mb-1">Gastado</div>
                            <div class="h3 text-primary mb-0">${{ number_format($paidSum, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Datos de contacto --}}
            <div class="block block-rounded">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Datos personales</h3>
                </div>
                <div class="block-content block-content-full">
                    <dl class="row mb-0">
                        <dt class="col-3">Nombre</dt>
                        <dd class="col-9">{{ $customer->name }}</dd>

                        <dt class="col-3">Email</dt>
                        <dd class="col-9">
                            <a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a>
                        </dd>

                        <dt class="col-3">Teléfono</dt>
                        <dd class="col-9">{{ $customer->phone ?: '-' }}</dd>

                        <dt class="col-3">Acceso</dt>
                        <dd class="col-9">
                            <span class="badge {{ $customer->is_wholesaler ? 'bg-primary' : 'bg-secondary' }}">
                                {{ $customer->is_wholesaler ? 'Mayorista' : 'Minorista' }}
                            </span>
                        </dd>

                        @if(optional($customer->billingData)->document_number ?? null)
                        <dt class="col-3">CUIT / DNI</dt>
                        <dd class="col-9">{{ $customer->billingData->document_number }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            @if($customer->billingData)
            <div class="block block-rounded mt-3">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Facturación</h3>
                </div>
                <div class="block-content block-content-full">
                    <dl class="row mb-0">
                        <dt class="col-4">Razón social</dt>
                        <dd class="col-8">{{ $customer->billingData->business_name ?? '-' }}</dd>

                        <dt class="col-4">Tipo de comprobante</dt>
                        <dd class="col-8">Factura {{ $customer->billingData->invoice_type ?? 'C' }}</dd>

                        <dt class="col-4">Condición IVA</dt>
                        <dd class="col-8">{{ $customer->billingData->tax_status ?? '-' }}</dd>

                        <dt class="col-4">Domicilio fiscal</dt>
                        <dd class="col-8">
                            {{ $customer->billingData->address_line ?? '-' }}
                            @if($customer->billingData->city)
                                <br>{{ $customer->billingData->city }} ({{ $customer->billingData->province }})
                            @endif
                            @if($customer->billingData->postal_code)
                                <br>CP {{ $customer->billingData->postal_code }}
                            @endif
                            @if($customer->billingData->country)
                                <br>{{ $customer->billingData->country }}
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>
            @endif

            {{-- Domicilio principal --}}
            @if($customer->address)
            @php $addr = $customer->address; @endphp
            <div class="block block-rounded mt-3">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Domicilio</h3>
                </div>
                <div class="block-content block-content-full">
                    <div class="mb-1"><strong>{{ $addr->title ?? 'Domicilio' }}</strong></div>
                    <div>{{ $addr->address_line }}</div>
                    <div>{{ $addr->city }} (CP {{ $addr->postal_code }})</div>
                    <div>{{ $addr->province }} — {{ $addr->country }}</div>
                </div>
            </div>
            @endif

            {{-- (Opcional) otras direcciones --}}
            @if($customer->addresses && $customer->addresses->count() > 1)
            <div class="block block-rounded mt-3">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Otras direcciones</h3>
                </div>
                <div class="block-content block-content-full">
                    <ul class="list-group">
                        @foreach($customer->addresses as $a)
                            @continue($customer->address && $a->id === $customer->address->id)
                            <li class="list-group-item">
                                <div class="fw-semibold">{{ $a->title }}</div>
                                <small class="text-muted">
                                    {{ $a->address_line }} — {{ $a->city }} ({{ $a->province }}) — {{ $a->country }}
                                </small>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
        </div>

        {{-- Columna derecha: operaciones/pedidos --}}
        <div class="col-md-8">
            <div class="block block-rounded">
                <div class="block-header block-header-default d-flex align-items-center justify-content-between">
                    <h3 class="block-title">Operaciones (pedidos)</h3>
                    <span class="badge bg-info">{{ $orders->total() }} en total</span>
                </div>
                <div class="block-content block-content-full overflow-x-auto">
                    <table class="table table-striped table-vcenter js-dataTable-full">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th class="text-end">Total</th>
                                <th>Pago</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($orders as $order)
                            @php
                                $oStatus = $order->status;
                                $oClass  = $orderStatusClass[$oStatus] ?? 'bg-secondary';
                                $oLabel  = $orderStatusLabels[$oStatus] ?? ucfirst($oStatus);

                                $payment = $order->payments->last();
                                $pStatus = optional($payment)->status;
                                $pClass  = $paymentStatusClass[$pStatus] ?? 'bg-secondary';
                                $pLabel  = $paymentStatusLabels[$pStatus] ?? ($pStatus ? ucfirst($pStatus) : '—');
                                $pMethod = optional($payment)->method;
                            @endphp
                            <tr>
                                <td>#{{ $order->id }}</td>
                                <td><small class="text-muted">{{ $order->created_at->format('d/m/Y H:i') }}</small></td>
                                <td><span class="badge {{ $oClass }}">{{ $oLabel }}</span></td>
                                <td class="text-end"><strong>${{ number_format($order->total, 2) }}</strong></td>
                                <td>
                                    @if($payment)
                                        <div><small>{{ $pMethod }}</small></div>
                                        <span class="badge {{ $pClass }}">{{ $pLabel }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.orders.show', $order) }}"
                                       class="btn btn-sm btn-alt-secondary"
                                       data-bs-toggle="tooltip" title="Ver pedido">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>


                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function(){
    // Si querés DataTables client-side (ojo con paginación Laravel)
    // $('.js-dataTable-full').DataTable({ paging: false, dom: 'lrtip', order: [[0,'desc']] });
});

  $(function () {
        var table = $('.js-dataTable-full').DataTable({
        order: [[0, 'desc']],
        pageLength: 10,
        dom: 'lrtip'
        });
    });
</script>
@endsection
