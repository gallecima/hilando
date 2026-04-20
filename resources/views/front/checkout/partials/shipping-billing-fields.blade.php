@php
  $isCustomer    = isset($customer) && $customer;
  $guestShipping = (object) (session('guest_checkout.shipping') ?? []);
  $guestBilling  = (object) (session('guest_checkout.billing') ?? []);

  // Personales
  $name  = old('name',  $isCustomer ? ($customer->name  ?? '') : ($guestShipping->name  ?? ''));
  $email = old('email', $isCustomer ? ($customer->email ?? '') : ($guestShipping->email ?? ''));
  $phone = old('phone', $isCustomer ? ($customer->phone ?? '') : ($guestShipping->phone ?? ''));
  $document_number = old('document_number', $isCustomer ? ($customer->document ?? '') : ($guestShipping->document_number ?? ''));

  // Envío
  $address     = old('address',     $isCustomer ? (optional($customer->address)->address_line ?? '') : ($guestShipping->address ?? ''));
  $city        = old('city',        $isCustomer ? (optional($customer->address)->city ?? '')         : ($guestShipping->city ?? ''));
  $province    = old('province',    $isCustomer ? (optional($customer->address)->province ?? '')     : ($guestShipping->province ?? ''));
  $postal_code = old('postal_code', $isCustomer ? (optional($customer->address)->postal_code ?? '')  : ($guestShipping->postal_code ?? ''));
  $country     = old('country',     $isCustomer ? (optional($customer->address)->country ?? 'Argentina') : ($guestShipping->country ?? 'Argentina'));

  $localityIdFromCustomer = $isCustomer ? (optional($customer->address)->locality_id ?? null) : null;

  // Facturación
  $billing_name = old('billing_name',
      $isCustomer
          ? (optional($customer->billingData)->business_name ?? ($customer->name ?? ''))
          : (($guestBilling->business_name ?? '') ?: ($name ?? ''))
  );
  $document     = old('document',
      $isCustomer
          ? (optional($customer->billingData)->document_number ?? ($customer->document ?? ''))
          : (($guestBilling->document_number ?? '') ?: ($document_number ?? ''))
  );
  $tax_status   = old('tax_status',
      $isCustomer
          ? (optional($customer->billingData)->tax_status ?? 'Consumidor Final')
          : (($guestBilling->tax_status ?? '') ?: 'Consumidor Final')
  );

  $billing_open = (bool) old('modify_billing', false)
    || $errors->has('billing_name')
    || $errors->has('document')
    || $errors->has('tax_status');
@endphp

<h5 class="mb-3">Información personal</h5>

<div class="mb-3">
  <label for="name" class="form-label">Nombre completo</label>
  <input type="text" name="name" id="name" class="form-control" value="{{ $name }}" required>
  @error('name') <small class="text-danger">{{ $message }}</small> @enderror
</div>

<div class="mb-3">
  <label for="email" class="form-label">Correo electrónico</label>
  <input type="email" name="email" id="email" class="form-control" value="{{ $email }}" required>
  @error('email') <small class="text-danger">{{ $message }}</small> @enderror
</div>

<div class="mb-3">
  <label for="phone" class="form-label">Teléfono</label>
  <input type="text" name="phone" id="phone" class="form-control" value="{{ $phone }}">
  @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
</div>

<div class="mb-3">
  <label for="document_number" class="form-label">DNI</label>
  <input type="text" name="document_number" id="document_number" class="form-control js-dni" value="{{ $document_number }}"
         inputmode="numeric" placeholder="12.345.678" maxlength="10" pattern="\d{2}\.\d{3}\.\d{3}">
  @error('document_number') <small class="text-danger">{{ $message }}</small> @enderror
</div>

<h5 class="mt-4 mb-3">Dirección de envío</h5>

<div class="mb-3">
  <label for="address" class="form-label">Dirección</label>
  <input type="text" name="address" id="address" class="form-control" value="{{ $address }}" required>
  @error('address') <small class="text-danger">{{ $message }}</small> @enderror
</div>

@php
  $provinces = \App\Models\Province::orderBy('name')->get();

  $selectedLocalityId = old('locality_id', $localityIdFromCustomer ?? ($guestShipping->locality_id ?? null));
  $selectedLocalityId = $selectedLocalityId !== '' ? $selectedLocalityId : null;

  $selectedProvinceId = old('province_id');
  $selectedProvinceId = $selectedProvinceId !== '' ? $selectedProvinceId : null;

  $selectedLocalityModel = null;
  if ($selectedLocalityId) {
      $selectedLocalityModel = \App\Models\Locality::with('province')->find($selectedLocalityId);
  }
  if (!$selectedProvinceId && $selectedLocalityModel) {
      $selectedProvinceId = $selectedLocalityModel->province_id;
  }

  if (($city ?? '') === '' && $selectedLocalityModel) {
      $city = (string) ($selectedLocalityModel->name ?? '');
  }
  if (($province ?? '') === '' && $selectedLocalityModel && $selectedLocalityModel->province) {
      $province = (string) ($selectedLocalityModel->province->name ?? '');
  }
@endphp

{{-- Inputs ocultos para mantener compatibilidad con el backend actual --}}
<input type="hidden" name="province" id="province" value="{{ $province }}">
<input type="hidden" name="city" id="city" value="{{ $city }}">

<div class="row">
  <div class="col-md-4 mb-3">
    <label for="province_id" class="form-label">Provincia</label>
    <select name="province_id" id="province_id" class="form-select" required data-selected="{{ $selectedProvinceId ?? '' }}">
      <option value="">Seleccionar provincia</option>
      @foreach($provinces as $prov)
        <option value="{{ $prov->id }}"
          {{ (int)$selectedProvinceId === $prov->id ? 'selected' : '' }}>
          {{ $prov->name }}
        </option>
      @endforeach
    </select>
    @error('province_id') <small class="text-danger">{{ $message }}</small> @enderror
  </div>

  <div class="col-md-4 mb-3">
    <label for="locality_id" class="form-label">Ciudad / Localidad</label>
    <select name="locality_id" id="locality_id" class="form-select" required data-selected="{{ $selectedLocalityId ?? '' }}">
      <option value="">Seleccioná una provincia primero</option>
    </select>
    @error('locality_id') <small class="text-danger">{{ $message }}</small> @enderror
  </div>

  <div class="col-md-4 mb-3">
    <label for="postal_code" class="form-label">Código postal</label>
    <input type="text" name="postal_code" id="postal_code" class="form-control" value="{{ $postal_code }}" required>
    @error('postal_code') <small class="text-danger">{{ $message }}</small> @enderror
  </div>
</div>

<input type="hidden" name="country" value="{{ $country }}">

<div class="form-check mt-4 mb-2">
  <input class="form-check-input" type="checkbox" value="1" id="modify_billing" name="modify_billing"
    {{ $billing_open ? 'checked' : '' }}>
  <label class="form-check-label" for="modify_billing">
    Modificar datos de facturación
  </label>
</div>

<div id="billing-section" class="{{ $billing_open ? '' : 'd-none' }}">
  <h5 class="mt-3 mb-3">Datos fiscales</h5>

  <div class="mb-3">
    <label for="billing_name" class="form-label">Razón social / Nombre</label>
    <input type="text" name="billing_name" id="billing_name" class="form-control" value="{{ $billing_name }}" {{ $billing_open ? '' : 'disabled' }}>
    @error('billing_name') <small class="text-danger">{{ $message }}</small> @enderror
  </div>

  <div class="mb-3">
    <label for="document" class="form-label">CUIT / DNI</label>
    <input type="text" name="document" id="document" class="form-control js-fiscal-document" value="{{ $document }}"
           inputmode="numeric" placeholder="12.345.678 o 20-12345678-3" maxlength="13"
           pattern="(\d{2}\.\d{3}\.\d{3}|\d{2}-\d{8}-\d)" {{ $billing_open ? '' : 'disabled' }}>
    @error('document') <small class="text-danger">{{ $message }}</small> @enderror
  </div>

  <div class="mb-3">
    <label for="tax_status" class="form-label">Condición frente al IVA</label>
    <select class="form-select" name="tax_status" id="tax_status" {{ $billing_open ? '' : 'disabled' }}>
      <option value="">Seleccionar</option>
      <option value="Consumidor Final"      {{ $tax_status === 'Consumidor Final' ? 'selected' : '' }}>Consumidor Final</option>
      <option value="Monotributista"        {{ $tax_status === 'Monotributista' ? 'selected' : '' }}>Monotributista</option>
      <option value="Responsable Inscripto" {{ $tax_status === 'Responsable Inscripto' ? 'selected' : '' }}>Responsable Inscripto</option>
      <option value="Exento"                {{ $tax_status === 'Exento' ? 'selected' : '' }}>Exento</option>
    </select>
    @error('tax_status') <small class="text-danger">{{ $message }}</small> @enderror
  </div>
</div>
