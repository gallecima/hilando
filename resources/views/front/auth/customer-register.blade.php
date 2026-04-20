@extends('layouts.front')

@section('title', 'Crear cuenta')

@section('content')
  @include('front.partials.page-header', [
    'title' => 'Crear cuenta',
    'subtitle' => 'Completá tus datos para registrar una cuenta de cliente.',
    'breadcrumbs' => [
      ['label' => 'Inicio', 'url' => route('home')],
      ['label' => 'Crear cuenta'],
    ],
  ])

  <section class="pb-5">
    <div class="container">
      <div class="card shadow-sm front-form-card">
        <div class="card-body">
          <form method="POST" action="{{ route('customer.register.submit') }}">
            @csrf
            <div class="row g-4">
              <div class="col-lg-6">
                <h2 class="h5">Datos personales</h2>

                <div class="mb-3">
                  <label for="name" class="form-label">Nombre completo</label>
                  <input type="text" class="form-control" name="name" id="name" required value="{{ old('name') }}">
                </div>

                <div class="mb-3">
                  <label for="email" class="form-label">Correo electrónico</label>
                  <input type="email" class="form-control" name="email" id="email" required value="{{ old('email') }}">
                </div>

                <div class="mb-3">
                  <label for="password" class="form-label">Contraseña</label>
                  <input type="password" class="form-control" name="password" id="password" required>
                </div>

                <div class="mb-3">
                  <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                  <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" required>
                </div>

                <div class="mb-3">
                  <label for="phone" class="form-label">Teléfono</label>
                  <input type="text" class="form-control" name="phone" id="phone" value="{{ old('phone') }}">
                </div>

                <div class="mb-3">
                  <label for="document" class="form-label">DNI</label>
                  <input type="text" class="form-control" name="document" id="document" value="{{ old('document') }}">
                </div>
              </div>

              <div class="col-lg-6">
                <h2 class="h5">Domicilio</h2>

                <div class="mb-3">
                  <label for="address_line" class="form-label">Dirección</label>
                  <input type="text" class="form-control" name="address_line" id="address_line" value="{{ old('address_line') }}">
                </div>
                <div class="mb-3">
                  <label for="city" class="form-label">Ciudad</label>
                  <input type="text" class="form-control" name="city" id="city" value="{{ old('city') }}">
                </div>
                <div class="mb-3">
                  <label for="province" class="form-label">Provincia</label>
                  <input type="text" class="form-control" name="province" id="province" value="{{ old('province') }}">
                </div>
                <div class="mb-3">
                  <label for="postal_code" class="form-label">Código postal</label>
                  <input type="text" class="form-control" name="postal_code" id="postal_code" value="{{ old('postal_code') }}">
                </div>
                <div class="mb-3">
                  <label for="country" class="form-label">País</label>
                  <input type="text" class="form-control" name="country" id="country" value="{{ old('country', 'Argentina') }}">
                </div>

                <h2 class="h5 mt-4">Datos de facturación</h2>

                <div class="mb-3">
                  <label for="business_name" class="form-label">Razón social</label>
                  <input type="text" class="form-control" name="business_name" id="business_name" value="{{ old('business_name') }}">
                </div>

                <div class="mb-3">
                  <label for="document_number" class="form-label">CUIT</label>
                  <input type="text" class="form-control" name="document_number" id="document_number" value="{{ old('document_number') }}">
                </div>

                <div class="mb-3">
                  <label for="tax_status" class="form-label">Condición frente al IVA</label>
                  <select class="form-select" name="tax_status" id="tax_status">
                    <option value="Consumidor Final">Consumidor Final</option>
                    <option value="Monotributista">Monotributista</option>
                    <option value="Responsable Inscripto">Responsable Inscripto</option>
                    <option value="Exento">Exento</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="d-flex gap-2 mt-4">
              <button type="submit" class="btn btn-primary">Registrarse</button>
              <a href="{{ route('front.checkout.index') }}" class="btn btn-outline-secondary">Atrás</a>
            </div>

            @if ($errors->any())
              <div class="alert alert-danger mt-4 mb-0">
                <ul class="mb-0">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif
          </form>
        </div>
      </div>
    </div>
  </section>
@endsection
