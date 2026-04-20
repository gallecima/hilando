@extends('layouts.front')

@section('title', 'Cambiar contraseña')
@section('body_class', 'page-hero account-page')

@section('content')
  @include('front.partials.page-header', [
    'variant' => 'hero',
    'title' => 'Cambiar contraseña',
    'subtitle' => 'Actualizá la contraseña de acceso de tu cuenta.',
    'breadcrumbs' => [
      ['label' => 'Inicio', 'url' => route('home')],
      ['label' => 'Mi cuenta', 'url' => route('front.mi-cuenta.index')],
      ['label' => 'Cambiar contraseña'],
    ],
    'slides' => $checkoutHeroSlides ?? collect(),
    'backgroundImage' => $checkoutHeroBackgroundImage ?? null,
    'heroId' => 'accountPasswordHeroCarousel',
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

              <form method="POST" action="{{ route('front.mi-cuenta.password.update') }}">
                @csrf

                <div class="mb-3">
                  <label for="current_password" class="form-label">Contraseña actual</label>
                  <input type="password" name="current_password" id="current_password" class="form-control" required>
                  @error('current_password') <div class="small text-danger mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                  <label for="password" class="form-label">Nueva contraseña</label>
                  <input type="password" name="password" id="password" class="form-control" required>
                  @error('password') <div class="small text-danger mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                  <label for="password_confirmation" class="form-label">Confirmar nueva contraseña</label>
                  <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary">Actualizar contraseña</button>
              </form>
            </div>
          </div>
        </div>
      </div>
      </div>
    </div>
  </section>
@endsection
