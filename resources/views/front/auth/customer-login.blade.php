@extends('layouts.front')

@php
  $accessMode = $accessMode ?? 'retail';
  $redirectTo = $redirectTo ?? route('front.mi-cuenta.pedidos');
  $isWholesaleAccess = $accessMode === 'wholesale';
@endphp

@section('title', $isWholesaleAccess ? 'Acceso mayorista' : 'Iniciar sesión')
@section('body_class', 'page-hero checkout-page')

@section('content')
  @include('front.checkout.partials.page-header', [
    'title' => $isWholesaleAccess ? 'Acceso mayorista' : 'Iniciar sesión',
    'subtitle' => $isWholesaleAccess
      ? 'Ingresá con una cuenta habilitada como mayorista para ver precios y mínimos de compra especiales.'
      : 'Accedé con el mismo email y DNI que usaste al comprar.',
    // 'eyebrow' => $isWholesaleAccess ? 'Mayorista' : 'Cuenta',
    'breadcrumbs' => [
      ['label' => 'Inicio', 'url' => route('home')],
      ['label' => $isWholesaleAccess ? 'Mayorista' : 'Ingresar'],
    ],
  ])

  <section class="checkout-flow-section">
    <div class="checkout-flow-shell">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-6">
            <div class="card shadow-sm front-form-card">
              <div class="card-body">
                @if($isWholesaleAccess)
                  <div class="alert alert-info">
                    Este acceso es para clientes mayoristas habilitados.
                  </div>
                @endif

                <form method="POST" action="{{ route('customer.login.submit') }}">
                  @csrf
                  <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
                  <input type="hidden" name="access_mode" value="{{ $isWholesaleAccess ? 'wholesale' : 'retail' }}">

                  <div class="mb-3">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required autofocus>
                  </div>

                  <div class="mb-3">
                    <label for="dni" class="form-label">DNI</label>
                    <input type="text" name="dni" id="dni" class="form-control" value="{{ old('dni') }}" required inputmode="numeric" placeholder="12.345.678">
                  </div>

                  <div class="form-check mb-3">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Recordarme</label>
                  </div>

                  <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Iniciar sesión</button>
                    <a href="{{ route('customer.password.request') }}" class="btn btn-outline-secondary">Recuperar contraseña</a>
                  </div>
                </form>

                @if ($errors->any())
                  <div class="alert alert-danger mt-4 mb-0">
                    <ul class="mb-0">
                      @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                      @endforeach
                    </ul>
                  </div>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
