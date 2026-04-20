@extends('layouts.backend')

@section('content')
<!-- Hero -->
<div class="bg-body-light">
    <div class="content content-full">
    <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
        <div class="flex-grow-1">
        <h1 class="h3 fw-bold mb-1">
            Menús
        </h1>
        <h2 class="fs-base lh-base fw-medium text-muted mb-0">
            Opciones del menú lateral
        </h2>
        </div>
        <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-alt">
            <li class="breadcrumb-item">
            <a class="link-fx" href="javascript:void(0)">Configuración</a>
            </li>
            <li class="breadcrumb-item" aria-current="page">
                <a class="link-fx" href="{{ route('admin.menus.index') }}">Menús</a>
            </li>
            <li class="breadcrumb-item" aria-current="page">
                Nuevo
            </li>
        </ol>
        </nav>
    </div>
    </div>
</div>
<!-- END Hero -->

<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Nuevo Menú</h3>
        </div>
        <div class="block-content block-content-full">

              <div class="row">
                <div class="col-lg-4">
                  <p class="fs-sm text-muted">
                    Estas son las opciones disponibles en el menú lateral.
                  </p>
                  <p class="fs-sm text-muted">
                    Para los iconos, podés usar <a href="https://fontawesome.com/icons" class="text-info" target="_blank">Font Awesome</a> o <a href="https://simplelineicons.github.io/" class="text-info" target="_blank">Simple Line Icons</a>.
                  </p>
                </div>
                <div class="col-lg-8 space-y-5"> 

                    <form action="{{ route('admin.menus.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" value="{{ old('nombre') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="ruta" class="form-label">Ruta</label>
                            <input type="text" class="form-control" id="ruta" name="ruta" value="{{ old('ruta') }}">
                        </div>

                        <div class="mb-3">
                            <label for="icono" class="form-label">Icono</label>
                            <input type="text" class="form-control" id="icono" name="icono" value="{{ old('icono') }}">
                        </div>

                        <div class="mb-3">
                            <label for="menu_group_id">Grupo</label>
                            <select name="menu_group_id" class="form-select">
                                <option value="">-- Ninguno --</option>
                                @foreach ($menuGroups as $group)
                                    <option value="{{ $group->id }}" {{ old('menu_group_id', $menu->menu_group_id ?? '') == $group->id ? 'selected' : '' }}>
                                        {{ $group->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="orden" class="form-label">Order</label>
                            <input type="number" class="form-control" id="orden" name="orden" value="{{ old('orden') }}">
                        </div>
                                                
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1" checked>
                            <label class="form-check-label" for="activo">Activo</label>
                        </div>

                        <div class="d-flex">
                            <a href="{{ route('admin.menus.index') }}" class="btn btn-alt-primary me-2">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Guardar
                            </button>
                        </div>
                    </form>

                </div>
              </div>

        </div>
    </div>
</div>
@endsection