@extends('layouts.backend')

@section('content')
<!-- Hero -->
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-1">Cupones de Descuento</h1>
                <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                    Crear nuevo cupón para aplicar en el checkout
                </h2>
            </div>
            <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-alt">
                    <li class="breadcrumb-item">
                        <a class="link-fx" href="#">Comercio</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a class="link-fx" href="{{ route('admin.discount-coupons.index') }}">Cupones</a>
                    </li>
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
            <h3 class="block-title">Nuevo Cupón</h3>
        </div>
        <div class="block-content block-content-full">

            <div class="row">
                <div class="col-lg-4">
                    <p class="fs-sm text-muted">
                        Creá cupones con código para aplicar descuentos en el proceso de compra. Podés limitar su uso y establecer fechas de validez.
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
                    <form action="{{ route('admin.discount-coupons.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="code" class="form-label">Código del Cupón</label>
                            <input type="text" class="form-control" id="code" name="code" value="{{ old('code') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="discount_type" class="form-label">Tipo de Descuento</label>
                            <select class="form-control" id="discount_type" name="discount_type" required>
                                <option value="percentage" {{ old('discount_type') === 'percentage' ? 'selected' : '' }}>Porcentaje (%)</option>
                                <option value="fixed" {{ old('discount_type') === 'fixed' ? 'selected' : '' }}>Importe Fijo ($)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="discount_value" class="form-label">Valor del Descuento</label>
                            <input type="number" step="0.01" class="form-control" id="discount_value" name="discount_value" value="{{ old('discount_value') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="max_uses" class="form-label">Stock de usos del cupón</label>
                            <input type="number" class="form-control" id="max_uses" name="max_uses" value="{{ old('max_uses') }}" placeholder="Opcional">
                        </div>

                        <div class="mb-3">
                            <label for="valid_from" class="form-label">Fecha de Emisión</label>
                            <input type="date" class="form-control" id="valid_from" name="valid_from" value="{{ old('valid_from') }}">
                        </div>

                        <div class="mb-3">
                            <label for="valid_until" class="form-label">Fecha de Vencimiento</label>
                            <input type="date" class="form-control" id="valid_until" name="valid_until" value="{{ old('valid_until') }}">
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Activo</label>
                        </div>

                        <div class="d-flex">
                            <a href="{{ route('admin.discount-coupons.index') }}" class="btn btn-alt-primary me-2">
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