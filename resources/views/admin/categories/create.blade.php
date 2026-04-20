@extends('layouts.backend')

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
                <a class="link-fx" href="{{ route('admin.categories.index') }}">Categorías</a>
            </li>
            <li class="breadcrumb-item" aria-current="page">
                Nueva
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
            <h3 class="block-title">Nueva Categoría</h3>
        </div>
        <div class="block-content block-content-full">

              <div class="row">
                <div class="col-lg-4">
                    <p class="fs-sm text-muted">
                        Define las categorías que tendrán tus productos.
                    </p>
                    <p class="fs-sm text-muted">
                        Cada categoría tendrá un set de atributos definidos (color, talle, tamaño, potencia, etc.). Si aun no existen los atributos necesarios para esta categoría, deberás crearlos <a href="/admin/attributes">aquí</a>.
                    </p>
                    <p class="fs-sm text-muted">
                        Imagen recomendada: 16:9 / Icono: 1:1. Puedes subir cualquier imagen y recortarlas a la proporción necesaria luego.
                    </p>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
                <div class="col-lg-8 space-y-5">         

                    <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="parent_id" class="form-label">Categoría Padre</label>
                            <select class="form-control" id="parent_id" name="parent_id">
                                <option value="">-- Ninguna --</option>
                                @foreach($categories as $parent)
                                    <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                        {{ $parent->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="attributes" class="form-label">Atributos disponibles</label>
                            <select class="form-select" id="attributes" name="attributes[]" multiple size="{{ min(max(count($attributes), 6), 12) }}">
                                @foreach($attributes as $attribute)
                                    <option value="{{ $attribute->id }}" {{ (collect(old('attributes'))->contains($attribute->id)) ? 'selected' : '' }}>
                                        {{ $attribute->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Podés seleccionar varios manteniendo presionada la tecla Ctrl o Cmd.</div>
                        </div>             
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Descripción</label>
                            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        </div>
                                        
                        <div class="mb-3">
                            <label for="order" class="form-label">Orden en listados</label>
                            <input type="number" class="form-control" id="order" name="order" value="{{ old('order', 0) }}">
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Imagen Principal</label>
                            <input type="file" class="form-control" id="image" name="image">
                        </div>

                        <div class="mb-3">
                            <label for="icon" class="form-label">Icono de Categoría</label>
                            <input type="file" class="form-control" id="icon" name="icon">
                        </div>                

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="is_active">Activo</label>
                        </div>

                        <div class="d-flex">
                            <a href="{{ route('admin.categories.index') }}" class="btn btn-alt-primary me-2">
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
