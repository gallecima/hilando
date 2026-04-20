@php
  $currentCat = optional($product->categories->first());
  $trail = $currentCat ? $currentCat->ancestors()->push($currentCat) : collect();
@endphp

@extends('layouts.front')

@section('title', $product->name)
@section('body_class', 'page-hero product-page')

@section('content')
  @php
    $breadcrumbs = [['label' => 'Inicio', 'url' => route('home')]];
    foreach ($trail as $cat) {
      $breadcrumbs[] = ['label' => $cat->name, 'url' => route('category.show', $cat->slug)];
    }
    $breadcrumbs[] = ['label' => $product->name];
    $heroCategory = collect([$currentCat])
      ->filter()
      ->merge($trail->reverse())
      ->first(fn ($category) => filled($category->image));
    $heroBackgroundImage = filled($heroCategory?->image)
      ? asset('storage/' . ltrim((string) $heroCategory->image, '/'))
      : ($product->featured_image ? asset('storage/' . $product->featured_image) : null);

    $galleryImages = collect();
    if ($product->featured_image) {
      $galleryImages->push([
        'src' => asset('storage/' . $product->featured_image),
        'alt' => $product->name,
      ]);
    }
    foreach ($product->images as $image) {
      $galleryImages->push([
        'src' => asset('storage/' . $image->path),
        'alt' => $product->name,
      ]);
    }
    if ($galleryImages->isEmpty()) {
      $galleryImages->push([
        'src' => asset('images/logo.svg'),
        'alt' => $product->name,
      ]);
    }

    $currentCustomer = auth('customer')->user();
    $isWholesalePricing = $product->usesWholesalePricing($currentCustomer);
    $resolvedPrice = $product->resolveUnitPrice($currentCustomer);
    $resolvedMinQuantity = $product->is_digital ? 1 : $product->resolveMinQuantity($currentCustomer);
    $hasVariantPricing = false;
    $staticAttributes = collect();
    $variantAttributes = collect();

    try {
      foreach ($product->attributeValuesGroupedByAttribute() as $attribute) {
        $isVariantAttribute = $attribute->values->contains(function ($value) {
          return $value->pivot && ($value->pivot->price !== null || $value->pivot->stock !== null);
        });

        if ($attribute->values->contains(fn ($value) => $value->pivot && $value->pivot->price !== null)) {
          $hasVariantPricing = true;
        }

        if ($isVariantAttribute) {
          $variantAttributes->push($attribute);
        } else {
          $staticAttributes->push($attribute);
        }
      }
    } catch (\Throwable $e) {
      $hasVariantPricing = false;
      $staticAttributes = collect();
      $variantAttributes = collect();
    }

    $isFreeProduct = (float) $resolvedPrice <= 0;
    $isFavorite = collect(session('favorite_product_ids', []))
      ->map(fn ($id) => (int) $id)
      ->contains((int) $product->id);
    $categoryDisplayName = $currentCat?->name ?: ($product->categories->first()?->name ?: 'Sin categoría');
    $skuDisplay = $product->sku ?: 'Sin SKU';
    $summaryText = trim((string) ($product->short_description ?? ''));
    if ($summaryText === '') {
      $summaryText = \Illuminate\Support\Str::limit(trim(strip_tags((string) ($product->description ?? ''))), 170);
    }
  @endphp

  @include('front.partials.page-header', [
    'variant' => 'hero',
    // 'eyebrow' => 'Categoría',
    // 'title' => $currentCat?->name ?: 'Producto',
    // 'subtitle' => $product->name,
    'breadcrumbs' => $breadcrumbs,
    'backgroundImage' => $heroBackgroundImage,
  ])

  <section class="product-showcase-section pb-5">
    <div class="container">
      @php
        $tribunoMeta = $hasVariantPricing && ! $isWholesalePricing
          ? app(\App\Support\Hooks::class)->render('front:product:detail.discount.meta', $product)
          : '';
      @endphp

      <div class="product-showcase-shell">
        <div class="row g-4 g-xl-5 align-items-center">
          <div class="col-lg-6">
            <div class="product-showcase-media">
              @if($galleryImages->count() > 1)
                <div id="productGalleryCarousel" class="carousel slide product-gallery-carousel" data-bs-touch="true">
                  <div class="carousel-indicators product-gallery-indicators">
                    @foreach($galleryImages as $index => $image)
                      <button type="button" data-bs-target="#productGalleryCarousel" data-bs-slide-to="{{ $index }}" class="{{ $index === 0 ? 'active' : '' }}" aria-current="{{ $index === 0 ? 'true' : 'false' }}" aria-label="Imagen {{ $index + 1 }}"></button>
                    @endforeach
                  </div>
                  <div class="carousel-inner">
                    @foreach($galleryImages as $index => $image)
                      <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                        <img src="{{ $image['src'] }}" class="d-block w-100 product-gallery-image" alt="{{ $image['alt'] }}">
                      </div>
                    @endforeach
                  </div>
                  <button class="carousel-control-prev product-gallery-control" type="button" data-bs-target="#productGalleryCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Anterior</span>
                  </button>
                  <button class="carousel-control-next product-gallery-control" type="button" data-bs-target="#productGalleryCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Siguiente</span>
                  </button>
                </div>
              @else
                <img src="{{ $galleryImages->first()['src'] }}" class="d-block w-100 product-gallery-image" alt="{{ $galleryImages->first()['alt'] }}">
              @endif
            </div>
          </div>

          <div class="col-lg-6">
            <form
              id="product-add-to-cart-form"
              method="POST"
              action="{{ route('cart.add') }}"
              class="product-showcase-form"
              data-unit-price="{{ number_format((float) $resolvedPrice, 2, '.', '') }}"
              data-minimum-quantity="{{ $resolvedMinQuantity }}"
              data-wholesale-pricing="{{ $isWholesalePricing ? '1' : '0' }}"
            >
              @csrf
              <input type="hidden" name="product_id" value="{{ $product->id }}">

              @if(trim($tribunoMeta) !== '')
                {!! $tribunoMeta !!}
              @else
                <div id="tribuno-meta" class="d-none" data-tribuno-pct="0" data-tribuno-active="0"></div>
              @endif

              <div id="variant-price-box" class="product-showcase-price-box">
                @if($hasVariantPricing && !$isWholesalePricing)
                  {{-- <p class="product-showcase-price-kicker mb-2">Precio</p> --}}
                  <p class="product-showcase-price-value is-placeholder mb-2">Seleccioná una variante</p>
                  <p class="product-showcase-price-note mb-0">Elegí una opción para ver el precio final.</p>
                @else
                  @if(!$isWholesalePricing && !$isFreeProduct && $product->has_discount_price)
                    <p class="product-showcase-price-compare mb-2">${{ number_format((float) $product->base_price, 2, ',', '.') }}</p>
                  @endif
                  <p class="product-showcase-price-value mb-2">{{ $isFreeProduct ? 'Gratis' : '$ ' . number_format((float) $resolvedPrice, 2, ',', '.') }}</p>
                  @if($isWholesalePricing || $resolvedMinQuantity > 1)
                    <p class="product-showcase-price-note mb-0">
                      {{ $isWholesalePricing ? 'Precio mayorista activo.' : 'Compra mínima requerida.' }}
                      @if($resolvedMinQuantity > 1)
                        Mínimo {{ $resolvedMinQuantity }} {{ \Illuminate\Support\Str::plural('unidad', $resolvedMinQuantity) }}.
                      @endif
                    </p>
                  @endif
                @endif
              </div>

              <div class="product-showcase-copy">
                <h2 class="product-showcase-title mb-3">{{ $product->name }}</h2>
                @if($summaryText !== '')
                  <p class="product-showcase-summary mb-0">{{ $summaryText }}</p>
                @endif
              </div>

              <div class="row row-cols-2 gx-4 gy-3 product-showcase-meta">
                <div class="col">
                  <span class="product-showcase-meta-label">SKU</span>
                  <span class="product-showcase-meta-value">{{ $skuDisplay }}</span>
                </div>
                <div class="col">
                  <span class="product-showcase-meta-label">Categoría</span>
                  <span class="product-showcase-meta-value">{{ $categoryDisplayName }}</span>
                </div>
              </div>

              @foreach($variantAttributes as $attribute)
                @php
                  $usesColorSwatches = $attribute->values->isNotEmpty()
                    && $attribute->values->every(fn ($value) => $value->isHexColor());
                  $defaultVariantValue = $attribute->values->first(function ($value) use ($product) {
                    $pivot = $value->pivot ?? null;
                    $stock = $pivot && $pivot->stock !== null ? (int) $pivot->stock : (int) $product->stock;
                    return $stock > 0;
                  });
                @endphp
                <div class="product-showcase-option-block">
                  <label class="product-showcase-option-label d-block">{{ $attribute->name }}</label>
                  @if($usesColorSwatches)
                    <div class="attribute-swatch-group product-showcase-swatches">
                      @foreach($attribute->values as $value)
                        @php
                          $pivot = $value->pivot ?? null;
                          $price = $pivot && $pivot->price !== null ? $pivot->price : $product->price;
                          $stock = $pivot && $pivot->stock !== null ? $pivot->stock : $product->stock;
                          $hexColor = $value->hex_color;
                          $hexRgb = ltrim((string) $hexColor, '#');
                          $red = hexdec(substr($hexRgb, 0, 2));
                          $green = hexdec(substr($hexRgb, 2, 2));
                          $blue = hexdec(substr($hexRgb, 4, 2));
                          $luminance = ((0.299 * $red) + (0.587 * $green) + (0.114 * $blue)) / 255;
                          $checkColor = $luminance > 0.68 ? '#102228' : '#FFFFFF';
                        @endphp
                        <label class="attribute-swatch-option {{ $stock < 1 ? 'is-disabled' : '' }}" for="attribute-{{ $attribute->id }}-{{ $value->id }}">
                          <input
                            class="variant-radio attribute-swatch-input"
                            type="radio"
                            name="attributes[{{ $attribute->id }}]"
                            value="{{ $value->id }}"
                            id="attribute-{{ $attribute->id }}-{{ $value->id }}"
                            data-stock="{{ $stock }}"
                            data-price="{{ number_format((float) $price, 2, '.', '') }}"
                            {{ $stock < 1 ? 'disabled' : '' }}
                            {{ $defaultVariantValue && $defaultVariantValue->id === $value->id ? 'checked' : '' }}
                            required
                          >
                          <span class="attribute-swatch-chip" title="{{ $value->value }}{{ $stock < 1 ? ' · Sin stock' : '' }}">
                            <span class="attribute-swatch-dot" style="background-color: {{ $hexColor }};">
                              <span class="attribute-swatch-check" style="color: {{ $checkColor }};">&#10003;</span>
                            </span>
                          </span>
                          <span class="visually-hidden">{{ $attribute->name }}: {{ $value->value }}</span>
                        </label>
                      @endforeach
                    </div>
                  @else
                    <div class="product-option-pills">
                      @foreach($attribute->values as $value)
                        @php
                          $pivot = $value->pivot ?? null;
                          $price = $pivot && $pivot->price !== null ? $pivot->price : $product->price;
                          $stock = $pivot && $pivot->stock !== null ? $pivot->stock : $product->stock;
                          $hexColor = $value->hex_color;
                        @endphp
                        <label class="product-option-pill {{ $stock < 1 ? 'is-disabled' : '' }}" for="attribute-{{ $attribute->id }}-{{ $value->id }}">
                          <input
                            class="variant-radio product-option-input"
                            type="radio"
                            name="attributes[{{ $attribute->id }}]"
                            value="{{ $value->id }}"
                            id="attribute-{{ $attribute->id }}-{{ $value->id }}"
                            data-stock="{{ $stock }}"
                            data-price="{{ number_format((float) $price, 2, '.', '') }}"
                            {{ $stock < 1 ? 'disabled' : '' }}
                            {{ $defaultVariantValue && $defaultVariantValue->id === $value->id ? 'checked' : '' }}
                            required
                          >
                          <span class="product-option-pill-label">
                            @if($hexColor)
                              <span class="attribute-inline-swatch me-2" style="background-color: {{ $hexColor }};" aria-hidden="true"></span>
                            @endif
                            {{ $value->value }}
                          </span>
                          <span class="product-option-pill-meta">{{ $stock < 1 ? 'Sin stock' : 'Stock ' . $stock }}</span>
                        </label>
                      @endforeach
                    </div>
                  @endif
                </div>
              @endforeach

              @if($product->is_digital)
                <input type="hidden" name="quantity" id="quantity-input" value="1">
              @else
                <div class="product-showcase-quantity">
                  <label for="quantity-input" class="product-showcase-option-label d-block">Cantidad</label>
                  <input
                    type="number"
                    name="quantity"
                    id="quantity-input"
                    class="form-control product-showcase-quantity-input"
                    value="{{ $resolvedMinQuantity }}"
                    min="{{ $resolvedMinQuantity }}"
                    max="{{ max((int) $product->stock, 1) }}"
                  >
                  @if($resolvedMinQuantity > 1)
                    <div class="form-text">Cantidad mínima para este acceso: {{ $resolvedMinQuantity }} {{ \Illuminate\Support\Str::plural('unidad', $resolvedMinQuantity) }}.</div>
                  @endif
                </div>
              @endif

              <div class="product-showcase-actions">
                <button
                  type="button"
                  class="btn btn-secondary rounded-pill"
                  id="buy-now-btn"
                  data-default-label="Comprar"
                  data-free-label="Descargar gratis"
                  {{ $variantAttributes->isNotEmpty() || (!$product->is_digital && (int) $product->stock < $resolvedMinQuantity) ? 'disabled' : '' }}
                >
                  {{ $isFreeProduct ? 'Descargar gratis' : 'Comprar' }}
                </button>
                <button
                  type="submit"
                  class="btn btn-outline-secondary rounded-pill"
                  id="add-to-cart-btn"
                  data-default-label="Añadir al carrito"
                  data-free-label="Descargar gratis"
                  {{ $variantAttributes->isNotEmpty() || (!$product->is_digital && (int) $product->stock < $resolvedMinQuantity) ? 'disabled' : '' }}
                >
                  {{ $isFreeProduct ? 'Descargar gratis' : 'Añadir al carrito' }}
                </button>

                <button
                  type="button"
                  class="btn btn-outline-secondary rounded-pill favorite-toggle-button product-showcase-favorite js-toggle-favorite {{ $isFavorite ? 'active' : '' }}"
                  data-id="{{ $product->id }}"
                  data-active="{{ $isFavorite ? '1' : '0' }}"
                  data-active-label="Quitar de favoritos"
                  data-inactive-label="Agregar a favoritos"
                  aria-label="{{ $isFavorite ? 'Quitar de favoritos' : 'Agregar a favoritos' }}"
                >
                  <img src="{{ asset('media/iconos/favorite.svg') }}" alt="" class="favorite-toggle-icon" aria-hidden="true">
                </button>
              </div>

              <div class="product-showcase-status">
                <p class="small text-body-secondary mb-0" id="stock-info">
                  Stock disponible: <span id="stock-available">{{ $product->stock }}</span>
                </p>

                @if($isWholesalePricing)
                  <p class="small text-body-secondary mb-0">Estás navegando con acceso mayorista.</p>
                @endif

                {{-- <a href="{{ route('category.show', 'todas') }}" class="product-showcase-back-link">Ver catálogo completo</a> --}}
              </div>

              @if(!$product->is_digital)
                <div class="product-showcase-shipping">
                  {!! app(\App\Support\Hooks::class)->render('front:product:detail.shipping.widget', $product) !!}
                </div>
              @endif
            </form>
          </div>
        </div>
      </div>

      @if($product->description || $staticAttributes->isNotEmpty())
        <div class="row g-4 pt-4">
          @if($product->description)
            <div class="{{ $staticAttributes->isNotEmpty() ? 'col-lg-7' : 'col-12' }}">
              <div class="card border-0 shadow-sm rounded-4 product-detail-panel">
                <div class="card-body p-4 p-lg-5">
                  <h2 class="h4 mb-3">Descripción</h2>
                  <div class="text-body-secondary">
                    {!! nl2br(e($product->description)) !!}
                  </div>
                </div>
              </div>
            </div>
          @endif

          @if($staticAttributes->isNotEmpty())
            <div class="{{ $product->description ? 'col-lg-5' : 'col-12' }}">
              <div class="card border-0 shadow-sm rounded-4 product-detail-panel">
                <div class="card-body p-4 p-lg-5">
                  <h2 class="h4 mb-3">Características</h2>
                  <div class="table-responsive">
                    <table class="table align-middle mb-0 product-detail-table">
                      <tbody>
                        @foreach($staticAttributes as $attribute)
                          <tr>
                            <th scope="row">{{ $attribute->name }}</th>
                            <td>
                              @php
                                $staticUsesColorSwatches = $attribute->values->isNotEmpty()
                                  && $attribute->values->every(fn ($value) => $value->isHexColor());
                              @endphp
                              @if($staticUsesColorSwatches)
                                <div class="attribute-swatch-inline">
                                  @foreach($attribute->values as $value)
                                    <span
                                      class="attribute-inline-swatch attribute-inline-swatch-lg"
                                      style="background-color: {{ $value->hex_color }};"
                                      title="{{ $value->value }}"
                                      aria-hidden="true"
                                    ></span>
                                    <span class="visually-hidden">{{ $value->value }}</span>
                                  @endforeach
                                </div>
                              @else
                                {{ $attribute->values->pluck('value')->implode(', ') }}
                              @endif
                            </td>
                          </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          @endif
        </div>
      @endif
    </div>
  </section>

  @if($otherProducts->isNotEmpty())
    <section class="pb-5">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h2 class="h3 mb-0">Productos relacionados</h2>
          {{-- <a href="{{ route('category.show', 'todas') }}" class="btn btn-outline-primary">Ver catálogo completo</a> --}}
        </div>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-4">
          @foreach($otherProducts as $otherProduct)
            <div class="col">
              @include('front.partials.product-card', ['product' => $otherProduct, 'showCategories' => true])
            </div>
          @endforeach
        </div>
      </div>
    </section>
  @endif
@endsection

@section('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const form = document.getElementById('product-add-to-cart-form');
      if (!form) return;

      const quantityInput = document.getElementById('quantity-input');
      const stockAvailable = document.getElementById('stock-available');
      const addButton = document.getElementById('add-to-cart-btn');
      const buyNowButton = document.getElementById('buy-now-btn');
      const priceBox = document.getElementById('variant-price-box');
      const meta = document.getElementById('tribuno-meta');
      const minimumQuantity = parseInt(form.dataset.minimumQuantity || '1', 10);
      const resolvedUnitPrice = parseFloat(form.dataset.unitPrice || '0');
      const useWholesalePricing = form.dataset.wholesalePricing === '1';
      const discountPct = parseFloat(meta?.dataset.tribunoPct || '0');
      const hasActiveBenefit = meta?.dataset.tribunoActive === '1';
      const variantRadios = Array.from(document.querySelectorAll('.variant-radio'));
      const variantGroupNames = Array.from(new Set(variantRadios.map(function (radio) {
        return radio.name;
      })));
      const checkoutUrl = @json(route('front.checkout.index'));
      const initialStockDisabled = {{ (!$product->is_digital && (int) $product->stock < $resolvedMinQuantity) ? 'true' : 'false' }};

      function formatCurrency(value) {
        return new Intl.NumberFormat('es-AR', {
          minimumFractionDigits: Number.isInteger(value) ? 0 : 2,
          maximumFractionDigits: 2
        }).format(value);
      }

      function setActionButtonsDisabled(disabled) {
        [addButton, buyNowButton].forEach(function (button) {
          if (button) button.disabled = disabled;
        });
      }

      function getSelectedVariantRadios() {
        return Array.from(form.querySelectorAll('.variant-radio:checked'));
      }

      function getPrimarySelectedVariant() {
        const selected = getSelectedVariantRadios();
        return selected.length ? selected[selected.length - 1] : null;
      }

      function setButtonLabel(isFree) {
        if (addButton) {
          addButton.textContent = isFree
            ? (addButton.dataset.freeLabel || 'Descargar gratis')
            : (addButton.dataset.defaultLabel || 'Añadir al carrito');
        }

        if (buyNowButton) {
          buyNowButton.textContent = isFree
            ? (buyNowButton.dataset.freeLabel || 'Descargar gratis')
            : (buyNowButton.dataset.defaultLabel || 'Comprar');
        }
      }

      function buildMinimumNote(prefix) {
        const parts = [];
        if (prefix) parts.push(prefix);
        if (minimumQuantity > 1) {
          parts.push('Mínimo ' + minimumQuantity + ' unidades.');
        }
        return parts.join(' ').trim();
      }

      function renderPriceBox(options) {
        if (!priceBox) return;

        const kicker = options.kicker || 'Precio';
        const value = options.value || '';
        const note = options.note || '';
        const compare = options.compare || '';
        const placeholderClass = options.placeholder ? ' is-placeholder' : '';

        priceBox.innerHTML =
          // '<p class="product-showcase-price-kicker mb-2">' + kicker + '</p>' +
          (compare ? '<p class="product-showcase-price-compare mb-2">' + compare + '</p>' : '') +
          '<p class="product-showcase-price-value' + placeholderClass + ' mb-2">' + value + '</p>' +
          (note ? '<p class="product-showcase-price-note mb-0">' + note + '</p>' : '');
      }

      function renderPendingVariantPrice() {
        renderPriceBox({
          kicker: 'Precio',
          value: 'Seleccioná una variante',
          note: 'Elegí una opción para ver el precio final.',
          placeholder: true
        });
        setButtonLabel(false);
      }

      function renderPrice(price) {
        if (!priceBox || Number.isNaN(price)) return;

        if (price <= 0) {
          renderPriceBox({
            kicker: 'Precio',
            value: 'Gratis',
            note: buildMinimumNote('')
          });
          setButtonLabel(true);
          return;
        }

        if (useWholesalePricing) {
          renderPriceBox({
            kicker: 'Precio mayorista',
            value: '$ ' + formatCurrency(price),
            note: buildMinimumNote('Acceso mayorista activo.')
          });
          setButtonLabel(false);
          return;
        }

        if (discountPct > 0) {
          const discountAmount = +(price * (discountPct / 100)).toFixed(2);
          const finalPrice = Math.max(price - discountAmount, 0);

          if (hasActiveBenefit) {
            renderPriceBox({
              kicker: 'Precio final',
              compare: '$ ' + formatCurrency(price),
              value: finalPrice <= 0 ? 'Gratis' : '$ ' + formatCurrency(finalPrice),
              note: buildMinimumNote('Descuento activo: ' + discountPct + '%.')
            });
            setButtonLabel(finalPrice <= 0);
          } else {
            renderPriceBox({
              kicker: 'Precio',
              value: '$ ' + formatCurrency(price),
              note: buildMinimumNote('Con el beneficio activo pagarías $ ' + formatCurrency(finalPrice) + '.')
            });
            setButtonLabel(false);
          }
        } else {
          renderPriceBox({
            kicker: 'Precio',
            value: '$ ' + formatCurrency(price),
            note: buildMinimumNote('')
          });
          setButtonLabel(false);
        }
      }

      function updateFromVariant(radio) {
        const stock = parseInt(radio.dataset.stock || '0', 10);
        const price = useWholesalePricing
          ? resolvedUnitPrice
          : parseFloat(radio.dataset.price || '0');

        if (quantityInput) {
          quantityInput.min = String(minimumQuantity);
          quantityInput.max = String(Math.max(stock, 1));

          if (parseInt(quantityInput.value || String(minimumQuantity), 10) < minimumQuantity) {
            quantityInput.value = String(minimumQuantity);
          }

          if (stock > 0 && parseInt(quantityInput.value || String(minimumQuantity), 10) > stock) {
            quantityInput.value = String(Math.max(minimumQuantity, stock));
          }
        }

        if (stockAvailable) {
          stockAvailable.textContent = String(stock);
        }

        renderPrice(price);
        setActionButtonsDisabled(stock < minimumQuantity);
      }

      function syncActionButtons() {
        if (variantRadios.length > 0) {
          const selectedRadios = getSelectedVariantRadios();
          const allGroupsSelected = selectedRadios.length === variantGroupNames.length;
          const hasUnavailableSelection = selectedRadios.some(function (radio) {
            return parseInt(radio.dataset.stock || '0', 10) < minimumQuantity;
          });

          setActionButtonsDisabled(!allGroupsSelected || hasUnavailableSelection);
          return;
        }

        setActionButtonsDisabled(initialStockDisabled);
      }

      function submitProduct(mode) {
        if (!form.reportValidity()) {
          return;
        }

        if (quantityInput) {
          const max = parseInt(quantityInput.max || '1', 10);
          const min = parseInt(quantityInput.min || String(minimumQuantity), 10);
          const current = parseInt(quantityInput.value || String(minimumQuantity), 10);

          if (current < min || current > max) {
            alert('La cantidad seleccionada no es válida para este acceso.');
            return;
          }
        }

        const formData = new FormData(form);
        setActionButtonsDisabled(true);

        fetch(@json(route('cart.add')), {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          },
          body: formData
        })
          .then(async function (response) {
            const payload = await response.json().catch(function () { return {}; });
            if (!response.ok || payload.success !== true) {
              throw new Error(payload.message || 'No se pudo agregar el producto al carrito.');
            }
            return payload;
          })
          .then(function () {
            window.frontStore?.updateCartBadge?.();

            if (mode === 'checkout') {
              window.location.href = checkoutUrl;
              return;
            }

            window.frontStore?.openCart?.();
          })
          .catch(function (error) {
            alert(error.message || 'No se pudo agregar el producto al carrito.');
          })
          .finally(function () {
            syncActionButtons();
          });
      }

      variantRadios.forEach(function (radio) {
        radio.addEventListener('change', function () {
          updateFromVariant(radio);
        });
      });

      form.addEventListener('submit', function (event) {
        event.preventDefault();
        submitProduct('cart');
      });

      if (buyNowButton) {
        buyNowButton.addEventListener('click', function (event) {
          event.preventDefault();
          submitProduct('checkout');
        });
      }

      const initialSelectedVariant = getPrimarySelectedVariant();

      if (variantRadios.length > 0 && !initialSelectedVariant && !useWholesalePricing) {
        renderPendingVariantPrice();
      } else {
        renderPrice(initialSelectedVariant && !useWholesalePricing
          ? parseFloat(initialSelectedVariant.dataset.price || String(resolvedUnitPrice))
          : resolvedUnitPrice);

        if (initialSelectedVariant) {
          updateFromVariant(initialSelectedVariant);
        }
      }

      syncActionButtons();
    });
  </script>
  {!! app(\App\Support\Hooks::class)->render('front:product:detail.shipping.scripts', $product) !!}
@endsection
