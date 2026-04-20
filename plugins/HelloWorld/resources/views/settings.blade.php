@extends('layouts.backend')

@section('content')
<div class="content">
  <div class="block block-rounded">
    <div class="block-header block-header-default">
      <h3 class="block-title">Configuración: Hello World</h3>
    </div>
    <div class="block-content block-content-full overflow-x-auto">
      <form method="POST" action="{{ route('admin.plugins.helloworld.settings.save') }}" class="row g-3">
        @csrf
        <div class="col-12">
          <label class="form-label">Mensaje</label>
          <textarea name="message" class="form-control" rows="2">{{ old('message', $plugin->config['message'] ?? '¡Hola mundo!') }}</textarea>
        </div>
        <div class="col-md-6">
          <label class="form-label">Hook</label>
          @php $hook = old('hook', $plugin->config['hook'] ?? 'front:global:banner') @endphp
          <select name="hook" class="form-select">
            <option value="front:global:banner" {{ $hook==='front:global:banner'?'selected':'' }}>Banner global</option>
            <option value="front:global:body-end" {{ $hook==='front:global:body-end'?'selected':'' }}>Body end</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Estilo (clases CSS)</label>
          <input type="text" name="style" class="form-control"
            value="{{ old('style', $plugin->config['style'] ?? 'alert alert-info') }}">
        </div>
        <div class="col-12">
          <label class="form-label d-block">Secciones</label>
          @php
          $ctx = collect(old('contexts', $plugin->config['contexts'] ?? []))->values();
          $opts = ['home'=>'Home','product'=>'Producto','category'=>'Categoría','cart'=>'Carrito','checkout'=>'Checkout','all'=>'Todas'];
          @endphp
          @foreach($opts as $val => $label)
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" id="ctx_{{ $val }}" name="contexts[]"
              value="{{ $val }}" {{ $ctx->contains($val) ? 'checked' : '' }}>
            <label class="form-check-label" for="ctx_{{ $val }}">{{ $label }}</label>
          </div>
          @endforeach
        </div>
        <div class="col-12">
          <button class="btn btn-primary">Guardar</button>
          <a href="{{ route('admin.plugins.index') }}" class="btn btn-alt-secondary">Volver</a>
        </div>
      </form>


      @if ($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
      @endif

    </div>
  </div>
</div>
@endsection