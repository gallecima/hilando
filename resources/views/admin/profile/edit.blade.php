@extends('layouts.backend')

@section('content')



        <!-- Hero -->
        <div class="bg-image" style="background-image: url('assets/media/photos/photo10@2x.jpg');">
          <div class="bg-primary-dark-op">
            <div class="content content-full text-center">
              <div class="my-3">
                <img class="img-avatar img-avatar-thumb" src="{{ asset('storage/' . $user->profile_photo) }}" alt="">
              </div>
              <h1 class="h2 text-white mb-0">Editar cuenta</h1>
              <h2 class="h4 fw-normal text-white-75">
                {{ Auth::user()->name }}<br>                
              </h2>

            </div>
          </div>
        </div>
        <!-- END Hero -->

        <!-- Page Content -->
        <div class="content content-boxed">
          <!-- User Profile -->
          <div class="block block-rounded">
            <div class="block-header block-header-default">
              <h3 class="block-title">Perfil de usuario</h3>
            </div>
            <div class="block-content">
              <div class="row push">
                  <div class="col-lg-4">
                    <p class="fs-sm text-muted">
                      La información vital de tu cuenta. Tu nombre de usuario será visible.
                    </p>
                  </div>
                  <div class="col-lg-8 col-xl-5">
                    @include('admin.profile.partials.update-profile-information-form')
                  </div>
              </div>
            </div>
          </div>
          <!-- END User Profile -->

          <!-- Change Password -->
          <div class="block block-rounded">
            <div class="block-header block-header-default">
              <h3 class="block-title">Actualizar contraseña</h3>
            </div>
            <div class="block-content">
              <form action="be_pages_projects_edit.html" method="POST" onsubmit="return false;">
                <div class="row push">
                  <div class="col-lg-4">
                    <p class="fs-sm text-muted">
                      Actualiza tu contraseña perdiodicamente para mantener tu cuenta segura.
                    </p>
                  </div>
                  <div class="col-lg-8 col-xl-5">
                    @include('admin.profile.partials.update-password-form')
                  </div>
                </div>
              </form>
            </div>
          </div>
          <!-- END Change Password -->

          <!-- Connections -->
          <div class="block block-rounded">
            <div class="block-header block-header-default">
              <h3 class="block-title">Eliminar cuenta</h3>
            </div>
            <div class="block-content">
              <div class="row push">
                <div class="col-lg-4">
                </div>
                <div class="col-lg-8 col-xl-7">
                  @include('admin.profile.partials.delete-user-form')
                </div>
              </div>
            </div>
          </div>
          <!-- END Connections -->
        </div>
        <!-- END Page Content -->

@endsection