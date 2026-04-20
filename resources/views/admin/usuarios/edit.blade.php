@extends('layouts.backend')

@section('content')
<!-- Hero -->
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-1">Usuarios</h1>
                <h2 class="fs-base lh-base fw-medium text-muted mb-0">Gestión de usuarios del sistema</h2>
            </div>
            <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-alt">
                    <li class="breadcrumb-item"><a class="link-fx" href="#">Configuración</a></li>
                    <li class="breadcrumb-item"><a class="link-fx" href="{{ route('admin.usuarios.index') }}">Usuarios</a></li>
                    <li class="breadcrumb-item" aria-current="page">Editar</li>
                </ol>
            </nav>
        </div>
    </div>
</div>
<!-- END Hero -->

<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Editar Usuario</h3>
        </div>
        <div class="block-content block-content-full">

              <div class="row">
                <div class="col-lg-4">
                  <p class="fs-sm text-muted">
                    Editá o actualizá los datos del usuario.
                  </p>
                    @if ($usuario->profile_photo)
                        <div class="mt-2">
                            <img src="{{ asset('storage/' . $usuario->profile_photo) }}" alt="Foto de perfil" class="img-thumbnail" style="width:200px;height: 200px;">
                        </div>
                    @endif
                </div>
                <div class="col-lg-8 space-y-5">        
                    <form action="{{ route('admin.usuarios.update', $usuario) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $usuario->name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $usuario->email) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="profile_photo" class="form-label">Foto de Perfil</label>
                            <input type="file" class="form-control" id="profile_photo" name="profile_photo" accept="image/*">
                        </div>

                        <div class="mb-3">
                            <label for="perfil_id" class="form-label">Perfil</label>
                            <select name="perfil_id" id="perfil_id" class="form-select">
                                @foreach($perfiles as $perfil)
                                    <option value="{{ $perfil->id }}" {{ $usuario->perfil_id == $perfil->id ? 'selected' : '' }}>{{ $perfil->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="active" value="0">
                            <input class="form-check-input" type="checkbox" id="activo" name="active" value="1" {{ old('active', $usuario->active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="activo">Activo</label>
                        </div>             

                        <div class="d-flex">
                            <a href="{{ route('admin.usuarios.index') }}" class="btn btn-alt-primary me-2">Cancelar</a>
                        
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
              </div>

        </div>
    </div>
</div>
@endsection
