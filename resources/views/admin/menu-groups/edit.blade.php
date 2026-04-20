@extends('layouts.backend')

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
            Organización de los grupos del menú lateral
        </h2>
        </div>
        <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-alt">
            <li class="breadcrumb-item">
                <a class="link-fx" href="javascript:void(0)">Configuración</a>
            </li>
            <li class="breadcrumb-item" aria-current="page">
                <a class="link-fx" href="{{ route('admin.menu-groups.index') }}">Grupos de Menú</a>
            </li>
            <li class="breadcrumb-item" aria-current="page">
                {{ $menuGroup->nombre }}
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
            <h3 class="block-title">Editar Grupo de Menú</h3>
        </div>
        <div class="block-content block-content-full">

            <div class="row">
                <div class="col-lg-4">
                    <p class="fs-sm text-muted">
                        Aquí puedes editar el nombre y el orden del grupo de menú.
                    </p>
                </div>

                <div class="col-lg-8 space-y-5">
                    <form action="{{ route('admin.menu-groups.update', $menuGroup) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" value="{{ old('nombre', $menuGroup->nombre) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="orden" class="form-label">Orden</label>
                            <input type="number" class="form-control" id="orden" name="orden" value="{{ old('orden', $menuGroup->orden) }}">
                        </div>

                        <div class="d-flex">
                            <a href="{{ route('admin.menu-groups.index') }}" class="btn btn-alt-primary me-2">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection