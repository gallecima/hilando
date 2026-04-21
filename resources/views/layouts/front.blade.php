<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @php
    $baseSiteTitle = $siteTitle ?? config('app.name', 'Tienda');
    $pageTitle = trim((string) $__env->yieldContent('title'));
    $fullTitle = $pageTitle !== '' ? ($baseSiteTitle . ' | ' . $pageTitle) : $baseSiteTitle;
    $brandLogo = $siteInfo?->logo_url ?: asset('images/logo.svg');
    $brandName = $siteInfo?->company_name ?: $baseSiteTitle;
    $supportEmail = $siteInfo?->support_email ?: 'info@hilandoculturas.com';
    $companyAddress = $siteInfo?->company_address ?: 'Salta, Argentina';
    $companyWebsite = $siteInfo?->company_website ?: url('/');
    $catalogUrl = route('category.show', 'todas');
    $cartBadgeCount = $cartItemCount ?? $cartCount ?? 0;
    $favoriteBadgeCount = collect(session('favorite_product_ids', []))
      ->map(fn ($id) => (int) $id)
      ->filter(fn ($id) => $id > 0)
      ->unique()
      ->count();
    $isCustomerAuthenticated = auth('customer')->check();
  @endphp
  <title>{{ $fullTitle }}</title>
  <link rel="shortcut icon" href="{{ asset('media/favicons/favicon.png') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  @include('front.partials.typography-styles')
  @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100 @yield('body_class')">
  <header class="border-bottom bg-white sticky-top">
    <nav class="navbar navbar-expand-lg bg-white">
      <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
          <img src="{{ $brandLogo }}" alt="{{ $brandName }}" height="52">
        </a>

        <div class="front-navbar-desktop d-none d-lg-flex ms-auto align-items-lg-center gap-lg-4">
          <ul class="navbar-nav mb-3 mb-lg-0 align-items-lg-center">
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Inicio</a>
            </li>
            <li class="nav-item">
              <a class="nav-link {{ request()->is('categoria/todas') ? 'active' : '' }}" href="{{ $catalogUrl }}">Productos</a>
            </li>
            @if(($menuCategories ?? collect())->isNotEmpty())
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  Categorías
                </a>
                <ul class="dropdown-menu">
                  @foreach($menuCategories as $category)
                    <li>
                      <a class="dropdown-item" href="{{ route('category.show', $category->slug) }}">{{ $category->name }}</a>
                    </li>
                    @foreach($category->children as $child)
                      <li>
                        <a class="dropdown-item" href="{{ route('category.show', $child->slug) }}">- {{ $child->name }}</a>
                      </li>
                    @endforeach
                  @endforeach
                </ul>
              </li>
            @endif
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('about.show') ? 'active' : '' }}" href="{{ route('about.show') }}">Sobre Hilando</a>
            </li>
            <li class="nav-item">
              <a class="nav-link {{ request()->routeIs('contact.show') ? 'active' : '' }}" href="{{ route('contact.show') }}">Contacto</a>
            </li>
          </ul>

          <div class="d-lg-flex align-items-lg-center gap-2 mt-3 mt-lg-0">
            <div class="d-flex gap-2">
              <button type="button" class="header-action-button position-relative" data-open-cart aria-label="Abrir carrito">
                <img src="{{ asset('media/iconos/cart.svg') }}" alt="" class="header-action-icon" aria-hidden="true">
                <span class="badge rounded-pill text-bg-light position-absolute top-0 start-100 translate-middle header-action-badge" data-cart-badge>{{ $cartBadgeCount }}</span>
              </button>

              <button type="button" class="header-action-button position-relative" data-open-favorites aria-label="Abrir favoritos">
                <img src="{{ asset('media/iconos/favorite.svg') }}" alt="" class="header-action-icon" aria-hidden="true">
                <span class="badge rounded-pill text-bg-light position-absolute top-0 start-100 translate-middle header-action-badge" data-favorite-badge>{{ $favoriteBadgeCount }}</span>
              </button>

              <button type="button" class="header-action-button" data-open-search aria-label="Buscar productos">
                <img src="{{ asset('media/iconos/search.svg') }}" alt="" class="header-action-icon" aria-hidden="true">
              </button>

              @if($isCustomerAuthenticated)
                <div class="dropdown">
                  <button class="header-action-button position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Mi cuenta">
                    <img src="{{ asset('media/iconos/user.svg') }}" alt="" class="header-action-icon" aria-hidden="true">
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('front.mi-cuenta.index') }}">Datos personales</a></li>
                    <li><a class="dropdown-item" href="{{ route('front.mi-cuenta.pedidos') }}">Mis pedidos</a></li>
                    <li><a class="dropdown-item" href="{{ route('front.mi-cuenta.password') }}">Cambiar contraseña</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                      <form action="{{ route('front.logout') }}" method="POST" class="px-3">
                        @csrf
                        <button type="submit" class="btn btn-link text-danger p-0">Cerrar sesión</button>
                      </form>
                    </li>
                  </ul>
                </div>
              @else
                <a href="{{ route('customer.login') }}" class="header-action-link" aria-label="Ingresar">
                  <img src="{{ asset('media/iconos/user.svg') }}" alt="" class="header-action-icon" aria-hidden="true">
                </a>
              @endif
            </div>
          </div>
        </div>

        <div class="front-navbar-mobile d-lg-none ms-auto">
          <button
            type="button"
            class="mobile-menu-toggle"
            data-mobile-menu-toggle
            aria-controls="mobileMenuPanel"
            aria-expanded="false"
            aria-label="Abrir menú"
          >
            <span class="mobile-menu-toggle-box" aria-hidden="true">
              <span class="mobile-menu-toggle-line"></span>
              <span class="mobile-menu-toggle-line"></span>
              <span class="mobile-menu-toggle-line"></span>
            </span>
          </button>
        </div>
      </div>
    </nav>

    <div class="mobile-menu-panel d-lg-none" id="mobileMenuPanel" aria-hidden="true">
      <div class="container mobile-menu-panel-inner">
        <div class="mobile-menu-header">
          <button type="button" class="mobile-menu-close" data-mobile-menu-close aria-label="Cerrar menú">
            <span class="mobile-menu-close-icon" aria-hidden="true">&times;</span>
          </button>
        </div>

        <nav class="mobile-menu-primary" aria-label="Menú móvil principal">
          <a href="{{ route('home') }}" class="mobile-menu-link {{ request()->routeIs('home') ? 'active' : '' }}" data-mobile-menu-close>Inicio</a>
          <a href="{{ $catalogUrl }}" class="mobile-menu-link {{ request()->is('categoria/todas') ? 'active' : '' }}" data-mobile-menu-close>Productos</a>
          <a href="{{ route('about.show') }}" class="mobile-menu-link {{ request()->routeIs('about.show') ? 'active' : '' }}" data-mobile-menu-close>Sobre Hilando</a>
          <a href="{{ route('contact.show') }}" class="mobile-menu-link {{ request()->routeIs('contact.show') ? 'active' : '' }}" data-mobile-menu-close>Contacto</a>
        </nav>

        @if(($menuCategories ?? collect())->isNotEmpty())
          <div class="mobile-menu-secondary">
            <p class="mobile-menu-label mb-3">Categorías</p>
            <div class="mobile-menu-category-list">
              @foreach(($menuCategories ?? collect())->take(6) as $category)
                <a href="{{ route('category.show', $category->slug) }}" class="mobile-menu-category-link" data-mobile-menu-close>{{ $category->name }}</a>
              @endforeach
              <a href="{{ $catalogUrl }}" class="mobile-menu-category-link" data-mobile-menu-close>Ver catálogo completo</a>
            </div>
          </div>
        @endif

        <div class="mobile-menu-footer">
          <div class="mobile-menu-actions">
            <button type="button" class="mobile-menu-action-button" data-open-cart data-mobile-menu-close>
              Carrito
              <span class="mobile-menu-action-badge" data-cart-badge>{{ $cartBadgeCount }}</span>
            </button>
            <button type="button" class="mobile-menu-action-button" data-open-favorites data-mobile-menu-close>
              Favoritos
              <span class="mobile-menu-action-badge" data-favorite-badge>{{ $favoriteBadgeCount }}</span>
            </button>
            <button type="button" class="mobile-menu-action-button" data-open-search data-mobile-menu-close>Buscar</button>
            @if($isCustomerAuthenticated)
              <a href="{{ route('front.mi-cuenta.index') }}" class="mobile-menu-action-button" data-mobile-menu-close>Mi cuenta</a>
            @else
              <a href="{{ route('customer.login') }}" class="mobile-menu-action-button" data-mobile-menu-close>Ingresar</a>
            @endif
          </div>

          @if($isCustomerAuthenticated)
            <form action="{{ route('front.logout') }}" method="POST" class="mobile-menu-logout-form">
              @csrf
              <button type="submit" class="mobile-menu-logout-button">Cerrar sesión</button>
            </form>
          @endif
        </div>
      </div>
    </div>
  </header>

  @unless($__env->hasSection('inline_flash_messages'))
    @include('front.partials.flash-messages', ['wrapperClass' => 'container pt-3'])
  @endunless

  <main class="flex-grow-1">
    @yield('content')
  </main>

  <footer class="bg-dark text-light py-5 mt-5">
    <div class="container">
      <div class="row g-4">
        <div class="col-12 col-md-6 col-lg-3">
          <h2 class="h5">{{ $brandName }}</h2>
          <p class="mb-0">Productos y recursos pensados para acompañar procesos creativos, educativos y culturales.</p>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
          <h2 class="h5">Menú</h2>
          <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link px-0 text-light" href="{{ route('home') }}">Inicio</a></li>
            <li class="nav-item"><a class="nav-link px-0 text-light" href="{{ $catalogUrl }}">Productos</a></li>
            <li class="nav-item"><a class="nav-link px-0 text-light" href="{{ route('about.show') }}">Sobre Hilando</a></li>
            <li class="nav-item"><a class="nav-link px-0 text-light" href="{{ route('contact.show') }}">Contacto</a></li>
            <li class="nav-item"><a class="nav-link px-0 text-light" href="{{ route('products.free') }}">Recursos gratuitos</a></li>
            <li class="nav-item"><button type="button" class="btn btn-link nav-link px-0 text-light text-start" data-open-cart>Ver carrito</button></li>
          </ul>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
          <h2 class="h5">Categorías</h2>
          <ul class="nav flex-column">
            @foreach(($menuCategories ?? collect())->take(6) as $category)
              <li class="nav-item">
                <a class="nav-link px-0 text-light" href="{{ route('category.show', $category->slug) }}">{{ $category->name }}</a>
              </li>
            @endforeach
            <li class="nav-item">
              <a class="nav-link px-0 text-light" href="{{ $catalogUrl }}">Ver catálogo completo</a>
            </li>
          </ul>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
          <h2 class="h5">Contacto</h2>
          <p class="mb-2">{{ $companyAddress }}</p>
          <p class="mb-2"><a class="link-light" href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a></p>
          <p class="mb-3"><a class="link-light" href="{{ $companyWebsite }}" target="_blank" rel="noreferrer">Sitio institucional</a></p>
          <form class="d-flex gap-2" data-newsletter-form>
            <input type="email" class="form-control" name="email" placeholder="Tu email" required>
            <button type="submit" class="btn btn-primary">Enviar</button>
          </form>
          <div class="small mt-2" data-newsletter-feedback></div>
        </div>
      </div>
    </div>
  </footer>

  <div class="offcanvas offcanvas-end" tabindex="-1" id="cartOffcanvas" aria-labelledby="cartOffcanvasLabel">
    <div class="offcanvas-header">
      <h2 class="offcanvas-title h5 mb-0" id="cartOffcanvasLabel">Carrito</h2>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body" id="cart-offcanvas-body">
      <p class="text-body-secondary mb-0">Cargando carrito...</p>
    </div>
  </div>

  <div class="offcanvas offcanvas-end" tabindex="-1" id="favoritesOffcanvas" aria-labelledby="favoritesOffcanvasLabel">
    <div class="offcanvas-header">
      <h2 class="offcanvas-title h5 mb-0" id="favoritesOffcanvasLabel">Favoritos</h2>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body" id="favorites-offcanvas-body">
      <p class="text-body-secondary mb-0">Cargando favoritos...</p>
    </div>
  </div>

  <div class="offcanvas offcanvas-top search-offcanvas" tabindex="-1" id="searchOffcanvas" aria-labelledby="searchOffcanvasLabel">
    <div class="search-offcanvas-panel">
      <div class="offcanvas-header">
        <h2 class="offcanvas-title h5 mb-0" id="searchOffcanvasLabel">Buscar productos</h2>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
      </div>
      <div class="offcanvas-body">
        <div class="search-offcanvas-shell">
          <form action="{{ route('product.search') }}" method="GET" class="search-offcanvas-form">
            <div class="row g-3 align-items-center">
              <div class="col-12 col-lg">
                <input
                  type="search"
                  name="q"
                  class="form-control"
                  placeholder="Buscar productos, categorías o materiales"
                  value="{{ request('q') }}"
                  autocomplete="off"
                  data-search-input
                >
              </div>
              <div class="col-12 col-lg-auto">
                <button class="btn btn-primary w-100" type="submit">Buscar</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    window.frontStore = window.frontStore || {};

    document.addEventListener('DOMContentLoaded', function () {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const cartBadges = Array.from(document.querySelectorAll('[data-cart-badge]'));
      const favoriteBadges = Array.from(document.querySelectorAll('[data-favorite-badge]'));
      const cartOffcanvasElement = document.getElementById('cartOffcanvas');
      const cartOffcanvasBody = document.getElementById('cart-offcanvas-body');
      const cartOffcanvas = cartOffcanvasElement ? new bootstrap.Offcanvas(cartOffcanvasElement) : null;
      const favoritesOffcanvasElement = document.getElementById('favoritesOffcanvas');
      const favoritesOffcanvasBody = document.getElementById('favorites-offcanvas-body');
      const favoritesOffcanvas = favoritesOffcanvasElement ? new bootstrap.Offcanvas(favoritesOffcanvasElement) : null;
      const searchOffcanvasElement = document.getElementById('searchOffcanvas');
      const searchOffcanvas = searchOffcanvasElement ? new bootstrap.Offcanvas(searchOffcanvasElement) : null;
      const searchInput = searchOffcanvasElement?.querySelector('[data-search-input]') || null;
      const newsletterForm = document.querySelector('[data-newsletter-form]');
      const newsletterFeedback = document.querySelector('[data-newsletter-feedback]');
      const mobileMenuToggle = document.querySelector('[data-mobile-menu-toggle]');
      const mobileMenuPanel = document.getElementById('mobileMenuPanel');
      const mobileMenuClosers = Array.from(document.querySelectorAll('[data-mobile-menu-close]'));

      function setBadgeValue(badges, value) {
        badges.forEach(function (badge) {
          badge.textContent = value;
        });
      }

      function closeMobileMenu() {
        if (!mobileMenuPanel || !mobileMenuToggle) return;

        document.body.classList.remove('mobile-menu-open');
        mobileMenuToggle.setAttribute('aria-expanded', 'false');
        mobileMenuToggle.setAttribute('aria-label', 'Abrir menú');
        mobileMenuPanel.setAttribute('aria-hidden', 'true');
      }

      function openMobileMenu() {
        if (!mobileMenuPanel || !mobileMenuToggle) return;

        document.body.classList.add('mobile-menu-open');
        mobileMenuToggle.setAttribute('aria-expanded', 'true');
        mobileMenuToggle.setAttribute('aria-label', 'Cerrar menú');
        mobileMenuPanel.setAttribute('aria-hidden', 'false');
      }

      function toggleMobileMenu() {
        if (document.body.classList.contains('mobile-menu-open')) {
          closeMobileMenu();
          return;
        }

        openMobileMenu();
      }

      function setNewsletterFeedback(type, message) {
        if (!newsletterFeedback) return;
        newsletterFeedback.className = 'small mt-2';
        if (type === 'success') newsletterFeedback.classList.add('text-success');
        if (type === 'error') newsletterFeedback.classList.add('text-danger');
        newsletterFeedback.textContent = message || '';
      }

      function updateCartBadge() {
        fetch(@json(route('cart.count')), {
          headers: { Accept: 'application/json' },
          credentials: 'same-origin'
        })
          .then(function (response) { return response.json(); })
          .then(function (payload) {
            setBadgeValue(cartBadges, payload.count ?? 0);
          })
          .catch(function () {});
      }

      function updateFavoriteBadge(countOverride) {
        if (typeof countOverride === 'number') {
          setBadgeValue(favoriteBadges, countOverride);
          return;
        }

        fetch(@json(route('favorites.count')), {
          headers: { Accept: 'application/json' },
          credentials: 'same-origin'
        })
          .then(function (response) { return response.json(); })
          .then(function (payload) {
            setBadgeValue(favoriteBadges, payload.count ?? 0);
          })
          .catch(function () {});
      }

      function openCart() {
        if (!cartOffcanvasBody || !cartOffcanvas) return;
        closeMobileMenu();

        cartOffcanvasBody.innerHTML = '<p class="text-body-secondary mb-0">Cargando carrito...</p>';

        fetch(@json(route('cart.partial')), {
          headers: {
            Accept: 'text/html',
            'X-Requested-With': 'XMLHttpRequest'
          },
          credentials: 'same-origin'
        })
          .then(function (response) { return response.text(); })
          .then(function (html) {
            cartOffcanvasBody.innerHTML = html;
            cartOffcanvas.show();
          })
          .catch(function () {
            cartOffcanvasBody.innerHTML = '<div class="alert alert-danger">No se pudo cargar el carrito.</div>';
            cartOffcanvas.show();
          });
      }

      function openFavorites() {
        if (!favoritesOffcanvasBody || !favoritesOffcanvas) return;
        closeMobileMenu();

        favoritesOffcanvasBody.innerHTML = '<p class="text-body-secondary mb-0">Cargando favoritos...</p>';

        fetch(@json(route('favorites.partial')), {
          headers: {
            Accept: 'text/html',
            'X-Requested-With': 'XMLHttpRequest'
          },
          credentials: 'same-origin'
        })
          .then(function (response) { return response.text(); })
          .then(function (html) {
            favoritesOffcanvasBody.innerHTML = html;
            bindAddToCartButtons();
            bindFavoriteButtons(favoritesOffcanvasBody);
            favoritesOffcanvas.show();
          })
          .catch(function () {
            favoritesOffcanvasBody.innerHTML = '<div class="alert alert-danger">No se pudo cargar la lista de favoritos.</div>';
            favoritesOffcanvas.show();
          });
      }

      function bindAddToCartButtons() {
        document.querySelectorAll('.js-add-to-cart').forEach(function (button) {
          if (button.dataset.bound === '1') return;
          button.dataset.bound = '1';

          button.addEventListener('click', function () {
            const productId = this.dataset.id;
            const quantity = Number(this.dataset.quantity || 1);

            this.disabled = true;

            fetch(@json(route('cart.add')), {
              method: 'POST',
              credentials: 'same-origin',
              headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken
              },
              body: JSON.stringify({
                product_id: productId,
                quantity: quantity
              })
            })
              .then(function (response) { return response.json(); })
              .then(function (payload) {
                if (payload.success !== true) {
                  throw new Error(payload.message || 'No se pudo agregar el producto.');
                }

                updateCartBadge();
                openCart();
              })
              .catch(function () {
                alert('No se pudo agregar el producto al carrito.');
              })
              .finally(() => {
                this.disabled = false;
              });
          });
        });
      }

      function syncFavoriteButtons(productId, active) {
        document.querySelectorAll('.js-toggle-favorite[data-id="' + productId + '"]').forEach(function (button) {
          button.dataset.active = active ? '1' : '0';
          button.classList.toggle('active', active);
          button.classList.toggle('btn-dark', active);
          button.classList.toggle('text-white', active);
          button.classList.toggle('btn-outline-secondary', !active);

          const label = button.querySelector('[data-favorite-label]');
          const activeLabel = button.dataset.activeLabel || 'Quitar de favoritos';
          const inactiveLabel = button.dataset.inactiveLabel || 'Agregar a favoritos';
          const nextLabel = active ? activeLabel : inactiveLabel;

          if (label) {
            label.textContent = nextLabel;
          }

          button.setAttribute('aria-label', nextLabel);
        });
      }

      function bindFavoriteButtons(root) {
        (root || document).querySelectorAll('.js-toggle-favorite').forEach(function (button) {
          if (button.dataset.bound === '1') return;
          button.dataset.bound = '1';

          button.addEventListener('click', function () {
            const productId = this.dataset.id;
            if (!productId) return;

            this.disabled = true;

            fetch(@json(route('favorites.toggle')), {
              method: 'POST',
              credentials: 'same-origin',
              headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken
              },
              body: JSON.stringify({ product_id: productId })
            })
              .then(function (response) { return response.json(); })
              .then(function (payload) {
                if (payload.success !== true) {
                  throw new Error(payload.message || 'No se pudo actualizar favoritos.');
                }

                syncFavoriteButtons(productId, payload.active === true);
                updateFavoriteBadge(payload.count ?? 0);

                if (favoritesOffcanvasElement?.classList.contains('show')) {
                  openFavorites();
                }
              })
              .catch(function () {
                alert('No se pudo actualizar favoritos.');
              })
              .finally(() => {
                this.disabled = false;
              });
          });
        });
      }

      document.querySelectorAll('[data-open-cart]').forEach(function (button) {
        button.addEventListener('click', function () {
          openCart();
        });
      });

      document.querySelectorAll('[data-open-favorites]').forEach(function (button) {
        button.addEventListener('click', function () {
          openFavorites();
        });
      });

      document.querySelectorAll('[data-open-search]').forEach(function (button) {
        button.addEventListener('click', function () {
          closeMobileMenu();
          searchOffcanvas?.show();
        });
      });

      mobileMenuToggle?.addEventListener('click', function () {
        toggleMobileMenu();
      });

      mobileMenuClosers.forEach(function (element) {
        element.addEventListener('click', function () {
          closeMobileMenu();
        });
      });

      mobileMenuPanel?.addEventListener('click', function (event) {
        if (event.target === mobileMenuPanel) {
          closeMobileMenu();
        }
      });

      document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
          closeMobileMenu();
        }
      });

      window.addEventListener('resize', function () {
        if (window.innerWidth >= 992) {
          closeMobileMenu();
        }
      });

      searchOffcanvasElement?.addEventListener('shown.bs.offcanvas', function () {
        searchInput?.focus();
        searchInput?.select();
      });

      newsletterForm?.addEventListener('submit', function (event) {
        event.preventDefault();
        const email = (newsletterForm.querySelector('input[name="email"]')?.value || '').trim();

        if (email === '') {
          setNewsletterFeedback('error', 'Ingresá un email válido.');
          return;
        }

        setNewsletterFeedback('', '');

        fetch(@json(route('front.newsletter.subscribe')), {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken
          },
          body: JSON.stringify({ email: email })
        })
          .then(async function (response) {
            const payload = await response.json().catch(function () { return {}; });
            if (!response.ok || payload.ok !== true) {
              throw new Error(payload.message || 'No se pudo procesar la suscripción.');
            }
            return payload;
          })
          .then(function () {
            newsletterForm.reset();
            setNewsletterFeedback('success', 'Suscripción recibida.');
          })
          .catch(function (error) {
            setNewsletterFeedback('error', error.message || 'No se pudo procesar la suscripción.');
          });
      });

      window.frontStore.updateCartBadge = updateCartBadge;
      window.frontStore.updateFavoriteBadge = updateFavoriteBadge;
      window.frontStore.openCart = openCart;
      window.frontStore.openFavorites = openFavorites;
      window.frontStore.bindAddToCartButtons = bindAddToCartButtons;
      window.frontStore.bindFavoriteButtons = bindFavoriteButtons;

      bindAddToCartButtons();
      bindFavoriteButtons(document);
      updateCartBadge();
      updateFavoriteBadge();
    });
  </script>
  @stack('scripts')
  @yield('scripts')
</body>
</html>
