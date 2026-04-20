@extends('layouts.backend')

@section('actions')
    <a href="{{ route('admin.shipping-points.create') }}" class="ms-2 btn btn-sm btn-alt-primary">Nuevo Punto</a>
@endsection

@section('content')
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-1">Puntos de Envío</h1>
                <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                    Sucursales, puntos de cobertura y referencias geográficas para sugerir métodos cercanos.
                </h2>
            </div>
            <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-alt">
                    <li class="breadcrumb-item"><a class="link-fx" href="javascript:void(0)">Gestión</a></li>
                    <li class="breadcrumb-item" aria-current="page">Puntos de Envío</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="content">
    <div class="block block-rounded">
        <div class="block-content block-content-full overflow-x-auto">
            <table class="table table-striped table-vcenter js-dataTable-full">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Proveedor</th>
                        <th>Zona</th>
                        <th>Radio</th>
                        <th>Coords</th>
                        <th>Activo</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shippingPoints as $point)
                    <tr>
                        <td>{{ $point->name }}</td>
                        <td>{{ $point->provider ?: '-' }}</td>
                        <td>{{ $point->zone_name ?: '-' }}</td>
                        <td>{{ $point->service_radius_km ? number_format($point->service_radius_km, 2, ',', '.') . ' km' : '-' }}</td>
                        <td>
                            @if($point->latitude && $point->longitude)
                                {{ number_format($point->latitude, 5, ',', '.') }}, {{ number_format($point->longitude, 5, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $point->is_active ? 'bg-success' : 'bg-danger' }}">
                                {{ $point->is_active ? 'Sí' : 'No' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group">
                                <a href="{{ route('admin.shipping-points.edit', $point) }}" class="btn btn-sm btn-alt-secondary" data-bs-toggle="tooltip" title="Editar">
                                    <i class="fa fa-fw fa-pencil-alt"></i>
                                </a>
                                <form action="{{ route('admin.shipping-points.destroy', $point) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-alt-secondary" data-bs-toggle="tooltip" title="Eliminar" onclick="return confirm('¿Eliminar este punto de envío?')">
                                        <i class="fa fa-fw fa-times"></i>
                                    </button>
                                </form>
                            </div>
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
        $('.js-dataTable-full').DataTable({
            order: [[0, 'asc']],
            paging: false,
            dom: 'lrtip'
        });
    });
</script>
@endsection
