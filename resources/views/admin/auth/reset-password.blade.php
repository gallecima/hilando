@extends('layouts.guest')

@section('content')
<!-- Main Container -->
<main id="main-container">
    <!-- Page Content -->
    <div class="bg-image">
        <div class="row g-0 bg-primary-dark-op">
            <!-- Meta Info Section -->
            <div class="hero-static col-lg-4 d-none d-lg-flex flex-column justify-content-center" style="background-image: url('{{ asset('media/photos/fondo.jpg') }}'); background-position:50% 50%">
                <div class="p-4 p-xl-5 flex-grow-1 d-flex align-items-center">
                    <div class="w-100">
                        <img src="{{ asset('media/logos/logo-w.svg') }}" style="max-width: 50%;">
                        <p class="text-white-75 me-xl-8 mt-2">
                            Establecé una nueva contraseña para acceder de nuevo a tu cuenta.
                        </p>
                    </div>
                </div>
                <div class="p-4 p-xl-5 d-xl-flex justify-content-between align-items-center fs-sm">
                    <p class="fw-medium text-white-50 mb-0">
                        <strong>Pixelio Cart 1.01</strong> &copy; <span data-toggle="year-copy"></span>
                    </p>
                    <ul class="list list-inline mb-0 py-2">
                        <li class="list-inline-item">
                            <a class="text-white-75 fw-medium" href="legales">Legales</a>
                        </li>
                        <li class="list-inline-item">
                            <a class="text-white-75 fw-medium" href="contacto">Contacto</a>
                        </li>
                        <li class="list-inline-item">
                            <a class="text-white-75 fw-medium" href="terminos_condiciones">Términos y condiciones</a>
                        </li>
                    </ul>
                </div>
            </div>
            <!-- END Meta Info Section -->

            <!-- Main Section -->
            <div class="hero-static col-lg-8 d-flex flex-column align-items-center bg-body-extra-light">
                <div class="p-3 w-100 d-lg-none text-center">
                    <a class="link-fx fw-semibold fs-3 text-dark" href="/">
                        Simmplia
                    </a>
                </div>
                <div class="p-4 w-100 flex-grow-1 d-flex align-items-center">
                    <div class="w-100">
                        <!-- Header -->
                        <div class="text-center mb-5">
                            <p class="mb-3">
                                <i class="fa fa-2x fa-key text-primary-light"></i>
                            </p>
                            <h1 class="fw-bold mb-2">Restablecer Contraseña</h1>
                            <p class="fw-medium text-muted">
                                Ingresá tu nueva contraseña para completar el proceso.
                            </p>
                        </div>
                        <!-- END Header -->

                        <!-- Reset Password Form -->
                        <div class="row g-0 justify-content-center">
                            <div class="col-sm-8 col-xl-6">
                                <form method="POST" action="{{ route('password.store') }}">
                                    @csrf

                                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                                    <div class="mb-4">
                                        <input id="email" class="form-control form-control-lg form-control-alt py-3 @error('email') is-invalid @enderror"
                                               type="email"
                                               name="email"
                                               value="{{ old('email', $request->email) }}"
                                               required autofocus
                                               placeholder="Ingresá tu email">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <input id="password" class="form-control form-control-lg form-control-alt py-3 @error('password') is-invalid @enderror"
                                               type="password"
                                               name="password"
                                               required
                                               placeholder="Nueva Contraseña">
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <input id="password_confirmation" class="form-control form-control-lg form-control-alt py-3"
                                               type="password"
                                               name="password_confirmation"
                                               required
                                               placeholder="Confirmá la Nueva Contraseña">
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <div>
                                            <a class="text-muted fs-sm fw-medium d-block d-lg-inline-block mb-1" href="{{ route('login') }}">
                                                Volver al Login
                                            </a>
                                        </div>
                                        <div>
                                            <button type="submit" class="btn btn-lg btn-alt-primary">
                                                <i class="fa fa-fw fa-check me-1 opacity-50"></i> Restablecer Contraseña
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!-- END Reset Password Form -->
                    </div>
                </div>
                <div class="px-4 py-3 w-100 d-lg-none d-flex flex-column flex-sm-row justify-content-between fs-sm text-center text-sm-start">
                    <p class="fw-medium text-black-50 py-2 mb-0">
                        <strong>Simmplia</strong> &copy; <span data-toggle="year-copy"></span>
                    </p>
                </div>
            </div>
            <!-- END Main Section -->
        </div>
    </div>
    <!-- END Page Content -->
</main>
<!-- END Main Container -->
@endsection