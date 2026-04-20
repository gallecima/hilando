@extends('layouts.backend')

@section('actions')
    <a href="{{ route('admin.shipmentmethod.create') }}" class="ms-2 btn btn-sm btn-alt-primary">Nuevo Método</a>
@endsection

@section('content')
<!-- Hero -->
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-1">Métodos de Envío</h1>
                <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                    Gestión de métodos de envío disponibles para el checkout
                </h2>
            </div>
            <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-alt">
                    <li class="breadcrumb-item">
                        <a class="link-fx" href="#">Comercio</a>
                    </li>
                    <li class="breadcrumb-item" aria-current="page">
                        Métodos de Envío
                    </li>
                </ol>
            </nav>
        </div>
    </div>
</div>
<!-- END Hero -->

<div class="content">

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

    <div class="block block-rounded">
        <div class="block-content block-content-full overflow-x-auto">
            <table class="table table-striped table-vcenter js-dataTable-full">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Importe</th>
                        <th>Demora</th>
                        <th>Descuento</th>
                        <th>Punto</th>
                        <th>Cajas</th>
                        <th>Peso</th>
                        <th>Dimensiones</th>
                        <th>Zona</th>
                        <th>Tipo</th>
                        <th>Activo</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shipmentMethods as $method)
                    <tr>
                        <td>{{ $method->id }}</td>
                        <td>{{ $method->name }}</td>
                        <td>${{ number_format($method->amount, 2, ',', '.') }}</td>
                        <td>{{ $method->delay ?? '-' }}</td>
                        <td>
                            @if($method->discount_type === 'percent')
                                {{ $method->discount_value }}%
                            @elseif($method->discount_type === 'amount')
                                ${{ number_format($method->discount_value, 2, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($method->shippingPoint)
                                {{ $method->shippingPoint->name }}
                                @if($method->allow_nearby_match)
                                    <div class="small text-muted">
                                        Cercanía: {{ number_format((float) ($method->nearby_radius_km ?: $method->shippingPoint->service_radius_km), 2, ',', '.') }} km
                                    </div>
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $method->shippingBoxes->isNotEmpty() ? $method->shippingBoxes->pluck('name')->join(', ') : '-' }}</td>
                        <td>{{ $method->weight_limit ? $method->weight_limit . ' kg' : '-' }}</td>
                        <td>
                            @if($method->width_limit || $method->height_limit || $method->length_limit)
                                {{ $method->width_limit ?? '-' }} x {{ $method->height_limit ?? '-' }} x {{ $method->length_limit ?? '-' }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $method->zone_name ?? '-' }}</td>
                        <td>
                            @if($method->is_pickup)
                                <span class="badge bg-info">Pickup</span>
                            @else
                                <span class="badge bg-secondary">Envío a domicilio</span>
                            @endif
                        </td>                        
                        <td>
                            <span class="badge {{ $method->is_active ? 'bg-success' : 'bg-danger' }}">
                                {{ $method->is_active ? 'Sí' : 'No' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group">
                                <a href="{{ route('admin.shipmentmethod.edit', $method) }}" class="btn btn-sm btn-alt-secondary" data-bs-toggle="tooltip" title="Editar">
                                    <i class="fa fa-fw fa-pencil-alt"></i>
                                </a>
                                <form action="{{ route('admin.shipmentmethod.destroy', $method) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-alt-secondary" data-bs-toggle="tooltip" title="Eliminar" onclick="return confirm('¿Eliminar este método de envío?')">
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
        var table = $('.js-dataTable-full').DataTable({
            order: [[1, 'asc']],
            paging: false,
            dom: 'lrtip'
        });

        $('#customSearch').on('keyup', function () {
            table.search(this.value).draw();
        });
        $('#customSearch').focus();
    });
</script>
@endsection
