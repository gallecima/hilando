        @php
        $menus = auth()->user()->perfil
            ->menus()
            ->with('menuGroup')
            ->where('activo', 1)
            ->leftJoin('menu_groups', 'menus.menu_group_id', '=', 'menu_groups.id')
            ->orderByRaw('CASE WHEN menus.menu_group_id IS NULL THEN 0 ELSE 1 END') // Sin grupo primero
            ->orderBy('menu_groups.orden')
            ->orderBy('menus.orden')
            ->select('menus.*')
            ->get()
            ->groupBy(function ($menu) {
                return $menu->menuGroup->nombre ?? null; // null = sin grupo
            });
        @endphp


      
      
      <!-- Side Header -->
      <div class="content-header">
        <!-- Logo -->
        <img src="{{ asset('media/logos/logo-w.svg') }}" style="max-width: 50%;">
        <!-- END Logo -->

        <!-- Extra -->
        <div class="d-flex align-items-center gap-1">
          <!-- Dark Mode -->
          <!-- Layout API, functionality initialized in Template._uiApiLayout() -->
          <div class="dropdown">
            <button type="button" class="btn btn-sm btn-alt-secondary" id="sidebar-dark-mode-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="far fa-fw fa-moon" data-dark-mode-icon></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end smini-hide border-0" aria-labelledby="sidebar-dark-mode-dropdown">
              <button type="button" class="dropdown-item d-flex align-items-center gap-2" data-toggle="layout" data-action="dark_mode_off" data-dark-mode="off">
                <i class="far fa-sun fa-fw opacity-50"></i>
                <span class="fs-sm fw-medium">Light</span>
              </button>
              <button type="button" class="dropdown-item d-flex align-items-center gap-2" data-toggle="layout" data-action="dark_mode_on" data-dark-mode="on">
                <i class="far fa-moon fa-fw opacity-50"></i>
                <span class="fs-sm fw-medium">Dark</span>
              </button>
              <button type="button" class="dropdown-item d-flex align-items-center gap-2" data-toggle="layout" data-action="dark_mode_system" data-dark-mode="system">
                <i class="fa fa-desktop fa-fw opacity-50"></i>
                <span class="fs-sm fw-medium">System</span>
              </button>
            </div>
          </div>
          <!-- END Dark Mode -->

          <!-- Close Sidebar, Visible only on mobile screens -->
          <!-- Layout API, functionality initialized in Template._uiApiLayout() -->
          <a class="d-lg-none btn btn-sm btn-alt-secondary ms-1" data-toggle="layout" data-action="sidebar_close" href="javascript:void(0)">
            <i class="fa fa-fw fa-times"></i>
          </a>
          <!-- END Close Sidebar -->
        </div>
        <!-- END Extra -->
      </div>
      <!-- END Side Header -->

      <!-- Sidebar Scrolling -->
      <div class="js-sidebar-scroll">
        <!-- Side Navigation -->
        <div class="content-side">
          <ul class="nav-main">

            @foreach ($menus as $grupo => $items)

                @if ($grupo)
                    <li class="nav-main-heading">{{ $grupo }}</li>
                @endif

                @foreach ($items as $menu)
                    <li class="nav-main-item">
                        @php
                            $isActive = request()->is(ltrim($menu->ruta, '/')) || request()->is(ltrim($menu->ruta, '/') . '/*');
                        @endphp
                        <a class="nav-main-link {{ $isActive ? 'active' : '' }}" href="{{ url($menu->ruta) }}">
                            @if ($menu->icono)
                                <i class="nav-main-link-icon {{ $menu->icono }}"></i>
                            @endif
                            <span class="nav-main-link-name">{{ $menu->nombre }}</span>
                        </a>
                    </li>
                @endforeach
            @endforeach    
            
{{-- ====== Plugins (hook) ====== --}}
@php
  $pluginsHtml = app()->bound('App\Support\Hooks')
    ? app('App\Support\Hooks')->render('admin:sidebar.plugins')
    : '';
@endphp

@if(trim($pluginsHtml) !== '')
  <li class="nav-main-heading">Plugins</li>
  {!! $pluginsHtml !!}
@endif
{{-- ============================ --}}            

            <li class="nav-main-item mt-4">

                <form method="POST" action="{{ route('logout') }}" id="logout-form">
                    @csrf
                    <a class="nav-main-link" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="nav-main-link-icon si si-logout"></i>
                        <span class="nav-main-link-name">Cerrar sesión</span>
                    </a>
                </form>                

            </li>            
          </ul>
        </div>
        <!-- END Side Navigation -->
      </div>
      <!-- END Sidebar Scrolling -->