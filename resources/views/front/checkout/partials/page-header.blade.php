@php
  $heroSlides = isset($checkoutHeroSlides) && $checkoutHeroSlides instanceof \Illuminate\Support\Collection
    ? $checkoutHeroSlides
    : collect($checkoutHeroSlides ?? []);
@endphp

@include('front.partials.page-header', [
  // 'title' => $title ?? 'Checkout',
  'subtitle' => $subtitle ?? null,
  'breadcrumbs' => $breadcrumbs ?? [],
  'variant' => 'hero',
  // 'eyebrow' => $eyebrow ?? 'Checkout',
  'slides' => $heroSlides,
  'backgroundImage' => $checkoutHeroBackgroundImage ?? ($heroSlides->first()['src'] ?? null),
  'heroId' => $heroId ?? 'checkoutHeroCarousel',
])
