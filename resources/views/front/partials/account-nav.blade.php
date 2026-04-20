<nav class="account-nav-menu" aria-label="Navegación de mi cuenta">
  <a
    href="{{ route('front.mi-cuenta.index') }}"
    class="btn account-nav-button {{ request()->routeIs('front.mi-cuenta.index') ? 'btn-primary account-nav-button-active' : 'btn-outline-secondary' }}"
    @if(request()->routeIs('front.mi-cuenta.index')) aria-current="page" @endif
  >
    Información personal
  </a>
  <a
    href="{{ route('front.mi-cuenta.pedidos') }}"
    class="btn account-nav-button {{ request()->routeIs('front.mi-cuenta.pedidos') || request()->routeIs('front.mi-cuenta.pedido') ? 'btn-primary account-nav-button-active' : 'btn-outline-secondary' }}"
    @if(request()->routeIs('front.mi-cuenta.pedidos') || request()->routeIs('front.mi-cuenta.pedido')) aria-current="page" @endif
  >
    Mis compras
  </a>
  <a
    href="{{ route('front.mi-cuenta.password') }}"
    class="btn account-nav-button {{ request()->routeIs('front.mi-cuenta.password') ? 'btn-primary account-nav-button-active' : 'btn-outline-secondary' }}"
    @if(request()->routeIs('front.mi-cuenta.password')) aria-current="page" @endif
  >
    Cambiar contraseña
  </a>
</nav>
