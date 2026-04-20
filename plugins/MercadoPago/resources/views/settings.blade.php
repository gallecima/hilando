@extends('layouts.backend')

@section('content')
<div class="content">
  <div class="row">
    <div class="col-xl-6">
      <div class="block block-rounded">
        <div class="block-header block-header-default">
          <h3 class="block-title">MercadoPago – Configuración</h3>
        </div>
        <div class="block-content block-content-full overflow-x-auto">
          @php $cfg = $plugin->config ?? []; @endphp
          <form method="POST" action="{{ route('admin.plugins.mercadopago.update') }}">
            @csrf

            <div class="mb-3">
              <label class="form-label">Modo</label>
              <select name="mode" class="form-select">
                <option value="test" {{ ($cfg['mode'] ?? 'test')==='test' ? 'selected' : '' }}>Test</option>
                <option value="live" {{ ($cfg['mode'] ?? 'test')==='live' ? 'selected' : '' }}>Live</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Public Key</label>
              <input name="public_key" class="form-control" value="{{ old('public_key', $cfg['public_key'] ?? '') }}">
            </div>

            <div class="mb-3">
              <label class="form-label">Access Token</label>
              <input name="access_token" class="form-control" value="{{ old('access_token', $cfg['access_token'] ?? '') }}">
            </div>

            <div class="mb-3">
              <label class="form-label">Integrator ID (opcional)</label>
              <input name="integrator_id" class="form-control" value="{{ old('integrator_id', $cfg['integrator_id'] ?? '') }}">
            </div>

            <div class="mb-3">
              <label class="form-label">Webhook Secret (opcional)</label>
              <input name="webhook_secret" class="form-control" value="{{ old('webhook_secret', $cfg['webhook_secret'] ?? '') }}">
              <small class="text-muted">Si lo usás, validá en el controller del webhook.</small>
            </div>

              <div class="mb-3">
                <label class="form-label">Success URL</label>
                <input name="success_url" class="form-control" value="{{ old('success_url', $cfg['success_url'] ?? '/checkout/complete?status=success') }}">
              </div>
              <div class="mb-3">
                <label class="form-label">Failure URL</label>
                <input name="failure_url" class="form-control" value="{{ old('failure_url', $cfg['failure_url'] ?? '/checkout/complete?status=failure') }}">
              </div>
              <div class="mb-3">
                <label class="form-label">Pending URL</label>
                <input name="pending_url" class="form-control" value="{{ old('pending_url', $cfg['pending_url'] ?? '/checkout/complete?status=pending') }}">
              </div>
              <div class="mb-3">
                <label class="form-label">Webhook URL (opcional)</label>
                <input name="webhook_url" class="form-control"
                      placeholder="https://tu-dominio.com/webhooks/mercadopago"
                      value="{{ old('webhook_url', $cfg['webhook_url'] ?? '') }}">
                <small class="text-muted">
                  Si lo dejás vacío, usaremos la URL interna: <code>{{ route('mp.webhook') }}</code>
                </small>
              </div>              

            <button class="btn btn-primary">Guardar</button>
            <a href="{{ route('admin.plugins.index') }}" class="btn btn-alt-secondary">Volver</a>
          </form>



        </div>
      </div>
    </div>
    <div class="col-xl-6">
      <div class="block block-rounded">
        <div class="block-header block-header-default">
          <h3 class="block-title">Probar Conexión</h3>
        </div>
        <div class="block-content block-content-full overflow-x-auto">

          <form method="POST" action="{{ route('admin.plugins.mercadopago.test') }}">
            @csrf
            <button class="btn btn-alt-primary w-100">
              <i class="fa fa-bolt me-1"></i> Probar conexión (crear preferencia)
            </button>
          </form>

          @if(session('error'))
            <div class="alert alert-danger mt-3">{{ session('error') }}</div>
          @endif
          @if(session('success'))
            <div class="alert alert-success mt-3">{{ session('success') }}</div>
          @endif

        </div>
      </div>
    </div>    
  </div>
</div>
@endsection