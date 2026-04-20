@extends('layouts.backend')

@section('actions')
    <a href="{{ route('admin.menu-groups.create') }}" type="button" class="ms-2 btn btn-sm btn-alt-primary">Nuevo Grupo</a>
@endsection

@section('content')
<!-- Hero -->
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-1">
                    Grupos de Menú
                </h1>
                <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                    Organización de los grupos de menús del sistema
                </h2>
            </div>
            <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-alt">
                    <li class="breadcrumb-item">
                        <a class="link-fx" href="javascript:void(0)">Configuración</a>
                    </li>
                    <li class="breadcrumb-item" aria-current="page">
                        Grupos de Menú
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
                        <th>Nombre</th>
                        <th>Orden</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($menuGroups as $group)
                    <tr>
                        <td>{{ $group->nombre }}</td>
                        <td>{{ $group->orden }}</td>
                        <td class="text-end">
                            <div class="btn-group">
                                <a href="{{ route('admin.menu-groups.edit', $group) }}" class="btn btn-sm btn-alt-secondary" data-bs-toggle="tooltip" title="Editar Grupo">
                                    <i class="fa fa-fw fa-pencil-alt"></i>
                                </a>
                                <form action="{{ route('admin.menu-groups.destroy', $group) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-alt-secondary" data-bs-toggle="tooltip" title="Eliminar Grupo" onclick="return confirm('¿Estás seguro de eliminar este grupo?')">
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