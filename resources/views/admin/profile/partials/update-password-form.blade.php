    <form id="send-verification" method="POST" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        @method('PUT')

        {{-- Contraseña actual --}}
        <div class="mb-4">
            <label for="current_password" class="form-label">Contraseña actual</label>
            <input id="current_password" name="current_password" type="password"
                   class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                   autocomplete="current-password">
            @error('current_password', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Nueva contraseña --}}
        <div class="mb-4">
            <label for="password" class="form-label">Nueva contraseña</label>
            <input id="password" name="password" type="password"
                   class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                   autocomplete="new-password">
            @error('password', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Confirmación --}}
        <div class="mb-4">
            <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
            <input id="password_confirmation" name="password_confirmation" type="password"
                   class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror"
                   autocomplete="new-password">
            @error('password_confirmation', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Botón + Estado --}}
        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-primary">
                Guardar cambios
            </button>

            @if (session('status') === 'password-updated')
                <span class="text-success small">Contraseña actualizada correctamente.</span>
            @endif
        </div>
    </form>