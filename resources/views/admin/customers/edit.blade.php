@extends('layouts.backend')

@section('content')
@php
    $billing = optional($customer->billingData);
@endphp
<div class="bg-body-light">
    <div class="content content-full d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 fw-bold mb-0">Editar cliente</h1>
            <p class="fs-sm text-muted mb-0">Actualizá los datos del cliente y su información de facturación.</p>
        </div>
        <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-alt-secondary">
            <i class="fa fa-arrow-left me-1"></i> Volver al detalle
        </a>
    </div>
</div>

<div class="content">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="block block-rounded">
                <div class="block-header block-header-default">
                    <h3 class="block-title">Datos generales</h3>
                </div>
                <div class="block-content block-content-full overflow-x-auto">
                    <form method="POST" action="{{ route('admin.customers.update', $customer) }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $customer->name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $customer->email) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Teléfono</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $customer->phone) }}" placeholder="Opcional">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Documento</label>
                                <input type="text" name="document" class="form-control" value="{{ old('document', $customer->document) }}" placeholder="DNI / CUIT">
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" @checked(old('is_active', $customer->is_active))>
                                    <label class="form-check-label" for="is_active">Cliente activo</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" value="1" id="is_wholesaler" name="is_wholesaler" @checked(old('is_wholesaler', $customer->is_wholesaler))>
                                    <label class="form-check-label" for="is_wholesaler">Cliente mayorista</label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h4 class="fw-semibold">Datos de facturación</h4>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Razón social</label>
                                <input type="text" name="billing[business_name]" class="form-control" value="{{ old('billing.business_name', $billing->business_name) }}" placeholder="Nombre / Razón social">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">CUIT / Documento</label>
                                <input type="text" name="billing[document_number]" class="form-control" value="{{ old('billing.document_number', $billing->document_number) }}" placeholder="Sólo números">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Condición frente al IVA</label>
                                <input type="text" name="billing[tax_status]" class="form-control" value="{{ old('billing.tax_status', $billing->tax_status) }}" placeholder="Responsable Inscripto, Monotributista, etc">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tipo de comprobante</label>
                                @php
                                    $invoiceType = old('billing.invoice_type', $billing->invoice_type ?? 'C');
                                @endphp
                                <select name="billing[invoice_type]" class="form-select">
                                    <option value="A" @selected($invoiceType === 'A')>Factura A</option>
                                    <option value="B" @selected($invoiceType === 'B')>Factura B</option>
                                    <option value="C" @selected($invoiceType === 'C')>Factura C</option>
                                </select>
                                <small class="text-muted">Seleccioná el tipo de comprobante que recibe el cliente.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Domicilio fiscal</label>
                                <input type="text" name="billing[address_line]" class="form-control" value="{{ old('billing.address_line', $billing->address_line) }}" placeholder="Calle y número">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Ciudad</label>
                                <input type="text" name="billing[city]" class="form-control" value="{{ old('billing.city', $billing->city) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Provincia</label>
                                <input type="text" name="billing[province]" class="form-control" value="{{ old('billing.province', $billing->province) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Código postal</label>
                                <input type="text" name="billing[postal_code]" class="form-control" value="{{ old('billing.postal_code', $billing->postal_code) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">País</label>
                                <input type="text" name="billing[country]" class="form-control" value="{{ old('billing.country', $billing->country ?? 'Argentina') }}">
                            </div>
                        </div>

                        <div class="d-flex gap-2 justify-content-end mt-4">
                            <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-alt-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save me-1"></i> Guardar cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
