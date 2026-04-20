@php
  $selectedValues = $selectedValues ?? [];
  $attributeMeta = $attributeMeta ?? [];
@endphp



@foreach($attributes as $attribute)
  <div class="mb-3">
    <label class="form-label">{{ $attribute->name }}</label>
    <div class="d-flex flex-column gap-2">
      @foreach($attribute->values as $value)
        <div class="row align-items-start mb-2" data-value-id="{{ $value->id }}">
          {{-- Checkbox --}}
          <div class="col-md-3">
            <div class="form-check">
              <input type="checkbox"
                     class="form-check-input attribute-checkbox"
                     id="attr_{{ $attribute->id }}_{{ $value->id }}"
                     name="attribute_values[]"
                     value="{{ $value->id }}"
                     data-has-stock-price="{{ $attribute->has_stock_price ? '1' : '0' }}"
                     {{ in_array($value->id, $selectedValues) ? 'checked' : '' }}>
              <label class="form-check-label" for="attr_{{ $attribute->id }}_{{ $value->id }}">
                {{ $value->value }}
              </label>
            </div>
          </div>

          <div class="col-md-5">
            <div class="row">

            

                {{-- Stock --}}
                <div class="col-md-12 mb-2 stock-price-fields">
                  @if($attribute->has_stock_price && in_array($value->id, $selectedValues))
                    <input type="number"
                          name="attribute_meta[{{ $value->id }}][stock]"
                          value="{{ $attributeMeta[$value->id]['stock'] ?? '' }}"
                          placeholder="Stock"
                          class="form-control stock-input"
                          step="1" min="0">
                  @endif
                </div>

                {{-- Precio --}}
                <div class="col-md-12 mb-2 stock-price-fields">
                  @if($attribute->has_stock_price && in_array($value->id, $selectedValues))
                    <input type="number"
                          name="attribute_meta[{{ $value->id }}][price]"
                          value="{{ $attributeMeta[$value->id]['price'] ?? '' }}"
                          placeholder="Precio"
                          class="form-control price-input"
                          step="0.01" min="0">
                  @endif
                </div>

                <div class="col-md-12 image-field">
                  @if($attribute->has_stock_price && in_array($value->id, $selectedValues))
                    <input type="file"
                          name="attribute_meta[{{ $value->id }}][image]"
                          accept="image/*"
                          class="form-control mb-1">
                  @endif
                </div>                



            </div>

          </div>

          <div class="col-md-4">

                {{-- Imagen --}}
                <div class="col-md-12 image-prev">
                  @php
                    $src = !empty($attributeMeta[$value->id]['image']) ? asset('storage/' . $attributeMeta[$value->id]['image']) : '';
                  @endphp
                  <img src="{{ $src }}"
                      alt="Imagen"
                      class="img-thumbnail {{ $src ? '' : 'd-none' }}"
                      style="max-height: 100px;">
                </div>

          </div>

          
        </div>
      @endforeach
    </div>
  </div>
@endforeach

<script>
  function actualizarAlertas() {
    let mostrarAlerta = false;
    document.querySelectorAll('.attribute-checkbox').forEach(function (checkbox) {
      if (checkbox.checked && checkbox.dataset.hasStockPrice === '1') {
        mostrarAlerta = true;
      }
    });
    document.querySelectorAll('.attribute-alert').forEach(function (alerta) {
      alerta.classList.toggle('d-none', !mostrarAlerta);
    });
  }

  function bindImagePreview(input, preview) {
    input.addEventListener('change', function (e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function (event) {
          preview.src = event.target.result;
          preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
      } else {
        preview.style.display = 'none';
        preview.src = '';
      }
    });
  }

  function bindPreviewToExistingInputs() {
    document.querySelectorAll('.image-field input[type="file"]').forEach(function (input) {
      const row = input.closest('.row');
      const previewContainer = row.querySelector('.image-prev');

      if (!previewContainer) return;

      // Si ya hay <img>, lo usamos. Si no, lo creamos.
      let preview = previewContainer.querySelector('img');
      if (!preview) {
        preview = document.createElement('img');
        preview.className = 'img-thumbnail mt-2';
        preview.style.maxHeight = '100px';
        preview.style.display = 'none';
        previewContainer.appendChild(preview);
      }

      bindImagePreview(input, preview);
    });
  }

  function bindAttributeCheckboxEvents() {
    document.querySelectorAll('.attribute-checkbox').forEach(function (checkbox) {
      checkbox.addEventListener('change', function () {
        const row = checkbox.closest('.row');
        const hasStockPrice = checkbox.dataset.hasStockPrice === '1';
        const valueId = checkbox.value;

        const stockCol = row.querySelector('.stock-price-fields:nth-child(1)');
        const priceCol = row.querySelector('.stock-price-fields:nth-child(2)');
        const imageCol = row.querySelector('.image-field');
        const imagePrev = row.querySelector('.image-prev');

        stockCol.innerHTML = '';
        priceCol.innerHTML = '';
        imageCol.innerHTML = '';
        imagePrev.innerHTML = '';

        if (checkbox.checked && hasStockPrice) {
          const stockInput = document.createElement('input');
          stockInput.type = 'number';
          stockInput.step = '1';
          stockInput.min = '0';
          stockInput.name = `attribute_meta[${valueId}][stock]`;
          stockInput.placeholder = 'Stock';
          stockInput.className = 'form-control stock-input mb-1';
          stockCol.appendChild(stockInput);

          const priceInput = document.createElement('input');
          priceInput.type = 'number';
          priceInput.step = '0.01';
          priceInput.min = '0';
          priceInput.name = `attribute_meta[${valueId}][price]`;
          priceInput.placeholder = 'Precio';
          priceInput.className = 'form-control price-input mb-1';
          priceCol.appendChild(priceInput);

          const imageInput = document.createElement('input');
          imageInput.type = 'file';
          imageInput.accept = 'image/*';
          imageInput.name = `attribute_meta[${valueId}][image]`;
          imageInput.className = 'form-control mb-1';
          imageCol.appendChild(imageInput);

          let preview = document.createElement('img');
          preview.className = 'img-thumbnail mt-2';
          preview.style.maxHeight = '100px';
          preview.style.display = 'none';
          imagePrev.appendChild(preview);

          bindImagePreview(imageInput, preview);
        }

        actualizarAlertas();
      });
    });

    document.querySelectorAll('.stock-input, .price-input').forEach(function (input) {
      input.addEventListener('input', actualizarAlertas);
    });

    actualizarAlertas();
  }

  // Ejecutar todos los bindings necesarios
  bindAttributeCheckboxEvents();
  bindPreviewToExistingInputs();
</script>