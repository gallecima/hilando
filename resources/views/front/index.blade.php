@extends('layouts.front')

@section('title', 'Inicio')
@section('body_class', 'home-page')

@push('styles')
  <style>
    :root {
      --home-bg: #e7ddd0;
      --home-paper: #f7f2eb;
      --home-ink: #102228;
      --home-ink-soft: rgba(16, 34, 40, 0.76);
      --home-shadow: 0 24px 60px rgba(16, 34, 40, 0.16);
    }

    body.home-page {
      background: #f7f2eb;
    }

    body.home-page header {
      position: absolute !important;
      top: 0;
      right: 0;
      left: 0;
      z-index: 1040;
      border-bottom: 0 !important;
      background: transparent !important;
    }

    body.home-page header .navbar {
      background: transparent !important;
      padding-top: 1rem;
      padding-bottom: 1rem;
    }

    body.home-page header .navbar-brand img {
      filter: brightness(0) invert(1);
      max-width:200px !important;
      height:auto;
    }

    body.home-page header .nav-link,
    body.home-page header .navbar-toggler,
    body.home-page header .btn,
    body.home-page header .dropdown-toggle {
      color: rgba(255, 255, 255, 0.92) !important;
    }

    body.home-page header .nav-link.active,
    body.home-page header .nav-link:hover,
    body.home-page header .dropdown-item:hover {
      color: #fff !important;
    }

    body.home-page header .navbar-toggler {
      border-color: rgba(255, 255, 255, 0.35);
    }

    body.home-page header .navbar-toggler-icon {
      filter: invert(1);
    }

    body.home-page header form[action="{{ route('product.search') }}"] {
      display: none !important;
    }

    body.home-page header .btn-outline-secondary {
      border-color: rgba(255, 255, 255, 0.42);
      background: transparent;
    }

    body.home-page header .btn-primary {
      border-color: rgba(255, 255, 255, 0.12);
      background: rgba(255, 255, 255, 0.12);
    }

    body.home-page header .dropdown-menu {
      background: rgba(16, 34, 40, 0.96);
      border: 1px solid rgba(255, 255, 255, 0.12);
      backdrop-filter: blur(12px);
    }

    body.home-page header .dropdown-item,
    body.home-page header .btn-link {
      color: rgba(255, 255, 255, 0.92);
    }

    body.home-page header .header-action-icon {
      filter: brightness(0) invert(1);
    }

    body.home-page footer {
      margin-top: 0 !important;
      background: #050505 !important;
    }

    .home-hero-section {
      position: relative;
      width: 100%;
      padding-bottom: 0;
    }

    .home-hero-card {
      border: 0;
      overflow: hidden;
      background: var(--home-ink);
    }

    .home-hero-card .carousel-item {
      position: relative;
    }

    .home-hero-image {
      height: 50vh;
      object-fit: cover;
      filter: saturate(0.82) brightness(0.58);
    }

    .home-hero-overlay {
      position: absolute;
      inset: 0;
      background:
        linear-gradient(180deg, rgba(9, 24, 28, 0.44) 0%, rgba(9, 24, 28, 0.18) 24%, rgba(9, 24, 28, 0.64) 100%),
        linear-gradient(90deg, rgba(9, 24, 28, 0.38) 0%, rgba(9, 24, 28, 0.08) 42%, rgba(9, 24, 28, 0.34) 100%);
    }

    .home-hero-copy {
      position: absolute;
      inset: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 7rem 1.5rem 4rem;
      text-align: center;
      color: #fff;
    }

    .home-hero-kicker {
      font-size: 0.78rem;
      letter-spacing: 0.28em;
      text-transform: uppercase;
      opacity: 0.82;
    }

    .home-hero-title {
      margin: 0 auto;
      font-family: var(--font-heading);
      font-size: clamp(2.2rem, 4.4vw, 3.3rem);
      font-weight: 400;
      font-style: normal;
      line-height: 1.04;
      text-transform: uppercase;
    }

    .home-hero-emphasis {
      color: #f6ebde;
      font-weight: 600;
    }

    .home-hero-text {
      max-width: 42rem;
      margin: 0 auto;
      color: rgba(255, 255, 255, 0.84);
      font-size: 1.02rem;
      white-space: pre-line;
    }

    .home-hero-actions {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 1rem;
    }

    .home-hero-actions .btn {
      min-width: 160px;
      padding: 0.85rem 1.5rem;
      border-radius: 999px;
      box-shadow: 0 8px 24px rgba(9, 24, 28, 0.18);
    }

    .home-hero-actions .btn-outline-light {
      background: rgba(255, 255, 255, 0.08);
    }

    .home-hero-carousel .carousel-indicators {
      bottom: 1.4rem;
      gap: 0.45rem;
      margin-bottom: 0;
    }

    .home-hero-carousel .carousel-indicators button {
      width: 0.7rem;
      height: 0.7rem;
      margin: 0;
      border-radius: 999px;
      border-top: 0;
      border-bottom: 0;
    }

    .home-hero-carousel .carousel-control-prev,
    .home-hero-carousel .carousel-control-next {
      width: 8%;
    }

    .home-categories-panel {
      position: relative;
      width: 100%;
      margin-top: -4.5rem;
      padding: 2.25rem;
      border-radius: 2rem 2rem 0 0;
      background: var(--home-paper);
      box-shadow: none;
      z-index: 2;
      padding-top:5rem !important;
      padding-bottom:5rem !important;
    }

    .home-categories-section {
      position: relative;
      width: 100%;
      padding-bottom: 0;
    }

    .home-section-kicker {
      color: rgba(16, 34, 40, 0.62);
      font-size: 0.8rem;
      letter-spacing: 0.22em;
      text-transform: uppercase;
    }

    .home-section-title {
      color: var(--home-ink);
      font-size: clamp(2rem, 3vw, 3rem);
      line-height: 1.02;
    }

    .home-category-card {
      position: relative;
      display: block;
      min-height: 220px;
      border-radius: 1.6rem;
      overflow: hidden;
      text-decoration: none;
      background: #241b17;
      box-shadow: 0 16px 32px rgba(36, 27, 23, 0.12);
    }

    .home-category-card img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      filter: brightness(0.78);
      transition: transform 0.45s ease, filter 0.45s ease;
    }

    .home-category-card::after {
      content: "";
      position: absolute;
      inset: 0;
      background:
        linear-gradient(180deg, rgba(10, 10, 10, 0.08) 0%, rgba(10, 10, 10, 0.3) 60%, rgba(10, 10, 10, 0.46) 100%);
    }

    .home-category-card:hover img,
    .home-category-card:focus img {
      transform: scale(1.05);
      filter: brightness(0.86);
    }

    .home-category-title {
      position: absolute;
      inset: 0;
      z-index: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1rem;
      color: #fff;
      text-align: center;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      font-size: clamp(1rem, 1.2vw, 1.25rem);
      font-family: var(--font-heading);
      font-weight: 500;
    }

    .home-story-section {
      position: relative;
      width: 100%;
      margin-top: 0;
      margin-bottom: 0;
      background: var(--home-paper);
    }

    .home-story-banner {
      position: relative;
      height: clamp(300px, 34vw, 400px);
      overflow: hidden;
      background: var(--home-paper);
      border-radius: 2rem 2rem 0 0;
    }

    .home-story-banner img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      filter: grayscale(1) contrast(1.02) brightness(0.8);
    }

    .home-story-banner::after {
      content: "";
      position: absolute;
      inset: 0;
      background: rgba(0,0,0,.5);
    }

    .home-story-content {
      position: absolute;
      inset: 0;
      z-index: 1;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 2rem;
      padding: 3rem 12rem;
    }

    .home-story-title {
      /* max-width: 18ch; */
      color: #fff;
      font-size: clamp(1.8rem, 3vw, 1.4rem);
      line-height: 1.02;
      text-transform: uppercase;
      font-weight:400;
      font-family: var(--font-highlight)
    }

    .home-story-content .btn {
      border-radius: .7rem;
      padding: 1rem 1.5rem;
      white-space: nowrap;
      background-color: #F7F2EB !important;
      font-weight:400
    }

    @media (max-width: 991.98px) {
      body.home-page header {
        position: relative !important;
        background: var(--home-ink) !important;
      }

      body.home-page header .navbar {
        background: var(--home-ink) !important;
      }

      .home-categories-panel {
        margin-top: -2rem;
        padding: 1.5rem;
      }

      .home-story-content {
        flex-direction: column;
        align-items: flex-start;
        justify-content: flex-end;
        padding: 15%;
      }
    }

    @media (max-width: 575.98px) {
      .home-hero-copy {
        padding-top: 4rem;
      }

      .home-category-card {
        min-height: 180px;
      }
    }
  </style>
@endpush

@section('content')
  @php
    $heroCollection = isset($heroProducts) && $heroProducts instanceof \Illuminate\Support\Collection
      ? $heroProducts
      : collect();
    $heroSlides = collect();
    $categoryCards = isset($homeCategories) && $homeCategories instanceof \Illuminate\Support\Collection
      ? $homeCategories
      : collect();
    $sliderAltText = isset($siteTitle) && filled($siteTitle) ? $siteTitle : config('app.name', 'Tienda');
    $defaultHeroTitle = 'Cada propuesta es un capítulo de nuestra historia.';
    $defaultHeroText = 'Una tienda pensada para descubrir categorías, materiales y recursos con una presencia cálida, artesanal y contemporánea.';
    $defaultHeroButtons = collect([
      ['label' => 'Explorar categorías', 'url' => '#home-categories'],
      ['label' => 'Ver catálogo completo', 'url' => route('category.show', 'todas')],
    ]);

    if (isset($sliderPrincipal) && $sliderPrincipal && $sliderPrincipal->images->isNotEmpty()) {
      $heroSlides = $sliderPrincipal->images
        ->sortBy('orden')
        ->map(function ($image) use ($sliderAltText) {
          $ctaButtons = collect($image->cta_buttons ?? [])
            ->take(5)
            ->map(fn ($button) => [
              'label' => trim((string) ($button['label'] ?? '')),
              'url' => trim((string) ($button['url'] ?? '')),
            ])
            ->filter(fn ($button) => $button['label'] !== '' && $button['url'] !== '')
            ->values()
            ->all();

          return [
            'src' => asset('storage/' . $image->imagen),
            'alt' => $sliderAltText,
            'title' => $image->hero_title,
            'text' => $image->hero_text,
            'cta_buttons' => $ctaButtons,
            'use_default_content' => false,
          ];
        })
        ->values();
    }

    if ($heroSlides->isEmpty() && $heroCollection->isNotEmpty()) {
      $heroSlides = $heroCollection
        ->map(fn ($product) => [
          'src' => $product->featured_image ? asset('storage/' . $product->featured_image) : asset('images/logo.svg'),
          'alt' => $product->name,
          'title' => $defaultHeroTitle,
          'text' => $defaultHeroText,
          'cta_buttons' => [],
          'use_default_content' => true,
        ])
        ->values();
    }

    if ($heroSlides->isEmpty()) {
      $heroSlides = collect([
        ['src' => asset('media/photos/photo1.png'), 'alt' => 'Hilando Culturas', 'title' => $defaultHeroTitle, 'text' => $defaultHeroText, 'cta_buttons' => [], 'use_default_content' => true],
        ['src' => asset('media/photos/photo2.jpg'), 'alt' => 'Hilando Culturas', 'title' => $defaultHeroTitle, 'text' => $defaultHeroText, 'cta_buttons' => [], 'use_default_content' => true],
        ['src' => asset('media/photos/photo3.jpg'), 'alt' => 'Hilando Culturas', 'title' => $defaultHeroTitle, 'text' => $defaultHeroText, 'cta_buttons' => [], 'use_default_content' => true],
      ]);
    }

    $defaultCategoryImage = $heroSlides->first()['src'] ?? asset('media/photos/photo1.png');
  @endphp

  <section class="home-hero-section">
    <div class="home-hero-card">
      <div id="homeHeroCarousel" class="carousel slide home-hero-carousel" data-bs-ride="carousel">
        <div class="carousel-indicators">
          @foreach($heroSlides as $index => $slide)
            <button type="button" data-bs-target="#homeHeroCarousel" data-bs-slide-to="{{ $index }}" class="{{ $index === 0 ? 'active' : '' }}" aria-current="{{ $index === 0 ? 'true' : 'false' }}" aria-label="Slide {{ $index + 1 }}"></button>
          @endforeach
        </div>

        <div class="carousel-inner">
          @foreach($heroSlides as $index => $slide)
            @php
              $useDefaultContent = (bool) ($slide['use_default_content'] ?? false);
              $slideKicker = $useDefaultContent ? 'Hilando Culturas' : null;
              $slideTitle = filled($slide['title'] ?? null) ? $slide['title'] : ($useDefaultContent ? $defaultHeroTitle : null);
              $slideText = filled($slide['text'] ?? null) ? $slide['text'] : ($useDefaultContent ? $defaultHeroText : null);
              $slideButtons = collect($slide['cta_buttons'] ?? [])->take(5)->values();

              if ($useDefaultContent && $slideButtons->isEmpty()) {
                $slideButtons = $defaultHeroButtons;
              }
            @endphp
            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
              <img src="{{ $slide['src'] }}" class="d-block w-100 home-hero-image" alt="{{ $slide['alt'] }}">
              <div class="home-hero-overlay"></div>
              <div class="home-hero-copy">
                <div class="container">
                  <div class="row justify-content-center">
                    <div class="col-xl-8">
                      @if(filled($slideKicker))
                        <p class="home-hero-kicker mb-3">{{ $slideKicker }}</p>
                      @endif
                      @if(filled($slideTitle))
                        <h1 class="home-hero-title mb-4">{{ $slideTitle }}</h1>
                      @endif
                      @if(filled($slideText))
                        <p class="home-hero-text mb-4">{{ $slideText }}</p>
                      @endif
                      @if($slideButtons->isNotEmpty())
                        <div class="home-hero-actions">
                          @foreach($slideButtons as $button)
                            @php
                              $buttonClass = $loop->first ? 'btn btn-outline-light' : 'btn btn-light';
                              $isExternal = \Illuminate\Support\Str::startsWith($button['url'], ['http://', 'https://']);
                            @endphp
                            <a
                              href="{{ $button['url'] }}"
                              class="{{ $buttonClass }}"
                              @if($isExternal) target="_blank" rel="noreferrer" @endif
                            >
                              {{ $button['label'] }}
                            </a>
                          @endforeach
                        </div>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>

        @if($heroSlides->count() > 1)
          <button class="carousel-control-prev" type="button" data-bs-target="#homeHeroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Anterior</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#homeHeroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Siguiente</span>
          </button>
        @endif
      </div>
    </div>
  </section>

  <section class="home-categories-section">
    <div class="home-categories-panel">
      <div class="container">
        {{-- <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
          <div>
            <p class="home-section-kicker mb-2">Categorías</p>
            <h2 class="home-section-title mb-0">Entrá al catálogo por cada universo.</h2>
          </div>
          <a href="{{ route('category.show', 'todas') }}" class="btn btn-outline-dark rounded-pill px-4">Ver todas</a>
        </div> --}}

        @if($categoryCards->isNotEmpty())
          <div id="home-categories" class="row g-4">
            @foreach($categoryCards as $category)
              @php
                $categoryImage = filled($category->image)
                  ? asset('storage/' . $category->image)
                  : $defaultCategoryImage;
              @endphp
              <div class="col-12 col-md-6 col-xl-4">
                <a href="{{ route('category.show', $category->slug) }}" class="home-category-card">
                  <img src="{{ $categoryImage }}" alt="{{ $category->name }}">
                  <span class="home-category-title">{{ $category->name }}</span>
                </a>
              </div>
            @endforeach
          </div>
        @else
          <div class="alert alert-light border rounded-4 mb-0">
            No hay categorías activas con imagen disponibles para mostrar en la portada.
          </div>
        @endif
      </div>
    </div>
  </section>

  <section class="home-story-section">
    <div class="home-story-banner">
      <img src="{{ asset('media/photos/fondo-banner.jpg') }}" alt="Historias con propósito">
      <div class="home-story-content">
        <div class="container">
          <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
            <div>              
              <h2 class="home-story-title mb-0">HISTORIAS CON PROPÓSITO.</h2>
            </div>
            <div>
              <a href="sobre-hilando" class="btn btn-light">Descubrí nuestra historia</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
