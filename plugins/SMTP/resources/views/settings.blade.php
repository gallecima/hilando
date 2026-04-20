@extends('layouts.backend')

@section('content')
<div class="content">
  <div class="row">
    <div class="col-xl-8">
      <div class="block block-rounded">
        <div class="block-header block-header-default">
          <h3 class="block-title">SMTP – Configuración</h3>
        </div>
        <div class="block-content block-content-full overflow-x-auto">

          @php $cfg = $plugin->config ?? []; @endphp

          <form method="POST" action="{{ route('admin.plugins.smtp.update') }}">
            @csrf

            <div class="mb-3">
              <label class="form-label">Host</label>
              <input name="host" class="form-control" value="{{ old('host', $cfg['host'] ?? '') }}">
            </div>

            <div class="mb-3">
              <label class="form-label">Port</label>
              <input name="port" type="number" class="form-control" value="{{ old('port', $cfg['port'] ?? 587) }}">
            </div>

            <div class="mb-3">
              <label class="form-label">Encryption</label>
              <select name="encryption" class="form-select">
                <option value=""    {{ empty($cfg['encryption']) ? 'selected' : '' }}>Sin cifrado</option>
                <option value="tls" {{ ($cfg['encryption'] ?? '') === 'tls' ? 'selected' : '' }}>TLS</option>
                <option value="ssl" {{ ($cfg['encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Username</label>
              <input name="username" class="form-control" value="{{ old('username', $cfg['username'] ?? '') }}">
            </div>

            <div class="mb-3">
              <label class="form-label">Password</label>
              <input name="password" type="password" class="form-control" value="{{ old('password', $cfg['password'] ?? '') }}">
            </div>

            <div class="mb-3">
              <label class="form-label">From email</label>
              <input name="from_email" type="email" class="form-control" value="{{ old('from_email', $cfg['from_email'] ?? '') }}">
            </div>

            <div class="mb-3">
              <label class="form-label">From name</label>
              <input name="from_name" class="form-control" value="{{ old('from_name', $cfg['from_name'] ?? config('app.name')) }}">
            </div>

            <div class="mb-3">
              <label class="form-label">Reply-To</label>
              <input name="reply_to" type="email" class="form-control" value="{{ old('reply_to', $cfg['reply_to'] ?? '') }}">
            </div>

            <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="allow_self_signed" name="allow_self_signed" value="1"
                    {{ old('allow_self_signed', ($cfg['allow_self_signed'] ?? false)) ? 'checked' : '' }}>
              <label class="form-check-label" for="allow_self_signed">Permitir certificados autofirmados</label>
            </div>

            <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="skip_host_verify" name="skip_host_verify" value="1"
                    {{ old('skip_host_verify', ($cfg['skip_host_verify'] ?? false)) ? 'checked' : '' }}>
              <label class="form-check-label" for="skip_host_verify">
                Omitir verificación de nombre de host (CN/SAN)
              </label>
              <small class="text-muted d-block">Sólo si no podés usar el host exacto del certificado.</small>
            </div>

            <button class="btn btn-primary w-100">Guardar</button>
          </form>

          <hr>
        </div>
      </div>
    </div>

    <div class="col-xl-4">
      <div class="block block-rounded">
        <div class="block-header block-header-default">
          <h3 class="block-title">Probar configuración</h3>
        </div>
        <div class="block-content block-content-full overflow-x-auto">   
          <form method="POST" action="{{ route('admin.plugins.smtp.test') }}">
            @csrf
            <div class="mb-3">
              <label class="form-label">Destinatario</label>
              <input name="to" type="email" class="form-control" required>
            </div>
            <button class="btn btn-alt-primary w-100">
                <i class="fa fa-paper-plane me-1"></i> Probar envío
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