@php
  $title = $title ?? '';
  $subtitle = $subtitle ?? null;
  $breadcrumbs = $breadcrumbs ?? [];
  $variant = $variant ?? 'default';
  $backgroundImage = $backgroundImage ?? null;
  $eyebrow = $eyebrow ?? null;
  $heroId = $heroId ?? 'pageHeaderHeroCarousel';
  $slides = isset($slides) && $slides instanceof \Illuminate\Support\Collection
    ? $slides->values()
    : collect($slides ?? [])->values();
  $heroStyle = $backgroundImage
    ? "--page-header-bg-image: url('{$backgroundImage}');"
    : "--page-header-bg-image: none;";
@endphp

@if($variant === 'hero')
  <section class="page-header-hero {{ $slides->isNotEmpty() ? 'page-header-hero-slider' : '' }} mb-4" @if($slides->isEmpty() && $heroStyle) style="{{ $heroStyle }}" @endif>
    @if($slides->isNotEmpty())
      <div id="{{ $heroId }}" class="carousel slide page-header-hero-carousel" data-bs-ride="{{ $slides->count() > 1 ? 'carousel' : 'false' }}" data-bs-interval="5200">
        <div class="carousel-inner">
          @foreach($slides as $index => $slide)
            @php
              $slideSrc = trim((string) ($slide['src'] ?? ''));
              $slideStyle = $slideSrc !== '' ? "--page-header-bg-image: url('{$slideSrc}');" : "--page-header-bg-image: none;";
            @endphp
            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
              <div class="page-header-hero-slide" style="{{ $slideStyle }}"></div>
            </div>
          @endforeach
        </div>
      </div>
    @endif

    <div class="container page-header-hero-inner">
      @if(!empty($breadcrumbs))
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb page-header-hero-breadcrumb mb-3">
            @foreach($breadcrumbs as $breadcrumb)
              @php
                $crumbLabel = $breadcrumb['label'] ?? '';
                $crumbUrl = $breadcrumb['url'] ?? null;
              @endphp
              @if($crumbUrl)
                <li class="breadcrumb-item"><a href="{{ $crumbUrl }}">{{ $crumbLabel }}</a></li>
              @else
                <li class="breadcrumb-item active" aria-current="page">{{ $crumbLabel }}</li>
              @endif
            @endforeach
          </ol>
        </nav>
      @endif

      @if($eyebrow)
        <p class="page-header-hero-eyebrow mb-3">{{ $eyebrow }}</p>
      @endif

      <h1 class="page-header-hero-title mb-0">{{ $title }}</h1>

      @if($subtitle)
        <p class="page-header-hero-subtitle mb-0 mt-3">{{ $subtitle }}</p>
      @endif
    </div>
  </section>
@else
  <section class="bg-light border-bottom mb-4">
    <div class="container py-4">
      @if(!empty($breadcrumbs))
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-2">
            @foreach($breadcrumbs as $breadcrumb)
              @php
                $crumbLabel = $breadcrumb['label'] ?? '';
                $crumbUrl = $breadcrumb['url'] ?? null;
              @endphp
              @if($crumbUrl)
                <li class="breadcrumb-item"><a href="{{ $crumbUrl }}">{{ $crumbLabel }}</a></li>
              @else
                <li class="breadcrumb-item active" aria-current="page">{{ $crumbLabel }}</li>
              @endif
            @endforeach
          </ol>
        </nav>
      @endif

      <h1 class="h2 mb-0">{{ $title }}</h1>
      @if($subtitle)
        <p class="font-highlight text-body-secondary mb-0 mt-2">{{ $subtitle }}</p>
      @endif
    </div>
  </section>
@endif
