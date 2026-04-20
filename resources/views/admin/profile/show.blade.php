@extends('layouts.backend')

@section('content')

        <!-- Hero -->
        <div class="bg-image" style="background-image: url('assets/media/photos/photo12@2x.jpg');">
          <div class="bg-black-50">
            <div class="content content-full text-center">
              <div class="my-3">
                <img class="img-avatar img-avatar-thumb" src="{{ asset('storage/' . $user->profile_photo) }}" alt="">
              </div>
              <h1 class="h2 text-white mb-0">{{ Auth::user()->name }}</h1>
              <span class="text-white-75">{{ Auth::user()->email }}</span>
              <a class="btn btn-alt-secondary" href="/profile/edit">
                <i class="fa fa-fw fa-arrow-left text-danger"></i> Editar mi perfil
              </a>              
            </div>
          </div>
        </div>
        <!-- END Hero -->

        <!-- Stats -->
        <div class="bg-body-extra-light">
          <div class="content content-boxed">
            <div class="row items-push text-center">
            <div class="col-6 col-md-3">
                <div class="fs-sm fw-semibold text-muted text-uppercase">Empresas</div>
                <a class="link-fx fs-3" href="javascript:void(0)">3</a>
            </div>
              <div class="col-6 col-md-3">
                <div class="fs-sm fw-semibold text-muted text-uppercase">Clientes</div>
                <a class="link-fx fs-3" href="javascript:void(0)">17980</a>
              </div>
              <div class="col-6 col-md-3">
                <div class="fs-sm fw-semibold text-muted text-uppercase">Facturas</div>
                <a class="link-fx fs-3" href="javascript:void(0)">27</a>
              </div>
              <div class="col-6 col-md-3">
                <div class="fs-sm fw-semibold text-muted text-uppercase mb-2">739 Ratings</div>
                <span class="text-warning">
                  <i class="fa fa-star"></i>
                  <i class="fa fa-star"></i>
                  <i class="fa fa-star"></i>
                  <i class="fa fa-star"></i>
                  <i class="fa fa-star-half"></i>
                </span>
                <span class="fs-sm text-muted">(4.9)</span>
              </div>
            </div>
          </div>
        </div>
        <!-- END Stats -->        


        <!-- Page Content -->
        <div class="content">

      
        </div>
        <!-- END Page Content -->


@endsection