@extends('layouts.front')

@section('title', 'Restablecer contraseña')

@section('content')
  @include('front.partials.page-header', [
    'title' => 'Restablecer contraseña',
    'subtitle' => 'Ingresá tu nueva contraseña para la cuenta.',
    'breadcrumbs' => [
      ['label' => 'Inicio', 'url' => route('home')],
      ['label' => 'Restablecer contraseña'],
    ],
  ])

  <section class="pb-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-6">
          <div class="card shadow-sm front-form-card">
            <div class="card-body">
              @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
              @endif

              @if ($errors->any())
                <div class="alert alert-danger">
                  <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                      <li>{{ $error }}</li>
                    @endforeach
                  </ul>
                </div>
              @endif

              <form method="POST" action="{{ route('customer.password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ old('email', $email) }}">

                <div class="mb-3">
                  <label for="password" class="form-label">Nueva contraseña</label>
                  <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                  @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                  <label for="password_confirmation" class="form-label">Confirmar nueva contraseña</label>
                  <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required autocomplete="new-password">
                </div>

                <button type="submit" class="btn btn-primary">Guardar nueva contraseña</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
