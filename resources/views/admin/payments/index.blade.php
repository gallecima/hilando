@extends('layouts.backend')

@section('actions')
    {{-- Acciones opcionales --}}
@endsection

@section('content')
<!-- Hero -->
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-1">Pagos</h1>
                <h2 class="fs-base lh-base fw-medium text-muted mb-0">Historial y gestión de pagos</h2>
            </div>
        </div>
    </div>
</div>
<!-- END Hero -->

<div class="content">

    {{-- Notificaciones --}}
    @if(session('success'))
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            One.helpers('jq-notify', {
                type: 'success',
                icon: 'fa fa-check-circle me-1',
                from: 'bottom',
                message: @json(session('success'))
            });
        });
    </script>
    @endif

    {{-- Filtros --}}
    <div class="block block-rounded mb-3">
        <div class="block-content block-content-full overflow-x-auto">
            <form method="GET" action="{{ route('admin.payments.index') }}" class="row g-2 align-items-end">
                @php
                    $filters = $filters ?? [];
                    $from  = $filters['from']  ?? now()->startOfMonth()->format('Y-m-d');
                    $to    = $filters['to']    ?? now()->endOfMonth()->format('Y-m-d');
                    $status= $filters['status']?? '';
                    $method= $filters['method']?? '';
                @endphp

                <div class="col-md-3">
                    <label class="form-label">Desde</label>
                    <input type="date" name="from" value="{{ $from }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="to" value="{{ $to }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Estado</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="pending"   {{ $status==='pending'?'selected':'' }}>Pendiente</option>
                        <option value="completed" {{ $status==='completed'?'selected':'' }}>Completado</option>
                        <option value="failed"    {{ $status==='failed'?'selected':'' }}>Fallido</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Método</label>
                    <select name="method" class="form-select">
                        <option value="">Todos</option>
                        @foreach($availableMethods as $m)
                            <option value="{{ $m }}" {{ $method===$m?'selected':'' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-primary">
                        <i class="fa fa-filter me-1"></i> Aplicar
                    </button>
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-alt-secondary">
                        Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tarjetas por método --}}
    <div class="row">
        @forelse($summaryByMethod as $methodName => $sum)
            <div class="col-sm-6 col-md-4 col-lg-3">
                <div class="block block-rounded text-center">
                    <div class="block-content block-content-full">
                        <div class="fs-sm text-muted mb-1">{{ $methodName }}</div>
                        <div class="fs-3 fw-semibold">${{ number_format($sum['amount'], 2) }}</div>
                        <div class="fs-sm text-muted">{{ $sum['count'] }} pagos</div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info mb-3">No hay pagos para el rango/criterios seleccionados.</div>
            </div>
        @endforelse
    </div>

    {{-- Tabla --}}
    <div class="block block-rounded">
        <div class="block-content block-content-full overflow-x-auto">
            <table class="table table-striped table-vcenter js-dataTable-full">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pago / Fecha / Estado</th>
                        <th>Método / Importe</th>
                        <th>Pedido / Cliente</th>
                    </tr>
                </thead>
                <tbody>
                @php
                    $paymentStatusLabels = [
                        'pending'   => 'Pendiente',
                        'completed' => 'Completado',
                        'failed'    => 'Fallido',
                    ];
                @endphp

                @foreach($payments as $payment)
                    @php
                        $status = $payment->status;
                        $statusClass = match($status) {
                            'pending'   => 'bg-warning',
                            'completed' => 'bg-success',
                            'failed'    => 'bg-danger',
                            default     => 'bg-secondary',
                        };
                        $order = $payment->order;
                        $filePath   = data_get($payment?->payment_data, 'file');
                    @endphp
                    <tr>
                        <td>{{ $payment->id }}</td>
                        <td>
                            <strong>#{{ $payment->id }}</strong><br>
                            <small class="text-muted">{{ $payment->created_at->format('d/m/Y H:i') }}</small><br>
                            <span class="badge {{ $statusClass }}">{{ $paymentStatusLabels[$status] ?? ucfirst($status) }}</span>
                        </td>
                        <td>
                            <div>{{ $payment->method }}</div>
                            <strong>${{ number_format($payment->amount, 2) }}</strong>
                                
                                @if($filePath)
                                    <br>
                                    <a href="{{ asset('storage/' . $filePath) }}" target="_blank">Ver comprobante</a>
                                @endif                            
                        </td>
                        <td>
                            @if($order)
                                <div>Pedido: <a href="{{ route('admin.orders.show', $order) }}">#{{ $order->id }}</a></div>
                                <small>{{ $order->name }} — {{ $order->email }}</small>
                            @else
                                <small class="text-muted">Sin pedido</small>
                            @endif
                        </td>
                    </tr>
                @endforeach

                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@section('js')
<script>
    $(document).ready(function(){
        var table = $('.js-dataTable-full').DataTable({
            order: [[0, 'desc']],
            pageLength: 10,
            dom: 'lrtip'
        });

        // Si querés un input de búsqueda global externo:
        $('#customSearch').on('keyup', function () {
            table.search(this.value).draw();
        });
    });
</script>
@endsection