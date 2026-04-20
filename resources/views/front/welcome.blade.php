@extends('layouts.simple')

@section('title', 'Bienvenido')

@section('content')
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-body text-center py-5">
          <img src="{{ asset('media/logos/logo-b.svg') }}" alt="Logo" class="img-fluid mb-4">
          <div class="d-flex justify-content-center gap-2">
            <a class="btn btn-primary" href="/login">Login</a>
            <a class="btn btn-outline-secondary" href="/register">Register</a>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
