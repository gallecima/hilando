@extends('layouts.backend')

@section('content')
<!-- Hero -->
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-1">Perfiles</h1>
                <h2 class="fs-base lh-base fw-medium text-muted mb-0">Gestión de perfiles de usuario</h2>
            </div>
            <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-alt">
                    <li class="breadcrumb-item"><a class="link-fx" href="#">Configuración</a></li>
                    <li class="breadcrumb-item"><a class="link-fx" href="{{ route('admin.perfiles.index') }}">Perfiles</a></li>
                    <li class="breadcrumb-item" aria-current="page">Nuevo</li>
                </ol>
            </nav>
        </div>
    </div>
</div>
<!-- END Hero -->

<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Nuevo Perfil</h3>
        </div>
        <div class="block-content block-content-full">
            
            <form action="{{ route('admin.perfiles.store') }}" method="POST">
                @csrf

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="nombre" class="form-label">Nombre del Perfil</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="{{ old('nombre') }}" required>
                    </div>
                </div>

                <h5 class="fw-bold">Opciones de Menú</h5>
                <table class="table table-striped table-vcenter">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Nombre</th>
                            <th>Ruta</th>
                            <th>Grupo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($menus as $menu)
                            <tr>
                                <td style="width: 40px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="menus[]" value="{{ $menu->id }}"
                                            {{ in_array($menu->id, old('menus', [])) ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td><i class="{{ $menu->icono }} me-2"></i> {{ $menu->nombre }}</td>
                                <td>{{ $menu->ruta }}</td>
                                <td>{{ $menu->grupo }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="d-flex mt-4">
                    <a href="{{ route('admin.perfiles.index') }}" class="btn btn-alt-primary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Crear Perfil</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection