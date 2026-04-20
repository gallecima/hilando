@extends('layouts.backend')

@section('content')
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-1">Cajas de Envío</h1>
                <h2 class="fs-base lh-base fw-medium text-muted mb-0">Crear nueva caja logística</h2>
            </div>
            <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-alt">
                    <li class="breadcrumb-item"><a class="link-fx" href="javascript:void(0)">Gestión</a></li>
                    <li class="breadcrumb-item"><a class="link-fx" href="{{ route('admin.shipping-boxes.index') }}">Cajas de Envío</a></li>
                    <li class="breadcrumb-item" aria-current="page">Nuevo</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Nueva Caja de Envío</h3>
        </div>
        <div class="block-content block-content-full">
            <form action="{{ route('admin.shipping-boxes.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-lg-4">
                        <p class="fs-sm text-muted">
                            Definí dimensiones internas y peso máximo para que el checkout pueda agrupar productos automáticamente.
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
                    <div class="col-lg-8 space-y-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="code" class="form-label">Código</label>
                                <input type="text" class="form-control" id="code" name="code" value="{{ old('code') }}" placeholder="Se genera automáticamente si lo dejás vacío">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="provider" class="form-label">Proveedor</label>
                            <input type="text" class="form-control" id="provider" name="provider" value="{{ old('provider') }}">
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="inner_length" class="form-label">Largo interno (cm)</label>
                                <input type="number" step="0.01" class="form-control" id="inner_length" name="inner_length" value="{{ old('inner_length') }}" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="inner_width" class="form-label">Ancho interno (cm)</label>
                                <input type="number" step="0.01" class="form-control" id="inner_width" name="inner_width" value="{{ old('inner_width') }}" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="inner_height" class="form-label">Alto interno (cm)</label>
                                <input type="number" step="0.01" class="form-control" id="inner_height" name="inner_height" value="{{ old('inner_height') }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="max_weight" class="form-label">Peso máximo (kg)</label>
                                <input type="number" step="0.01" class="form-control" id="max_weight" name="max_weight" value="{{ old('max_weight') }}" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="box_weight" class="form-label">Peso propio caja (kg)</label>
                                <input type="number" step="0.01" class="form-control" id="box_weight" name="box_weight" value="{{ old('box_weight', '0') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="priority" class="form-label">Prioridad</label>
                                <input type="number" class="form-control" id="priority" name="priority" value="{{ old('priority', '0') }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notas</label>
                            <textarea class="form-control" id="notes" name="notes" rows="4">{{ old('notes') }}</textarea>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Activo</label>
                        </div>

                        <div class="d-flex">
                            <a href="{{ route('admin.shipping-boxes.index') }}" class="btn btn-alt-primary me-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
