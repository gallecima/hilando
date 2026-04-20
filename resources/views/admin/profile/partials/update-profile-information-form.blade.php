<!-- Reenvío de verificación -->
<form id="send-verification" method="POST" action="{{ route('verification.send') }}">
    @csrf
</form>

<form method="POST" action="{{ route('profile.update') }}" class="row g-3" enctype="multipart/form-data">
    @csrf
    @method('PATCH')

    {{-- Nombre --}}
    <div class="col-md-12">
        <label for="name" class="form-label">Nombre</label>
        <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Email --}}
    <div class="col-md-12">
        <label for="email" class="form-label">Correo electrónico</label>
        <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $user->email) }}" required autocomplete="username">
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="mt-2">
                <p class="small text-muted">
                    Tu correo no está verificado.
                    <button form="send-verification" class="btn btn-sm btn-link p-0 align-baseline">
                        Reenviar enlace de verificación
                    </button>
                </p>

                @if (session('status') === 'verification-link-sent')
                    <div class="alert alert-success py-1 px-2 small mt-1 mb-0">
                        Se envió un nuevo enlace de verificación.
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- Subir nueva imagen --}}
    <div class="col-md-12">
        <label for="profile_photo" class="form-label">Foto de perfil</label>
        <input type="file" class="form-control @error('profile_photo') is-invalid @enderror" id="profile_photo" name="profile_photo">
        @error('profile_photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>    

    {{-- Botón --}}
    <div class="col-12 d-flex justify-content-between align-items-center mt-3">
        <button type="submit" class="btn btn-primary">
            Guardar cambios
        </button>

        @if (session('status') === 'profile-updated')
            <span class="text-success small">Guardado correctamente.</span>
        @endif
    </div>
</form>