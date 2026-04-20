@extends('layouts.backend')

@section('content')
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-1">Puntos de Envío</h1>
                <h2 class="fs-base lh-base fw-medium text-muted mb-0">Crear nuevo punto de cobertura o sucursal</h2>
            </div>
            <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-alt">
                    <li class="breadcrumb-item"><a class="link-fx" href="javascript:void(0)">Gestión</a></li>
                    <li class="breadcrumb-item"><a class="link-fx" href="{{ route('admin.shipping-points.index') }}">Puntos de Envío</a></li>
                    <li class="breadcrumb-item" aria-current="page">Nuevo</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Nuevo Punto de Envío</h3>
        </div>
        <div class="block-content block-content-full">
            <form action="{{ route('admin.shipping-points.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-lg-4">
                        <p class="fs-sm text-muted">
                            Definí un punto geográfico para usarlo como sucursal, referencia logística o zona de cercanía al sugerir envíos.
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
                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="provider" class="form-label">Proveedor</label>
                                <input type="text" class="form-control" id="provider" name="provider" value="{{ old('provider') }}" placeholder="Ej: Correo / Transporte / Retiro local">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="service_radius_km" class="form-label">Radio de cercanía (km)</label>
                                <input type="number" step="0.01" class="form-control" id="service_radius_km" name="service_radius_km" value="{{ old('service_radius_km', '10') }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address_line" class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="address_line" name="address_line" value="{{ old('address_line') }}">
                        </div>

                        <div class="mb-3">
                            <label for="postal_code" class="form-label">Código postal</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="postal_code" name="postal_code" value="{{ old('postal_code') }}" placeholder="Ej: 4400">
                                <button class="btn btn-outline-primary" type="button" id="btn_resolve_postcode">Autocompletar ubicación</button>
                            </div>
                            <small class="text-muted d-block mt-1" id="postcode-feedback">Podés autocompletar localidad, provincia, país y coordenadas usando código postal.</small>
                            <small class="text-danger d-none" id="postcode-error"></small>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="country_name" class="form-label">País</label>
                                <input type="text" class="form-control" id="country_name" name="country_name" value="{{ old('country_name', 'Argentina') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="province_name" class="form-label">Provincia</label>
                                <input type="text" class="form-control" id="province_name" name="province_name" value="{{ old('province_name') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="locality_name" class="form-label">Localidad</label>
                                <input type="text" class="form-control" id="locality_name" name="locality_name" value="{{ old('locality_name') }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="latitude" class="form-label">Latitud</label>
                                <input type="number" step="0.0000001" class="form-control" id="latitude" name="latitude" value="{{ old('latitude') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="longitude" class="form-label">Longitud</label>
                                <input type="number" step="0.0000001" class="form-control" id="longitude" name="longitude" value="{{ old('longitude') }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notas</label>
                            <textarea class="form-control" id="notes" name="notes" rows="4">{{ old('notes') }}</textarea>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Activo</label>
                        </div>

                        <div class="d-flex">
                            <a href="{{ route('admin.shipping-points.index') }}" class="btn btn-alt-primary me-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </div>
                </div>
            </form>
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
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const resolveBtn = document.getElementById('btn_resolve_postcode');
    const feedbackBox = document.getElementById('postcode-feedback');
    const errorBox = document.getElementById('postcode-error');

    if (!countryInput || !provinceInput || !localityInput || !postcodeInput || !latitudeInput || !longitudeInput) return;

    const resolveUrl = @json(route('api.location.resolve'));

    function setFeedback(message) {
        if (feedbackBox) feedbackBox.textContent = message || '';
    }

    function setError(message) {
        if (!errorBox) return;
        errorBox.textContent = message || '';
        errorBox.classList.toggle('d-none', !message);
    }

    async function resolveByPostcode() {
        setError('');
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

            const response = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
            const payload = await response.json().catch(() => null);
            if (!response.ok || !payload || payload.ok !== true) {
                throw new Error((payload && (payload.error || payload.message)) || 'No se pudo resolver la ubicación.');
            }

            countryInput.value = String(payload.country || 'AR').toUpperCase() === 'AR' ? 'Argentina' : String(payload.country || '');
            provinceInput.value = String(payload.province || '').trim();
            localityInput.value = String(payload.city || '').trim();
            postcodeInput.value = String(payload.postcode || postcode).trim();
            latitudeInput.value = payload.lat !== null && payload.lat !== undefined ? String(payload.lat) : '';
            longitudeInput.value = payload.lon !== null && payload.lon !== undefined ? String(payload.lon) : '';

            setFeedback('Ubicación detectada correctamente.');
        } catch (error) {
            setError(error.message || 'No se pudo resolver la ubicación.');
            setFeedback('Podés cargar los datos manualmente.');
        } finally {
            if (resolveBtn) resolveBtn.disabled = false;
        }
    }

    if (resolveBtn) {
        resolveBtn.addEventListener('click', resolveByPostcode);
    }
});
</script>
@endpush
