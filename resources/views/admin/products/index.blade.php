@extends('layouts.backend')

@section('actions')
    <a href="{{ route('admin.products.create') }}" class="ms-2 btn btn-sm btn-alt-primary">Nuevo Producto</a>
@endsection

@section('content')
<!-- Hero -->
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-1">Productos</h1>
                <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                    Gestión de productos del catálogo
                </h2>
            </div>
            <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-alt">
                    <li class="breadcrumb-item">
                        <a class="link-fx" href="#">Comercio</a>
                    </li>
                    <li class="breadcrumb-item" aria-current="page">
                        Productos
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
            <form id="product-order-form" action="{{ route('admin.products.order') }}" method="POST" class="d-none">
                @csrf
                @method('PATCH')
            </form>
            <div class="d-flex justify-content-end mb-3">
                <button type="submit" form="product-order-form" class="btn btn-sm btn-alt-primary">Guardar orden</button>
            </div>
            <table class="table table-striped table-vcenter js-dataTable-full">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th width="120">Orden</th>
                        <th>Nombre</th>
                        <th>SKU</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Descarga</th>
                        <th>Activo</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                    <tr>
                        <td>{{ $product->id }}</td>
                        <td>
                            <input
                                type="number"
                                class="form-control form-control-sm"
                                name="orders[{{ $product->id }}]"
                                value="{{ old('orders.' . $product->id, $product->order) }}"
                                min="0"
                                placeholder="-"
                                form="product-order-form"
                            >
                        </td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->sku }}</td>
                        <td>
                            @if($product->has_discount_price)
                                <small class="text-muted text-decoration-line-through d-block">${{ number_format((float) $product->base_price, 2) }}</small>
                            @endif
                            ${{ number_format((float) $product->price, 2) }}
                        </td>
                        <td>{{ $product->stock }}</td>
                        <td>
                            @if($product->has_downloadable_files)
                                <span class="badge bg-info">{{ count($product->downloadable_files) }} archivo{{ count($product->downloadable_files) === 1 ? '' : 's' }}</span>
                            @else
                                <span class="badge bg-secondary">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $product->is_active ? 'bg-success' : 'bg-danger' }}">
                                {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-alt-secondary">
                                <i class="fa fa-fw fa-pencil-alt"></i>
                            </a>
                            <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" onclick="return confirm('¿Eliminar producto?')" class="btn btn-sm btn-alt-secondary">
                                    <i class="fa fa-fw fa-times"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-3">
                {{ $products->links() }}
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
