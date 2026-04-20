@extends('layouts.backend')

@section('actions')
    {{-- Acciones opcionales --}}
@endsection

@section('content')
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-1">Clientes</h1>
                <h2 class="fs-base lh-base fw-medium text-muted mb-0">Estadísticas de compras por cliente</h2>
            </div>
        </div>
    </div>
</div>

<div class="content">

    {{-- Filtros --}}
    @php
        $from  = $filters['from']  ?? now()->startOfMonth()->format('Y-m-d');
        $to    = $filters['to']    ?? now()->endOfMonth()->format('Y-m-d');
        $q     = $filters['q']     ?? '';
        $minTx = $filters['minTx'] ?? '';
    @endphp
    <div class="block block-rounded mb-3">
        <div class="block-content block-content-full overflow-x-auto">
            <form method="GET" action="{{ route('admin.customers.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Desde</label>
                    <input type="date" name="from" class="form-control" value="{{ $from }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="to" class="form-control" value="{{ $to }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Búsqueda</label>
                    <input type="text" name="q" class="form-control" value="{{ $q }}" placeholder="Nombre, email o teléfono">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Mín. transacciones</label>
                    <input type="number" min="0" name="min_tx" class="form-control" value="{{ $minTx }}">
                </div>
                <div class="col-12 d-flex gap-2 mt-2">
                    <a href="{{ route('admin.customers.export', request()->query()) }}"
                    class="btn btn-alt-primary">
                    <i class="fa fa-file-csv me-1"></i> Exportar CSV
                    </a>                    
                    <button class="btn btn-primary"><i class="fa fa-filter me-1"></i> Aplicar</button>
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-alt-secondary">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="block block-rounded">
        <div class="block-content block-content-full overflow-x-auto">
            <table class="table table-striped table-vcenter js-dataTable-full">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Contacto</th>
                        <th class="text-end">Tx</th>
                        <th class="text-end">Tx (pagas)</th>
                        <th class="text-end">Gastado</th>
                        <th class="text-end">Ticket prom.</th>
                        <th class="text-end">Última compra</th>
                        <th class="text-end"></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($customers as $c)
                    @php
                        $tx_count  = (int) ($c->tx_count ?? 0);
                        $paidCount  = (int) ($c->paid_count ?? 0);
                        $paidSum    = (float) ($c->paid_sum ?? 0);
                        $avgTicket  = $paidCount > 0 ? $paidSum / $paidCount : 0;
                        $lastAt     = $c->last_order_at ? \Carbon\Carbon::parse($c->last_order_at) : null;
                    @endphp
                    <tr>
                        <td>{{ $c->id }}</td>
                        <td>
                            <strong>{{ $c->name }}</strong><br>
                            <small class="text-muted">Alta: {{ $c->created_at->format('d/m/Y') }}</small>
                        </td>
                        <td>
                            <div>{{ $c->email }}</div>
                            @if($c->phone)
                                <small class="text-muted">{{ $c->phone }}</small>
                            @endif
                            @if($c->document)<br>
                                <small class="text-primary">DNI {{ $c->document }}</small>
                            @endif
                        </td>
                        <td class="text-end">
                            <span class="badge bg-secondary">{{ $tx_count }}</span>
                        </td>                        
                        <td class="text-end">
                            <span class="badge bg-primary">{{ $paidCount }}</span>
                        </td>
                        <td class="text-end">
                            <strong>${{ number_format($paidSum, 2) }}</strong>
                        </td>
                        <td class="text-end">
                            ${{ number_format($avgTicket, 2) }}
                        </td>
                        <td class="text-end">
                            {{ $lastAt ? $lastAt->format('d/m/Y H:i') : '-' }}
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.customers.show', $c) }}" class="btn btn-sm btn-alt-secondary" data-bs-toggle="tooltip" title="Ver cliente">
                                <i class="fa fa-eye"></i>
                            </a>
                            {{-- Si tenés ruta a pedidos del cliente, podés linkearla también --}}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            {{-- Paginación Laravel (si preferís DataTables serverless) --}}
            <div class="mt-3">
                {{ $customers->links() }}
            </div>
        </div>
    </div>

</div>
@endsection

@section('js')
<script>
    $(document).ready(function(){
        var table = $('.js-dataTable-full').DataTable({
            paging: false,
            ordering: false,
            dom: 'lrtip'
        });
        $('#customSearch').on('keyup', function () {
            console.log(this.value);
            table.search(this.value).draw();
        });
        $('#customSearch').focus();           
    });
</script>
@endsection