@extends('layouts.backend')

@section('content')
<div class="content">
  <div class="row">
    <div class="col-xl-8">
      <div class="block block-rounded">
        <div class="block-header block-header-default">
          <h3 class="block-title">
            Configuración de plugin
            <small class="text-muted">({{ $plugin->name }} – v{{ $plugin->version }})</small>
          </h3>
          <div class="block-options">
            <span class="badge {{ $plugin->is_active ? 'bg-success' : 'bg-warning' }}">
              {{ $plugin->is_active ? 'Activo' : 'Inactivo' }}
            </span>
          </div>
        </div>
        <div class="block-content block-content-full overflow-x-auto">
          @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif
          @if ($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <form method="POST" action="{{ route('admin.plugins.settings.update', $plugin->slug) }}">
            @csrf

            @php
              // Config actual (array asociativo)
              $cfg = is_array($plugin->config) ? $plugin->config : [];
            @endphp

            <div class="mb-3">
              <label class="form-label">Slug</label>
              <input type="text" class="form-control" value="{{ $plugin->slug }}" disabled>
              <small class="text-muted">Identificador técnico del plugin (no editable).</small>
            </div>

            <div class="table-responsive">
              <table class="table table-bordered align-middle" id="config-table">
                <thead>
                  <tr>
                    <th style="width: 30%">Clave</th>
                    <th>Valor</th>
                    <th style="width: 60px;"></th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($cfg as $k => $v)
                    <tr>
                      <td>
                        <input type="text" class="form-control" name="config_keys[]" value="{{ $k }}" readonly>
                        <small class="text-muted">existente</small>
                      </td>
                      <td>
                        <input type="text" class="form-control"
                               name="config[{{ $k }}]"
                               value="{{ is_scalar($v) ? $v : json_encode($v) }}">
                        <small class="text-muted">Si el valor es complejo, colocalo en JSON.</small>
                      </td>
                      <td class="text-center">
                        <button type="button" class="btn btn-sm btn-alt-danger js-remove-row" title="Quitar">
                          <i class="fa fa-times"></i>
                        </button>
                      </td>
                    </tr>
                  @empty
                    {{-- Sin config previa, mostramos una fila en blanco para empezar --}}
                    <tr>
                      <td>
                        <input type="text" class="form-control" name="config_keys_new[]" placeholder="clave (ej: message)">
                      </td>
                      <td>
                        <input type="text" class="form-control" name="config_new_values[]" placeholder="valor (texto o JSON)">
                      </td>
                      <td class="text-center">
                        <button type="button" class="btn btn-sm btn-alt-danger js-remove-row" title="Quitar">
                          <i class="fa fa-times"></i>
                        </button>
                      </td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            <div class="d-flex gap-2 mb-3">
              <button type="button" class="btn btn-alt-primary" id="btn-add-row">
                <i class="fa fa-plus me-1"></i> Agregar campo
              </button>

              <button class="btn btn-primary">Guardar</button>
              <a href="{{ route('admin.plugins.index') }}" class="btn btn-alt-secondary">Volver</a>
            </div>

            {{-- Ayuda --}}
            <hr>
            <p class="text-muted mb-0">
              <strong>Tip:</strong> Los valores complejos (arrays/objetos) podés guardarlos como JSON.
              Ej: <code>{"contexts":["home","checkout"]}</code>
            </p>
          </form>
        </div>
      </div>
    </div>

    <div class="col-xl-4">
      <div class="block block-rounded">
        <div class="block-header block-header-default">
          <h3 class="block-title">Estado</h3>
        </div>
        <div class="block-content block-content-full overflow-x-auto">
          <ul class="list-group push">
            <li class="list-group-item d-flex justify-content-between align-items-center">
              Instalado
              <span class="badge {{ $plugin->is_installed ? 'bg-success' : 'bg-danger' }}">
                {{ $plugin->is_installed ? 'Sí' : 'No' }}
              </span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              Activo
              <span class="badge {{ $plugin->is_active ? 'bg-success' : 'bg-warning' }}">
                {{ $plugin->is_active ? 'Sí' : 'No' }}
              </span>
            </li>
            @if($plugin->installed_at)
              <li class="list-group-item">
                <small class="text-muted">Instalado: {{ $plugin->installed_at }}</small>
              </li>
            @endif
          </ul>
          <a href="{{ route('admin.plugins.index') }}" class="btn btn-sm btn-alt-secondary w-100">Volver al listado</a>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const tableBody = document.querySelector('#config-table tbody');
  const btnAdd = document.getElementById('btn-add-row');

  const rowTemplate = () => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>
        <input type="text" class="form-control" name="config_keys_new[]" placeholder="clave (ej: message)">
      </td>
      <td>
        <input type="text" class="form-control" name="config_new_values[]" placeholder="valor (texto o JSON)">
      </td>
      <td class="text-center">
        <button type="button" class="btn btn-sm btn-alt-danger js-remove-row" title="Quitar">
          <i class="fa fa-times"></i>
        </button>
      </td>
    `;
    return tr;
  };

  btnAdd?.addEventListener('click', () => {
    tableBody.appendChild(rowTemplate());
  });

  tableBody?.addEventListener('click', (e) => {
    if (e.target.closest('.js-remove-row')) {
      const tr = e.target.closest('tr');
      tr?.remove();
    }
  });
});
</script>
@endsection