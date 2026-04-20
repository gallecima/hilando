
    <p class="text-muted small">
        Una vez que elimines tu cuenta, todos tus datos se borrarán permanentemente. Asegurate de descargar lo que necesites conservar.
    </p>

    <!-- Botón que abre el modal -->
    <button type="button" class="btn btn-danger mt-3" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
        Eliminar cuenta
    </button>

    <!-- Modal -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('profile.destroy') }}" class="modal-content">
                @csrf
                @method('DELETE')

                <div class="modal-header">
                    <h5 class="modal-title" id="deleteAccountModalLabel">¿Estás seguro?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <p class="mb-3">
                        Esta acción no se puede deshacer. Ingresá tu contraseña para confirmar que querés eliminar tu cuenta permanentemente.
                    </p>

                    <div class="mb-3">
                        <label for="delete_password" class="form-label visually-hidden">Contraseña</label>
                        <input type="password"
                               name="password"
                               id="delete_password"
                               placeholder="Contraseña"
                               class="form-control @error('password', 'userDeletion') is-invalid @enderror">
                        @error('password', 'userDeletion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-alt-primary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-danger">
                        Confirmar eliminación
                    </button>
                </div>
            </form>
        </div>
    </div>
