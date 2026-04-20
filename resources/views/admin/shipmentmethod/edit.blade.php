@extends('layouts.backend')

@section('content')
<!-- Hero -->
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-1">Métodos de Envío</h1>
                <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                    Editar método de envío existente
                </h2>
            </div>
            <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-alt">
                    <li class="breadcrumb-item">
                        <a class="link-fx" href="#">Comercio</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a class="link-fx" href="{{ route('admin.shipmentmethod.index') }}">Métodos de Envío</a>
                    </li>
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
            <h3 class="block-title">Editar Método de Envío</h3>
        </div>
        <div class="block-content block-content-full">
            <div class="row">
                <div class="col-lg-4">
                    <p class="fs-sm text-muted">
                        Modificá los datos del método de envío: costo, zona, tiempo estimado, descuentos y dimensiones.
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
                    <form action="{{ route('admin.shipmentmethod.update', $shipmentMethod) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre del método</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $shipmentMethod->name) }}" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="amount" class="form-label">Importe</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control" id="amount" name="amount" value="{{ old('amount', $shipmentMethod->amount) }}" required>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="min_cart_amount" class="form-label">Monto mínimo del carrito</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control" id="min_cart_amount" name="min_cart_amount" value="{{ old('min_cart_amount', $shipmentMethod->min_cart_amount ?? '') }}" placeholder="Ej: 10000.00">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="delay" class="form-label">Demora estimada</label>
                            <input type="text" class="form-control" id="delay" name="delay" value="{{ old('delay', $shipmentMethod->delay) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="discount_type" class="form-label">Tipo de Descuento</label>
                            <select class="form-select" id="discount_type" name="discount_type">
                                <option value="" {{ $shipmentMethod->discount_type === null ? 'selected' : '' }}>Sin descuento</option>
                                <option value="percent" {{ $shipmentMethod->discount_type === 'percent' ? 'selected' : '' }}>Porcentaje (%)</option>
                                <option value="amount" {{ $shipmentMethod->discount_type === 'amount' ? 'selected' : '' }}>Importe Fijo ($)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="discount_value" class="form-label">Valor del Descuento</label>
                            <input type="number" step="0.01" class="form-control" id="discount_value" name="discount_value" value="{{ old('discount_value', $shipmentMethod->discount_value) }}">
                        </div>

                        <div class="mb-3">
                            <label for="shipping_point_id" class="form-label">Punto de envío asociado</label>
                            <select class="form-select" id="shipping_point_id" name="shipping_point_id">
                                <option value="">Sin punto asociado</option>
                                @foreach($shippingPoints as $point)
                                    <option value="{{ $point->id }}" {{ (int) old('shipping_point_id', $shipmentMethod->shipping_point_id) === (int) $point->id ? 'selected' : '' }}>
                                        {{ $point->name }} @if($point->zone_name) - {{ $point->zone_name }} @endif
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Si vinculás un punto, el método podrá sugerirse por cercanía.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="allow_nearby_match" id="allow_nearby_match" value="1" {{ old('allow_nearby_match', $shipmentMethod->allow_nearby_match) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="allow_nearby_match">Permitir sugerencia por cercanía</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nearby_radius_km" class="form-label">Radio sugerido (km)</label>
                                <input type="number" step="0.01" class="form-control" id="nearby_radius_km" name="nearby_radius_km" value="{{ old('nearby_radius_km', $shipmentMethod->nearby_radius_km ?? '10') }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="postal_code" class="form-label">Código postal</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="postal_code" name="postal_code" value="{{ old('postal_code', $shipmentMethod->postal_code) }}" placeholder="Ej: 1425">
                                <button class="btn btn-outline-primary" type="button" id="btn_resolve_postcode">Autocompletar ubicación</button>
                            </div>
                            <small class="text-muted d-block mt-1" id="postcode-feedback">Podés autocompletar país, provincia y localidad usando código postal.</small>
                            <small class="text-danger d-none" id="postcode-error"></small>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="country_name" class="form-label">País</label>
                                <input type="text" class="form-control" id="country_name" name="country_name" value="{{ old('country_name', $shipmentMethod->country_name ?? optional($shipmentMethod->country)->name ?? 'Argentina') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="province_name" class="form-label">Provincia</label>
                                <input type="text" class="form-control" id="province_name" name="province_name" value="{{ old('province_name', $shipmentMethod->province_name ?? optional($shipmentMethod->province)->name) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="locality_name" class="form-label">Localidad</label>
                                <input type="text" class="form-control" id="locality_name" name="locality_name" value="{{ old('locality_name', $shipmentMethod->locality_name ?? optional($shipmentMethod->locality)->name) }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="weight_limit" class="form-label">Peso (kg)</label>
                                <input type="number" step="0.01" class="form-control" id="weight_limit" name="weight_limit" value="{{ old('weight_limit', $shipmentMethod->weight_limit) }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="width_limit" class="form-label">Ancho (cm)</label>
                                <input type="number" class="form-control" id="width_limit" name="width_limit" value="{{ old('width_limit', $shipmentMethod->width_limit) }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="height_limit" class="form-label">Alto (cm)</label>
                                <input type="number" class="form-control" id="height_limit" name="height_limit" value="{{ old('height_limit', $shipmentMethod->height_limit) }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="length_limit" class="form-label">Largo (cm)</label>
                                <input type="number" class="form-control" id="length_limit" name="length_limit" value="{{ old('length_limit', $shipmentMethod->length_limit) }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="shipping_boxes" class="form-label">Cajas permitidas</label>
                            @php
                                $selectedBoxes = old('shipping_boxes', $shipmentMethod->shippingBoxes->pluck('id')->all());
                                $selectedBoxes = array_map('intval', (array) $selectedBoxes);
                            @endphp
                            <select class="form-select" id="shipping_boxes" name="shipping_boxes[]" multiple size="6">
                                @foreach($shippingBoxes as $box)
                                    <option value="{{ $box->id }}" {{ in_array((int) $box->id, $selectedBoxes, true) ? 'selected' : '' }}>
                                        {{ $box->name }} ({{ number_format($box->inner_length, 0, ',', '.') }}x{{ number_format($box->inner_width, 0, ',', '.') }}x{{ number_format($box->inner_height, 0, ',', '.') }} cm / {{ number_format($box->max_weight, 2, ',', '.') }} kg)
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Si no seleccionás cajas, el método no restringe empaquetado.</small>
                        </div>


                        <div class="row mb-3">
                            <div class="col-md-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                        value="1" {{ old('is_active', $shipmentMethod->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Activo</label>
                                </div>
                            </div>

                            <div class="col-md-9">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="is_pickup" id="is_pickup"
                                        value="1" {{ old('is_pickup', $shipmentMethod->is_pickup) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_pickup">
                                        Es punto de retiro (pickup)
                                    </label>
                                </div>
                            </div>
                        </div>                      

                        <div class="d-flex">
                            <a href="{{ route('admin.shipmentmethod.index') }}" class="btn btn-alt-primary me-2">
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const countryInput = document.getElementById('country_name');
    const provinceInput = document.getElementById('province_name');
    const localityInput = document.getElementById('locality_name');
    const postcodeInput = document.getElementById('postal_code');
    const resolveBtn = document.getElementById('btn_resolve_postcode');
    const feedbackBox = document.getElementById('postcode-feedback');
    const errorBox = document.getElementById('postcode-error');

    if (!countryInput || !provinceInput || !localityInput || !postcodeInput) return;

    const resolveUrl = @json(route('api.location.resolve'));

    function setFeedback(message) {
        if (!feedbackBox) return;
        feedbackBox.textContent = message || '';
    }

    function setError(message) {
        if (!errorBox) return;
        if (!message) {
            errorBox.textContent = '';
            errorBox.classList.add('d-none');
            return;
        }
        errorBox.textContent = message;
        errorBox.classList.remove('d-none');
    }

    function clearMessages() {
        setError('');
        setFeedback('Podés autocompletar país, provincia y localidad usando código postal.');
    }

    async function fetchJson(url) {
        const response = await fetch(url, {
            headers: { 'Accept': 'application/json' },
        });

        const payload = await response.json().catch(() => null);
        if (!response.ok) {
            const message = payload && typeof payload === 'object'
                ? (payload.error || payload.message)
                : null;
            throw new Error(message || 'No se pudo completar la solicitud.');
        }
        return payload;
    }

    async function resolveByPostcode() {
        clearMessages();
        const postcode = String(postcodeInput.value || '').trim();
        if (!postcode) {
            setError('Ingresá un código postal.');
            return;
        }

        if (resolveBtn) resolveBtn.disabled = true;
        setFeedback('Buscando ubicación...');

        try {
            const url = new URL(resolveUrl, window.location.origin);
            url.searchParams.set('postcode', postcode);
            url.searchParams.set('country', 'AR');

            const payload = await fetchJson(url.toString());
            if (!payload || payload.ok !== true) {
                throw new Error((payload && payload.error) ? payload.error : 'No se pudo resolver la ubicación.');
            }

            const city = String(payload.city || '').trim();
            const province = String(payload.province || '').trim();
            const countryCode = String(payload.country || 'AR').trim().toUpperCase();
            const country = countryCode === 'AR' ? 'Argentina' : countryCode;
            const resolvedPostcode = String(payload.postcode || postcode).trim();

            countryInput.value = country;
            provinceInput.value = province;
            localityInput.value = city;
            postcodeInput.value = resolvedPostcode;

            const locationText = [city, province].filter(Boolean).join(', ');
            setFeedback(locationText ? `Ubicación detectada: ${locationText}` : 'Ubicación detectada correctamente.');
        } catch (error) {
            setError(error.message || 'No se pudo resolver la ubicación con ese código postal.');
        } finally {
            if (resolveBtn) resolveBtn.disabled = false;
        }
    }

    if (resolveBtn) {
        resolveBtn.addEventListener('click', resolveByPostcode);
    }

    if (postcodeInput) {
        postcodeInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                resolveByPostcode();
            }
        });
    }
});
</script>
@endpush
