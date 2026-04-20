@extends('layouts.simple')

@section('title', 'Landing')

@section('content')
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-body text-center py-5">
          <h1 class="display-6 fw-bold mb-3">Laravel {{ explode('.', App::VERSION())[0] }}</h1>
          <p class="lead text-body-secondary mb-4">Pantalla inicial simplificada con Bootstrap puro.</p>
          <a class="btn btn-primary" href="/dashboard">Entrar al dashboard</a>
        </div>
      </div>
    </div>
  </div>
@endsection
