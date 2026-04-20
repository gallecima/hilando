@extends('layouts.backend')

@section('actions')
    <a href="{{ route('admin.categories.create') }}" type="button" class="ms-2 btn btn-sm btn-alt-primary">Nueva Categoría</a>
@endsection

@section('content')
<!-- Hero -->
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-1">
                    Categorías
                </h1>
                <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                    Administración de categorías de productos
                </h2>
            </div>
            <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-alt">
                    <li class="breadcrumb-item">
                        <a class="link-fx" href="javascript:void(0)">Comercio</a>
                    </li>
                    <li class="breadcrumb-item" aria-current="page">
                        Categorías
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

    @if(session('error'))
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            One.helpers('jq-notify', {
                type: 'danger',
                icon: 'fa fa-times-circle me-1',
                from: 'bottom',
                message: @json(session('error'))
            });
        });
    </script>
    @endif

    <div class="block block-rounded">
        <div class="block-content block-content-full overflow-x-auto">
            <table class="table table-striped table-vcenter js-dataTable-full">
                <thead>
                    <tr>
                        <th width="50">ID</th>
                        <th>Nombre</th>
                        <th>Slug</th>
                        <th>Categoría Padre</th>
                        <th>Activo</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $category)
                    <tr>
                        <td>{{ $category->id }}</td>
                        <td>{{ $category->name }}</td>
                        <td><code>{{ $category->slug }}</code></td>
                        <td>{{ $category->parent ? $category->parent->name : '-' }}</td>
                        <td>
                            <span class="badge {{ $category->is_active ? 'bg-success' : 'bg-danger' }}">
                                {{ $category->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-alt-secondary" data-bs-toggle="tooltip" title="Editar Categoría">
                                    <i class="fa fa-fw fa-pencil-alt"></i>
                                </a>
                                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-alt-secondary" data-bs-toggle="tooltip" title="Eliminar Categoría" onclick="return confirm('¿Estás seguro de eliminar esta categoría?')">
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
            order: [[3, 'asc']],
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