@extends('layouts.backend')

@section('content')
<div class="bg-body-light">
  <div class="content content-full">
    <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
      <div class="flex-grow-1">
        <h1 class="h3 fw-bold mb-1">Información institucional</h1>
        <h2 class="fs-base lh-base fw-medium text-muted mb-0">Editar datos del sitio</h2>
      </div>
      <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-alt">
          <li class="breadcrumb-item"><a class="link-fx" href="{{ route('admin.dashboard') }}">Admin</a></li>
          <li class="breadcrumb-item"><a class="link-fx" href="{{ route('admin.info.index') }}">Información</a></li>
          <li class="breadcrumb-item" aria-current="page">Editar</li>
        </ol>
      </nav>
    </div>
  </div>
</div>

<div class="content">
  <form method="POST" action="{{ route('admin.info.update', $info) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @php
      $themeDefaults = \App\Models\SiteInfo::THEME_VAR_DEFAULTS;
      $storedThemeVars = ($supportsThemeVars ?? false) ? $info->resolvedThemeVars() : $themeDefaults;
      $themeVars = old('theme_vars', $storedThemeVars);
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
              <input type="text" class="form-control" name="site_title" value="{{ old('site_title', $info->site_title) }}" placeholder="Ej: Bilingual Treasure">
            </div>

            @include('admin.info.partials.theme-vars-fields', ['themeVars' => $themeVars, 'supportsThemeVars' => $supportsThemeVars ?? false])

            <div class="mb-3">
              <label class="form-label">Nombre de la empresa</label>
              <input type="text" class="form-control" name="company_name" value="{{ old('company_name', $info->company_name) }}">
            </div>
            <div class="mb-3">
              <label class="form-label">Dirección</label>
              <input type="text" class="form-control" name="company_address" value="{{ old('company_address', $info->company_address) }}">
            </div>
            <div class="mb-3">
              <label class="form-label">Sitio web</label>
              <input type="url" class="form-control" name="company_website" value="{{ old('company_website', $info->company_website) }}">
            </div>
            <div class="mb-3">
              <label class="form-label">Email de soporte</label>
              <input type="email" class="form-control" name="support_email" value="{{ old('support_email', $info->support_email) }}">
            </div>
          </div>
          <div class="col-md-4">
            <div class="mb-2 fw-semibold">Logo actual</div>
            @if($info && $info->logo_url)
              <img src="{{ $info->logo_url }}" alt="Logo actual" class="img-fluid mb-2" style="max-width: 220px;">
              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="remove_logo" id="remove_logo" value="1">
                <label class="form-check-label" for="remove_logo">Quitar logo actual</label>
              </div>
            @else
              <p class="text-muted">Sin logo cargado.</p>
            @endif

            <hr>
            <label class="form-label">Subir nuevo logo (PNG/JPG)</label>
            <input type="file" class="form-control" name="logo" accept="image/*">
            <p class="text-muted small mt-2">Se adjuntará inline en correos como <code>cid:company_logo</code>.</p>
          </div>
        </div>
      </div>
      <div class="block-content block-content-full text-end">
        <a href="{{ route('admin.info.index') }}" class="btn btn-alt-secondary">Cancelar</a>
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
      </div>
    </div>
  </form>
</div>
@endsection
