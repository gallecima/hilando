@extends('layouts.front')

@section('title', 'Contacto')
@section('body_class', 'page-hero contact-page')

@section('content')
  @php
    $contactEmail = trim((string) ($siteInfo?->support_email ?? 'info@hilandoculturas.com'));
    $contactAddress = trim((string) ($siteInfo?->company_address ?? 'Salta, Argentina'));
    $contactWebsite = trim((string) ($siteInfo?->company_website ?? url('/')));
    $mapQuery = $contactAddress !== '' ? $contactAddress : 'Salta, Argentina';
    $mapEmbedUrl = 'https://maps.google.com/maps?q=' . urlencode($mapQuery) . '&t=&z=14&ie=UTF8&iwloc=&output=embed';
  @endphp

  @include('front.partials.page-header', [
    'variant' => 'hero',
    'title' => 'Contacto',
    'breadcrumbs' => [
      ['label' => 'Inicio', 'url' => route('home')],
      ['label' => 'Contacto'],
    ],
    'slides' => $contactHeroSlides ?? collect(),
    'backgroundImage' => $contactHeroBackgroundImage ?? null,
    'heroId' => 'contactHeroCarousel',
  ])

  <section class="checkout-flow-section contact-page-section">
    <div class="checkout-flow-shell">
      <div class="container">
        <div class="contact-section-grid">
          <div class="contact-section-copy">
            <div class="contact-copy-intro">
              <h2 class="contact-copy-title mb-4">Historias con propósito</h2>
              <p class="product-showcase-summary mb-4">
                Trabajamos con artesanas y comunidades del norte argentino, elaborando piezas con fibras naturales
                mediante un proceso tradicional que cuidamos de punta a punta.
              </p>
              <p class="product-showcase-summary mb-0">
                Podemos contar la historia que siempre soñaste.
              </p>
            </div>

            <div class="contact-form-card">
              <form method="POST" action="{{ route('contact.submit') }}" class="contact-form">
                @csrf

                <div class="mb-3">
                  <label for="contact_email" class="form-label">Email</label>
                  <input
                    type="email"
                    name="email"
                    id="contact_email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}"
                    placeholder="tucorreo@gmail.com"
                    required
                  >
                  @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <div class="mb-4">
                  <label for="contact_message" class="form-label">Mensaje</label>
                  <textarea
                    name="message"
                    id="contact_message"
                    class="form-control @error('message') is-invalid @enderror"
                    rows="6"
                    placeholder="Contanos en qué podemos ayudarte"
                    required
                  >{{ old('message') }}</textarea>
                  @error('message')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>

                <button type="submit" class="btn btn-secondary w-100">Enviar solicitud de contacto</button>
              </form>
            </div>
          </div>

          <div class="contact-section-map">
            <div class="contact-map-frame">
              <iframe
                src="{{ $mapEmbedUrl }}"
                class="contact-map-iframe"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                title="Mapa de ubicación"
              ></iframe>

              <div class="contact-map-card">
                <h3 class="contact-map-address mb-2">{{ $contactAddress !== '' ? $contactAddress : 'Salta, Argentina' }}</h3>
                <p class="contact-map-meta mb-0">
                  <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>
                </p>
              </div>
            </div>

            <div class="contact-meta-list">
              <div class="contact-meta-item">
                <span class="contact-meta-label">Email</span>
                <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>
              </div>
              <div class="contact-meta-item">
                <span class="contact-meta-label">Dirección</span>
                <span>{{ $contactAddress !== '' ? $contactAddress : 'Salta, Argentina' }}</span>
              </div>
              <div class="contact-meta-item">
                <span class="contact-meta-label">Sitio</span>
                <a href="{{ $contactWebsite }}" target="_blank" rel="noreferrer">{{ parse_url($contactWebsite, PHP_URL_HOST) ?: $contactWebsite }}</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
