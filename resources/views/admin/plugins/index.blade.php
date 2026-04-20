@extends('layouts.backend')

@section('content')
<!-- Hero -->
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-1">
                    Plugins
                </h1>
                <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                    Gestion de plugins
                </h2>
                
            </div>
            <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-alt">
                    <li class="breadcrumb-item">
                        <a class="link-fx" href="javascript:void(0)">Configuración</a>
                    </li>
                    <li class="breadcrumb-item" aria-current="page">
                        Plugins
                    </li>
                </ol>
            </nav>
        </div>
    </div>
</div>
<!-- END Hero -->

<div class="content">

      <div class="row">
        @foreach($catalog as $slug => $def)
          @php $row = $installed->get($slug); @endphp
          <div class="col-md-4 col-xl-4">


                  <div class="block block-rounded block-themed">
                    <div class="block-header">
                      <h3 class="block-title">{{ $def['name'] }} <small>v{{ $def['version'] }}</small></h3>
                    </div>
                    <div class="block-content block-content-full overflow-x-auto">

                      {{-- Imagen del plugin (si la detectamos) --}}
                      @if(!empty($def['image_url']))
                        <div class="mb-3">
                          <img src="{{ $def['image_url'] }}" alt="{{ $def['name'] }}" class="img-fluid rounded border w-100">
                        </div>
                      @endif 

                      <p>{{ $def['description'] }}</p>
                      @if(!$row || !$row->is_installed)
                        <form method="POST" action="{{ route('admin.plugins.install', $slug) }}">
                          @csrf
                          <button class="btn w-100 btn-primary">Instalar</button>
                        </form>
                      @else
                        <div class="btn-group w-100">
                          {{-- Si el plugin provee su propia ruta de settings, mostrala; si no, la genérica --}}


                          @php
                            // Si el plugin define admin_route en su plugin.json, usalo.
                            // Ej: "admin_route": "admin.plugins.smtp.edit"
                            $adminRoute = $def['admin_route'] ?? null;
                          @endphp

                          @if($adminRoute && Route::has($adminRoute))
                            <a href="{{ route($adminRoute) }}" class="btn btn-alt-secondary">Configurar</a>
                          @elseif(!empty($def['has_admin']))
                            {{-- Fallback a la pantalla genérica --}}
                            <a href="{{ route('admin.plugins.settings', $slug) }}" class="btn btn-alt-secondary">Configurar</a>
                          @endif                          

                          <form method="POST" action="{{ route('admin.plugins.toggle',$slug) }}">
                            @csrf
                            <button class="btn {{ $row->is_active?'btn-success':'btn-warning' }}">
                              {{ $row->is_active ? 'Activo' : 'Inactivo' }}
                            </button>
                          </form>
                        </div>
                      @endif

                    </div>
                  </div>

          </div>

        @endforeach

  </div>
</div>
@endsection