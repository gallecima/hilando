@extends('layouts.front')

@section('title', 'Recuperar contraseña')

@section('content')
  @include('front.partials.page-header', [
    'title' => 'Recuperar contraseña',
    'subtitle' => 'Ingresá tu email y te enviaremos un enlace para restablecerla.',
    'breadcrumbs' => [
      ['label' => 'Inicio', 'url' => route('home')],
      ['label' => 'Recuperar contraseña'],
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

              <form method="POST" action="{{ route('customer.password.email') }}">
                @csrf
                <div class="mb-3">
                  <label for="email" class="form-label">Correo electrónico</label>
                  <input type="email" name="email" id="email" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Enviar enlace</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
