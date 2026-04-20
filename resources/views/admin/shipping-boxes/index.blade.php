@extends('layouts.backend')

@section('actions')
    <a href="{{ route('admin.shipping-boxes.create') }}" class="ms-2 btn btn-sm btn-alt-primary">Nueva Caja</a>
@endsection

@section('content')
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-1">Cajas de Envío</h1>
                <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                    Definí capacidades físicas para empaquetar productos según proveedor y método.
                </h2>
            </div>
            <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-alt">
                    <li class="breadcrumb-item"><a class="link-fx" href="javascript:void(0)">Gestión</a></li>
                    <li class="breadcrumb-item" aria-current="page">Cajas de Envío</li>
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
                        <th>Código</th>
                        <th>Proveedor</th>
                        <th>Medidas internas</th>
                        <th>Peso máx.</th>
                        <th>Peso caja</th>
                        <th>Activo</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shippingBoxes as $box)
                    <tr>
                        <td>{{ $box->name }}</td>
                        <td>{{ $box->code }}</td>
                        <td>{{ $box->provider ?: '-' }}</td>
                        <td>{{ number_format($box->inner_length, 2, ',', '.') }} x {{ number_format($box->inner_width, 2, ',', '.') }} x {{ number_format($box->inner_height, 2, ',', '.') }} cm</td>
                        <td>{{ number_format($box->max_weight, 2, ',', '.') }} kg</td>
                        <td>{{ number_format($box->box_weight, 2, ',', '.') }} kg</td>
                        <td>
                            <span class="badge {{ $box->is_active ? 'bg-success' : 'bg-danger' }}">
                                {{ $box->is_active ? 'Sí' : 'No' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group">
                                <a href="{{ route('admin.shipping-boxes.edit', $box) }}" class="btn btn-sm btn-alt-secondary" data-bs-toggle="tooltip" title="Editar">
                                    <i class="fa fa-fw fa-pencil-alt"></i>
                                </a>
                                <form action="{{ route('admin.shipping-boxes.destroy', $box) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-alt-secondary" data-bs-toggle="tooltip" title="Eliminar" onclick="return confirm('¿Eliminar esta caja de envío?')">
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
