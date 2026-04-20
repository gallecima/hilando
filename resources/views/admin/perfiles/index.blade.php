@extends('layouts.backend')

@section('actions')
    <a href="{{ route('admin.perfiles.create') }}" type="button" class="ms-2 btn btn-sm btn-alt-primary">Nuevo Perfil</a>
@endsection

@section('content')
<!-- Hero -->
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-1">
                    Perfiles
                </h1>
                <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                    Gestión de perfiles de usuario
                </h2>
            </div>
            <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-alt">
                    <li class="breadcrumb-item">
                        <a class="link-fx" href="#">Configuración</a>
                    </li>
                    <li class="breadcrumb-item" aria-current="page">
                        Perfiles
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
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Menús Asignados</th>
                        <th>Usuarios</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($perfiles as $perfil)
                    <tr>
                        <td>{{ $perfil->id }}</td>
                        <td>{{ $perfil->nombre }}</td>
                        <td>{{ $perfil->menus->pluck('nombre')->join(', ') }}</td>
                        <td>{{ $perfil->users()->count() }}</td>
                        <td class="text-end">
                            <div class="btn-group">
                                <a href="{{ route('admin.perfiles.edit', $perfil) }}" class="btn btn-sm btn-alt-secondary" data-bs-toggle="tooltip" title="Editar Perfil">
                                    <i class="fa fa-fw fa-pencil-alt"></i>
                                </a>
                                <form action="{{ route('admin.perfiles.destroy', $perfil) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-alt-secondary" data-bs-toggle="tooltip" title="Eliminar Perfil" onclick="return confirm('¿Estás seguro de eliminar este perfil?')">
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