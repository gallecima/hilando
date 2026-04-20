@if ($favorites->isEmpty())
  <div class="alert alert-secondary mb-0" role="alert">
    Todavía no guardaste productos favoritos.
  </div>
@else
  <div class="d-grid gap-3">
    @foreach ($favorites as $product)
      @php
        $productImage = $product->featured_image ? asset('storage/' . $product->featured_image) : asset('images/logo.svg');
        $currentCustomer = auth('customer')->user();
        $resolvedPrice = $product->resolveUnitPrice($currentCustomer);
        $resolvedMinQuantity = $product->resolveMinQuantity($currentCustomer);
        $isWholesalePricing = $product->usesWholesalePricing($currentCustomer);
        $isFreeProduct = (float) $resolvedPrice <= 0;
        $hasVariantOptions = false;

        try {
          foreach ($product->attributeValuesGroupedByAttribute() as $attribute) {
            if ($attribute->values->contains(fn ($value) => $value->pivot && ($value->pivot->price !== null || $value->pivot->stock !== null))) {
              $hasVariantOptions = true;
              break;
            }
          }
        } catch (\Throwable $e) {
          $hasVariantOptions = false;
        }
      @endphp
      <div class="border-bottom pb-3">
        <div class="d-flex gap-3">
          <a href="{{ route('product.show', $product) }}" class="text-decoration-none">
            <img src="{{ $productImage }}" width="72" height="72" class="rounded object-fit-cover" alt="{{ $product->name }}">
          </a>

          <div class="flex-grow-1">
            <a href="{{ route('product.show', $product) }}" class="text-decoration-none text-dark fw-semibold d-block mb-1">
              {{ $product->name }}
            </a>
            <div class="small text-body-secondary mb-2">
              {{ \Illuminate\Support\Str::limit(strip_tags((string) ($product->short_description ?: $product->description ?: 'Descubrí este producto en nuestro catálogo.')), 90) }}
            </div>
            <div class="small fw-semibold mb-1">
              {{ $isFreeProduct ? 'Gratis' : '$' . number_format((float) $resolvedPrice, 2, ',', '.') }}
            </div>
            @if($isWholesalePricing || $resolvedMinQuantity > 1)
              <div class="small text-body-secondary mb-2">
                {{ $isWholesalePricing ? 'Precio mayorista' : 'Compra mínima' }}
                @if($resolvedMinQuantity > 1)
                  · mínimo {{ $resolvedMinQuantity }} {{ \Illuminate\Support\Str::plural('unidad', $resolvedMinQuantity) }}
                @endif
              </div>
            @endif

            <div class="d-flex flex-wrap gap-2">
              <a href="{{ route('product.show', $product) }}" class="btn btn-outline-secondary btn-sm">Ver detalle</a>
              @if(!$hasVariantOptions && (int) $product->stock >= $resolvedMinQuantity)
                <button type="button" class="btn btn-primary btn-sm js-add-to-cart" data-id="{{ $product->id }}" data-quantity="{{ $resolvedMinQuantity }}">
                  {{ $isFreeProduct ? 'Descargar gratis' : 'Agregar al carrito' }}
                </button>
              @endif
            </div>
          </div>

          <button
            type="button"
            class="btn btn-sm btn-outline-secondary favorite-toggle-button js-toggle-favorite active"
            data-id="{{ $product->id }}"
            data-active="1"
            data-active-label="Quitar de favoritos"
            data-inactive-label="Agregar a favoritos"
            aria-label="Quitar de favoritos"
          >
            <img src="{{ asset('media/iconos/favorite.svg') }}" alt="" class="favorite-toggle-icon" aria-hidden="true">
          </button>
        </div>
      </div>
    @endforeach
  </div>
@endif
