@extends('layouts.backend')

@section('content')
<!-- Hero -->
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-1">Cupones</h1>
                <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                    Edición de cupones de descuento
                </h2>
            </div>
            <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3">
                <ol class="breadcrumb breadcrumb-alt">
                    <li class="breadcrumb-item"><a href="#">Comercio</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.discount-coupons.index') }}">Cupones</a></li>
                    <li class="breadcrumb-item" aria-current="page">{{ $coupon->code }}</li>
                </ol>
            </nav>
        </div>
    </div>
</div>
<!-- END Hero -->

<div class="content">
    <form action="{{ route('admin.discount-coupons.update', $coupon) }}" method="POST">
        @csrf
        @method('PUT')

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
            <div class="col-lg-8">
                <div class="block block-rounded">
                    <div class="block-header block-header-default">
                        <h3 class="block-title">Datos del Cupón</h3>
                    </div>
                    <div class="block-content block-content-full">

                        <div class="mb-3">
                            <label for="code" class="form-label">Código</label>
                            <input type="text" class="form-control" name="code" id="code"
                                value="{{ old('code', $coupon->code) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="discount_type" class="form-label">Tipo de descuento</label>
                            <select name="discount_type" id="discount_type" class="form-select" required>
                                <option value="percentage" {{ old('discount_type', $coupon->discount_type) == 'percentage' ? 'selected' : '' }}>Porcentaje</option>
                                <option value="fixed" {{ old('discount_type', $coupon->discount_type) == 'fixed' ? 'selected' : '' }}>Importe fijo</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="discount_value" class="form-label">Valor del descuento</label>
                            <input type="number" step="0.01" class="form-control" name="discount_value" id="discount_value"
                                value="{{ old('discount_value', $coupon->discount_value) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="max_uses" class="form-label">Stock de usos del cupón</label>
                            <input type="number" class="form-control" name="max_uses" id="max_uses"
                                value="{{ old('max_uses', $coupon->max_uses) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="valid_from" class="form-label">Fecha de emisión</label>
                            <input type="date" class="form-control" name="valid_from" id="valid_from" value="{{ old('valid_from', $coupon->valid_from->format('Y-m-d')) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="valid_until" class="form-label">Fecha de vencimiento</label>
                            <input type="date" class="form-control" name="valid_until" id="valid_until" value="{{ old('valid_until', $coupon->valid_until->format('Y-m-d')) }}" required>
                        </div>

                    </div>
                </div>

                <div class="d-flex">
                    <a href="{{ route('admin.discount-coupons.index') }}" class="btn btn-alt-primary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection