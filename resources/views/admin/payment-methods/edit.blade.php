@extends('layouts.backend')

@section('content')
<!-- Hero -->
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-1">Formas de pago</h1>
                <h2 class="fs-base lh-base fw-medium text-muted mb-0">Editar forma de pago</h2>
            </div>
            <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-alt">
                    <li class="breadcrumb-item"><a class="link-fx" href="#">Configuración</a></li>
                    <li class="breadcrumb-item"><a class="link-fx" href="{{ route('admin.payment-methods.index') }}">Formas de pago</a></li>
                    <li class="breadcrumb-item" aria-current="page">Editar</li>
                </ol>
            </nav>
        </div>
    </div>
</div>
<!-- END Hero -->

<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Editar forma de pago</h3>
        </div>
        <div class="block-content block-content-full">
            <form action="{{ route('admin.payment-methods.update', $paymentMethod) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-lg-4">
                        <p class="fs-sm text-muted">
                            Modificá los datos de esta forma de pago según sea necesario.
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

                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $paymentMethod->name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">Tipo</label>
                            <select name="type" id="type" class="form-select" required>
                                <option value="">-- Seleccionar --</option>
                                <option value="mercadopago" {{ old('type', $paymentMethod->type) == 'mercadopago' ? 'selected' : '' }}>Mercado Pago</option>
                                <option value="transferencia" {{ old('type', $paymentMethod->type) == 'transferencia' ? 'selected' : '' }}>Transferencia bancaria</option>
                                <option value="contraentrega" {{ old('type', $paymentMethod->type) == 'contraentrega' ? 'selected' : '' }}>Contraentrega</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="config" class="form-label">Configuración (JSON)</label>
                            <textarea class="form-control" id="config" name="config" rows="4">{{ old('config', json_encode($paymentMethod->config, JSON_PRETTY_PRINT)) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="instructions" class="form-label">Instrucciones para el cliente</label>
                            <textarea class="form-control" id="instructions" name="instructions" rows="3">{{ old('instructions', $paymentMethod->instructions) }}</textarea>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="active" name="active" value="1" {{ old('active', $paymentMethod->active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="active">Activo</label>
                        </div>

                        <div class="d-flex">
                            <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-alt-primary me-2">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Guardar cambios
                            </button>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection