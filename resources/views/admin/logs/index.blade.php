@extends('layouts.backend')

@section('actions')
    {{-- Acciones opcionales --}}
@endsection

@section('content')
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-1">Log de Actividades</h1>
                <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                    Registro de acciones del sistema (plataforma, administrativos y comerciales)
                </h2>
            </div>
        </div>
    </div>
</div>

<div class="content">
    {{-- === Filtros === --}}
    @php
        $from = $filters['from'] ?? now()->startOfMonth()->format('Y-m-d');
        $to   = $filters['to']   ?? now()->endOfMonth()->format('Y-m-d');
        $q    = $filters['q']    ?? '';
        $cat  = $filters['category'] ?? '';
        $user = $filters['user'] ?? '';
    @endphp

    <div class="block block-rounded mb-3">
        <div class="block-content block-content-full overflow-x-auto">
            <form method="GET" action="{{ route('admin.logs.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Desde</label>
                    <input type="date" name="from" class="form-control" value="{{ $from }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="to" class="form-control" value="{{ $to }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Categoría</label>
                    <select name="category" class="form-select">
                        <option value="">Todas</option>
                        <option value="plataforma" {{ $cat=='plataforma'?'selected':'' }}>Plataforma</option>
                        <option value="administrativos" {{ $cat=='administrativos'?'selected':'' }}>Administrativos</option>
                        <option value="comerciales" {{ $cat=='comerciales'?'selected':'' }}>Comerciales</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Usuario</label>
                    <input type="text" name="user" class="form-control" value="{{ $user }}" placeholder="Nombre o email">
                </div>

                <div class="col-12 mt-2">
                    <label class="form-label">Buscar texto</label>
                    <input type="text" name="q" class="form-control" value="{{ $q }}" placeholder="Descripción o tipo de evento">
                </div>

                <div class="col-12 d-flex gap-2 mt-3">
                    <button class="btn btn-primary">
                        <i class="fa fa-filter me-1"></i> Aplicar
                    </button>
                    <a href="{{ route('admin.logs.index') }}" class="btn btn-alt-secondary">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    {{-- === Tabla === --}}
    <div class="block block-rounded">
        <div class="block-content block-content-full overflow-x-auto">
            <table class="table table-striped table-vcenter js-dataTable-full">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Categoría</th>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th>Usuario</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $a)
                        <tr>
                            <td>{{ $a->id }}</td>
                            <td>
                                @php
                                    $icons = [
                                        'plataforma' => 'fa-cogs text-muted',
                                        'administrativos' => 'fa-briefcase text-info',
                                        'comerciales' => 'fa-shopping-cart text-success',
                                    ];
                                    $icon = $icons[$a->category] ?? 'fa-circle text-secondary';
                                @endphp
                                <i class="fa fa-fw {{ $icon }} me-1"></i>
                                <span class="fw-semibold text-capitalize">{{ $a->category }}</span>
                            </td>
                            <td>{{ $a->type ?? '-' }}</td>
                            <td>
                                <div>{{ $a->description }}</div>
                            </td>
                            <td>
                                @if($a->user)
                                    <div>{{ $a->user->name }}</div>
                                    <small class="text-muted">{{ $a->user->email }}</small>
                                @else
                                    <em class="text-muted">Sistema</em>
                                @endif
                            </td>
                            <td>
                                {{ optional($a->occurred_at)->format('d/m/Y H:i') ?? $a->created_at->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">
                                No se encontraron actividades en el rango o con los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Paginación --}}
            <div class="mt-3">
                {{ $activities->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function(){
        $('.js-dataTable-full').DataTable({
            order: [[0, 'desc']],
            paging: false,
            dom: 'lrtip'
        });
    });
</script>
@endsection
