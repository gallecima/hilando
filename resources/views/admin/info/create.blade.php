@extends('layouts.backend')

@section('content')
<div class="bg-body-light">
  <div class="content content-full">
    <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
      <div class="flex-grow-1">
        <h1 class="h3 fw-bold mb-1">Información institucional</h1>
        <h2 class="fs-base lh-base fw-medium text-muted mb-0">Cargar datos del sitio</h2>
      </div>
      <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-alt">
          <li class="breadcrumb-item"><a class="link-fx" href="{{ route('admin.dashboard') }}">Admin</a></li>
          <li class="breadcrumb-item"><a class="link-fx" href="{{ route('admin.info.index') }}">Información</a></li>
          <li class="breadcrumb-item" aria-current="page">Nueva</li>
        </ol>
      </nav>
    </div>
  </div>
</div>

<div class="content">
  <form method="POST" action="{{ route('admin.info.store') }}" enctype="multipart/form-data">
    @csrf
    @php
      $themeDefaults = \App\Models\SiteInfo::THEME_VAR_DEFAULTS;
      $themeVars = old('theme_vars', $themeDefaults);
      $themeVars = is_array($themeVars) ? array_replace($themeDefaults, $themeVars) : $themeDefaults;
    @endphp
    <div class="block block-rounded">
      <div class="block-header block-header-default">
        <h3 class="block-title">Datos</h3>
      </div>
      <div class="block-content block-content-full overflow-x-auto">
        <div class="row">
          <div class="col-md-8">
            <div class="mb-3">
              <label class="form-label">Título del sitio</label>
              <input type="text" class="form-control" name="site_title" value="{{ old('site_title') }}" placeholder="Ej: Bilingual Treasure">
            </div>

            @include('admin.info.partials.theme-vars-fields', ['themeVars' => $themeVars, 'supportsThemeVars' => $supportsThemeVars ?? false])

            <div class="mb-3">
              <label class="form-label">Nombre de la empresa</label>
              <input type="text" class="form-control" name="company_name" value="{{ old('company_name') }}">
            </div>
            <div class="mb-3">
              <label class="form-label">Dirección</label>
              <input type="text" class="form-control" name="company_address" value="{{ old('company_address') }}">
            </div>
            <div class="mb-3">
              <label class="form-label">Sitio web</label>
              <input type="url" class="form-control" name="company_website" value="{{ old('company_website') }}">
            </div>
            <div class="mb-3">
              <label class="form-label">Email de soporte</label>
              <input type="email" class="form-control" name="support_email" value="{{ old('support_email') }}">
            </div>
          </div>
          <div class="col-md-4">
            <label class="form-label">Logo (PNG/JPG)</label>
            <input type="file" class="form-control" name="logo" accept="image/*">
            <p class="text-muted small mt-2">Se adjuntará inline en correos como <code>cid:company_logo</code>.</p>
          </div>
        </div>
      </div>
      <div class="block-content block-content-full text-end">
        <a href="{{ route('admin.info.index') }}" class="btn btn-alt-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Guardar</button>
      </div>
    </div>
  </form>
</div>
@endsection
