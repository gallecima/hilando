@extends('layouts.backend')

@section('actions')
    <a href="{{ route('admin.usuarios.create') }}" class="ms-2 btn btn-sm btn-alt-primary">Nuevo Usuario</a>
@endsection

@section('content')
<!-- Hero -->
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-1">Usuarios</h1>
                <h2 class="fs-base lh-base fw-medium text-muted mb-0">Gestión de usuarios del sistema</h2>
            </div>
            <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-alt">
                    <li class="breadcrumb-item"><a class="link-fx" href="#">Configuración</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Usuarios</li>
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
                        <th></th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Perfil</th>
                        <th>Activo</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($usuarios as $usuario)
                    <tr>
                        <td>
                        
                            @if ($usuario->profile_photo)
                                    <img src="{{ asset('storage/' . $usuario->profile_photo) }}" alt="Foto de perfil" class="img-avatar img-avatar-thumb" style="max-height:40px; max-width:40px">
                            @endif                        

                        </td>
                        <td>{{ $usuario->name }}</td>
                        <td>{{ $usuario->email }}</td>
                        <td>{{ $usuario->perfil->nombre ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $usuario->active ? 'bg-success' : 'bg-danger' }}">
                                {{ $usuario->active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group">
                                <a href="{{ route('admin.usuarios.edit', $usuario) }}" class="btn btn-sm btn-alt-secondary" data-bs-toggle="tooltip" title="Editar Usuario">
                                    <i class="fa fa-fw fa-pencil-alt"></i>
                                </a>

                                @if(strtoupper($usuario->perfil->nombre) !== 'MASTER')
                                    <form action="{{ route('admin.usuarios.destroy', $usuario) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-alt-secondary" data-bs-toggle="tooltip" title="Eliminar Usuario" onclick="return confirm('¿Estás seguro de eliminar este usuario?')">
                                            <i class="fa fa-fw fa-times"></i>
                                        </button>
                                    </form>
                                @endif

                                <form action="{{ route('admin.usuarios.send-reset', $usuario) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-alt-secondary" data-bs-toggle="tooltip" title="Enviar email de recuperación">
                                        <i class="fa fa-key"></i>
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