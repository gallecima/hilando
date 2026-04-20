@extends('layouts.backend')

@section('actions')
    @if($info)
        <a href="{{ route('admin.info.edit', $info) }}" class="ms-2 btn btn-sm btn-alt-primary">Editar</a>
    @else
        <a href="{{ route('admin.info.create') }}" class="ms-2 btn btn-sm btn-alt-primary">Cargar datos</a>
    @endif
@endsection

@section('content')
<div class="bg-body-light">
  <div class="content content-full">
    <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
      <div class="flex-grow-1">
        <h1 class="h3 fw-bold mb-1">Información institucional</h1>
        <h2 class="fs-base lh-base fw-medium text-muted mb-0">
          Datos que se usan en correos y el sitio (%site_title%, %company_name%, %company_address%, %company_website%, %support_email%).
        </h2>
      </div>
      <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-alt">
          <li class="breadcrumb-item"><a class="link-fx" href="{{ route('admin.dashboard') }}">Admin</a></li>
          <li class="breadcrumb-item" aria-current="page">Información</li>
        </ol>
      </nav>
    </div>
  </div>
</div>

<div class="content">
  <div class="block block-rounded">
    <div class="block-header block-header-default">
      <h3 class="block-title mb-0">Resumen</h3>
      <div class="block-options">
        @if($info)
          <a href="{{ route('admin.info.edit', $info) }}" class="btn btn-sm btn-primary">Editar</a>
        @else
          <a href="{{ route('admin.info.create') }}" class="btn btn-sm btn-primary">Cargar datos</a>
        @endif
      </div>
    </div>
    <div class="block-content block-content-full overflow-x-auto">
      @if(!$info)
        <p class="text-muted mb-0">Aún no hay datos cargados.</p>
      @else
        <div class="row">
          <div class="col-md-8">
            <dl class="row mb-0">
              <dt class="col-sm-4">Título del sitio</dt>
              <dd class="col-sm-8">{{ $info->site_title ?: '—' }}</dd>

              @if($supportsThemeVars ?? false)
                <dt class="col-sm-4">Variables CSS</dt>
                <dd class="col-sm-8">
                  @php $resolvedThemeVars = $info->resolvedThemeVars(); @endphp
                  <div class="small bg-body-light border rounded p-2" style="font-family: monospace;">
                    @foreach($resolvedThemeVars as $var => $value)
                      @if($var === 'google_font_default_url')
                        <div>google-font-default-url: {{ $value ?: '—' }};</div>
                      @elseif($var === 'google_font_primary_url')
                        <div>google-font-primary-url: {{ $value ?: '—' }};</div>
                      @elseif($var === 'scroll_behavior')
                        <div>scroll-behavior: {{ $value }};</div>
                      @else
                        <div>--{{ str_replace('_', '-', $var) }}: {{ $value }};</div>
                      @endif
                    @endforeach
                  </div>
                </dd>
              @endif

              <dt class="col-sm-4">Nombre de la empresa</dt>
              <dd class="col-sm-8">{{ $info->company_name ?: '—' }}</dd>

              <dt class="col-sm-4">Dirección</dt>
              <dd class="col-sm-8">{{ $info->company_address ?: '—' }}</dd>

              <dt class="col-sm-4">Sitio web</dt>
              <dd class="col-sm-8">
                @if($info->company_website)
                  <a href="{{ $info->company_website }}" target="_blank" rel="noopener">{{ $info->company_website }}</a>
                @else
                  —
                @endif
              </dd>

              <dt class="col-sm-4">Email de soporte</dt>
              <dd class="col-sm-8">{{ $info->support_email ?: '—' }}</dd>
            </dl>
          </div>
          <div class="col-md-4">
            <div class="mb-2 fw-semibold">Logo</div>
              @if($info && $info->logo_url)
                <div class="ratio ratio-1x1 border rounded d-flex align-items-center justify-content-center bg-white" style="max-width: 220px;">
                  <img src="{{ $info->logo_url }}" alt="Logo" class="img-fluid p-2">
                </div>
              @else
                <p class="text-muted mb-0">Sin logo cargado.</p>
              @endif
          </div>
        </div>
      @endif
    </div>
  </div>
</div>
@endsection
