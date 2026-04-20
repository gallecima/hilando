@extends('layouts.front')

@section('title', 'Mi cuenta')
@section('body_class', 'page-hero account-page')

@section('content')
  @include('front.partials.page-header', [
    'variant' => 'hero',
    'title' => 'Mi cuenta',
    'subtitle' => 'Administrá tus datos personales y fiscales.',
    'breadcrumbs' => [
      ['label' => 'Inicio', 'url' => route('home')],
      ['label' => 'Mi cuenta'],
    ],
    'slides' => $checkoutHeroSlides ?? collect(),
    'backgroundImage' => $checkoutHeroBackgroundImage ?? null,
    'heroId' => 'accountHeroCarousel',
  ])

  <section class="checkout-flow-section">
    <div class="checkout-flow-shell">
      <div class="container">
      <div class="row g-4">
        <div class="col-lg-3">
          @include('front.partials.account-nav')
        </div>

        <div class="col-lg-9">
          <div class="card shadow-sm front-form-card">
            <div class="card-body">
              @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
              @endif

              <form method="POST" action="{{ route('front.mi-cuenta.update') }}">
                @csrf

                @php
                  $customer = auth('customer')->user();
                  $billing = $customer->billingData;
                @endphp

                <h2 class="h5 mb-3">Información personal</h2>

                <div class="mb-3">
                  <label for="name" class="form-label">Nombre completo</label>
                  <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $customer->name) }}" required>
                  @error('name') <div class="small text-danger mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                  <label for="email" class="form-label">Correo electrónico</label>
                  <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $customer->email) }}" required>
                  @error('email') <div class="small text-danger mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                  <label for="phone" class="form-label">Teléfono</label>
                  <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $customer->phone) }}">
                  @error('phone') <div class="small text-danger mt-1">{{ $message }}</div> @enderror
                </div>

                <h2 class="h5 mt-4 mb-3">Datos fiscales</h2>

                <div class="mb-3">
                  <label for="billing_name" class="form-label">Razón social</label>
                  <input type="text" name="billing_name" id="billing_name" class="form-control" value="{{ old('billing_name', $billing->business_name ?? '') }}">
                  @error('billing_name') <div class="small text-danger mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                  <label for="document" class="form-label">CUIT / DNI</label>
                  <input type="text" name="document" id="document" class="form-control" value="{{ old('document', $billing->document_number ?? '') }}">
                  @error('document') <div class="small text-danger mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                  <label for="tax_status" class="form-label">Condición frente al IVA</label>
                  @php $selectedStatus = old('tax_status', $billing->tax_status ?? '') @endphp
                  <select class="form-select" name="tax_status" id="tax_status">
                    <option value="">Seleccionar</option>
                    <option value="Consumidor Final" {{ $selectedStatus == 'Consumidor Final' ? 'selected' : '' }}>Consumidor Final</option>
                    <option value="Monotributista" {{ $selectedStatus == 'Monotributista' ? 'selected' : '' }}>Monotributista</option>
                    <option value="Responsable Inscripto" {{ $selectedStatus == 'Responsable Inscripto' ? 'selected' : '' }}>Responsable Inscripto</option>
                    <option value="Exento" {{ $selectedStatus == 'Exento' ? 'selected' : '' }}>Exento</option>
                  </select>
                  @error('tax_status') <div class="small text-danger mt-1">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn btn-primary">Guardar cambios</button>
              </form>
            </div>
          </div>
        </div>
      </div>
      </div>
    </div>
  </section>
@endsection
