@php
  $productImage = $product->featured_image ? asset('storage/' . $product->featured_image) : asset('images/logo.svg');
  $currentCustomer = auth('customer')->user();
  $isWholesalePricing = $product->usesWholesalePricing($currentCustomer);
  $resolvedMinQuantity = $product->resolveMinQuantity($currentCustomer);
  $resolvedPrice = $product->resolveUnitPrice($currentCustomer);
  $isFreeProduct = (float) $resolvedPrice <= 0;
  $hasVariantPricing = false;

  try {
    foreach ($product->attributeValuesGroupedByAttribute() as $attribute) {
      if ($attribute->values->contains(fn ($value) => $value->pivot && ($value->pivot->price !== null || $value->pivot->stock !== null))) {
        $hasVariantPricing = true;
        break;
      }
    }
  } catch (\Throwable $e) {
    $hasVariantPricing = false;
  }

  $isFavorite = collect(session('favorite_product_ids', []))
    ->map(fn ($id) => (int) $id)
    ->contains((int) $product->id);
  $detailActionLabel = $hasVariantPricing ? 'Ver opciones' : 'Ver';
  $primaryActionLabel = $isFreeProduct ? 'Descargar gratis' : 'Comprar';
  $primaryActionDisabled = !$hasVariantPricing && (int) $product->stock < $resolvedMinQuantity;
@endphp

<article class="catalog-product-card h-100 d-flex flex-column">
  <div class="catalog-product-media">
    <a href="{{ route('product.show', $product) }}" class="catalog-product-media-link text-decoration-none">
      <img src="{{ $productImage }}" class="catalog-product-image" alt="{{ $product->name }}">
    </a>

    <button
      type="button"
      class="btn {{ $isFavorite ? 'btn-dark text-white active' : 'btn-outline-secondary' }} favorite-toggle-button catalog-product-favorite catalog-product-favorite-floating js-toggle-favorite"
      data-id="{{ $product->id }}"
      data-active="{{ $isFavorite ? '1' : '0' }}"
      data-active-label="Quitar de favoritos"
      data-inactive-label="Agregar a favoritos"
      aria-label="{{ $isFavorite ? 'Quitar de favoritos' : 'Agregar a favoritos' }}"
      title="{{ $isFavorite ? 'Quitar de favoritos' : 'Agregar a favoritos' }}"
    >
      <img src="{{ asset('media/iconos/favorite.svg') }}" alt="" class="favorite-toggle-icon" aria-hidden="true">
    </button>
  </div>

  <div class="catalog-product-body d-flex flex-column pt-3">
    <h2 class="catalog-product-title h6 mb-2">
      <a href="{{ route('product.show', $product) }}">{{ $product->name }}</a>
    </h2>
    <p class="catalog-product-price mb-3">
      @if($hasVariantPricing && !$isFreeProduct)
        Desde
      @endif
      {{ $isFreeProduct ? 'Gratis' : '$' . number_format((float) $resolvedPrice, 2, ',', '.') }}
    </p>

    <div class="catalog-product-actions {{ $hasVariantPricing ? 'catalog-product-actions--single' : '' }} mt-auto">
      <a href="{{ route('product.show', $product) }}" class="btn btn-outline-secondary">{{ $detailActionLabel }}</a>

      @if(!$hasVariantPricing && $primaryActionDisabled)
        <button type="button" class="btn btn-secondary" disabled>Sin stock</button>
      @elseif(!$hasVariantPricing)
        <button type="button" class="btn btn-primary js-add-to-cart" data-id="{{ $product->id }}" data-quantity="{{ $resolvedMinQuantity }}">
          {{ $primaryActionLabel }}
        </button>
      @endif
    </div>
  </div>
</article>
