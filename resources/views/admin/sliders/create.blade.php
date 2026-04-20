@extends('layouts.backend')

@section('content')
<!-- Hero -->
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-1">
                    Sliders
                </h1>
                <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                    Gestor de sliders para el frontend
                </h2>
            </div>
            <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-alt">
                    <li class="breadcrumb-item">
                        <a class="link-fx" href="javascript:void(0)">Contenido</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a class="link-fx" href="{{ route('admin.sliders.index') }}">Sliders</a>
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
            <h3 class="block-title">Nuevo Slider</h3>
        </div>
        <div class="block-content block-content-full">

            <div class="row">
                <div class="col-lg-4">
                    <p class="fs-sm text-muted">
                        Completá los datos del slider. El campo slug se generará automáticamente.
                    </p>
                </div>

                <div class="col-lg-8 space-y-5"> 

                    <form action="{{ route('admin.sliders.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" value="{{ old('nombre') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug (opcional, se genera automáticamente si lo dejás vacío)</label>
                            <input type="text" class="form-control" id="slug" name="slug" value="{{ old('slug') }}">
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1" {{ old('activo', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="activo">Activo</label>
                        </div>

                        <div class="d-flex">
                            <a href="{{ route('admin.sliders.index') }}" class="btn btn-alt-primary me-2">
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