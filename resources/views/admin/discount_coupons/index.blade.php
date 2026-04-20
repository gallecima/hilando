@extends('layouts.backend')

@section('actions')
    <a href="{{ route('admin.discount-coupons.create') }}" class="ms-2 btn btn-sm btn-alt-primary">Nuevo Cupón</a>
@endsection

@section('content')
<!-- Hero -->
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-1">Cupones de Descuento</h1>
                <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                    Administración de cupones disponibles para el checkout
                </h2>
            </div>
            <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-alt">
                    <li class="breadcrumb-item">
                        <a class="link-fx" href="#">Comercio</a>
                    </li>
                    <li class="breadcrumb-item" aria-current="page">
                        Cupones de Descuento
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
                        <th>Código</th>
                        <th>Tipo</th>
                        <th>Valor</th>
                        <th>Usos</th>
                        <th>Desde</th>
                        <th>Hasta</th>
                        <th>Activo</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach($coupons as $coupon)
                    <tr>
                        <td>{{ $coupon->id }}</td>
                        <td><code>{{ $coupon->code }}</code></td>
                        <td>{{ $coupon->discount_type === 'percentage' ? 'Porcentaje' : 'Importe fijo' }}</td>
                        <td>
                            @if($coupon->discount_type === 'percentage')
                                {{ $coupon->discount_value }}%
                            @else
                                ${{ number_format($coupon->discount_value, 2, ',', '.') }}
                            @endif
                        </td>
                        <td>{{ $coupon->uses }}/{{ $coupon->max_uses ?? '∞' }}</td>
                        <td>{{ $coupon->valid_from->format('d/m/Y') }}</td>
                        <td>{{ $coupon->valid_until->format('d/m/Y') }}</td>
                        <td>
                            <span class="badge {{ $coupon->is_active ? 'bg-success' : 'bg-danger' }}">
                                {{ $coupon->is_active ? 'Sí' : 'No' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group">
                                <a href="{{ route('admin.discount-coupons.edit', $coupon) }}" class="btn btn-sm btn-alt-secondary" data-bs-toggle="tooltip" title="Editar cupón">
                                    <i class="fa fa-fw fa-pencil-alt"></i>
                                </a>
                                <form action="{{ route('admin.discount-coupons.destroy', $coupon) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-alt-secondary" data-bs-toggle="tooltip" title="Eliminar cupón" onclick="return confirm('¿Eliminar este cupón?')">
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