@extends('layouts.backend')

@section('content')
<!-- Hero -->
<div class="bg-body-light">
  <div class="content content-full">
    <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
      <div class="flex-grow-1">
        <h1 class="h3 fw-bold mb-1">Productos</h1>
        <h2 class="fs-base lh-base fw-medium text-muted mb-0">
          Crear nuevo producto
        </h2>
      </div>
      <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-alt">
          <li class="breadcrumb-item"><a class="link-fx" href="#">Comercio</a></li>
          <li class="breadcrumb-item"><a class="link-fx" href="{{ route('admin.products.index') }}">Productos</a></li>
          <li class="breadcrumb-item" aria-current="page">Nuevo</li>
        </ol>
      </nav>
    </div>
  </div>
</div>
<!-- END Hero -->

<div class="content">

<form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
  @csrf

  <div class="row">

    <!-- MAIN -->
    <div class="col-lg-8 space-y-4">

      <div class="block block-rounded">
        <div class="block-header block-header-default">
          <h3 class="block-title">Información</h3>
        </div>
        <div class="block-content block-content-full">

          <div class="mb-3">
            <label for="name" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
          </div>



          <div class="mb-3">
            <label for="short_description" class="form-label">Descripción Corta</label>
            <textarea class="form-control" id="short_description" name="short_description">{{ old('short_description') }}</textarea>
          </div>

          <div class="mb-3">
            <label for="description" class="form-label">Descripción</label>
            <textarea class="form-control" id="description" name="description" rows="15">{{ old('description') }}</textarea>
          </div>

          <div class="row mb-3">

            <div class="col-6 col-lg-3 mb-3">
              <label for="price" class="form-label">Precio final</label>
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" step="0.01" class="form-control" id="price" name="price" value="{{ old('price') }}" required>
              </div>
            </div>

            <div class="col-6 col-lg-3 mb-3">
              <label for="base_price" class="form-label">Precio base</label>
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" step="0.01" class="form-control" id="base_price" name="base_price" value="{{ old('base_price') }}" min="0">
              </div>
              <div class="form-text">Opcional. Si es mayor al precio final, se mostrará tachado.</div>
            </div>

            <div class="col-6 col-lg-3 mb-3">
              <label for="wholesale_price" class="form-label">Precio mayorista</label>
              <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" step="0.01" class="form-control" id="wholesale_price" name="wholesale_price" value="{{ old('wholesale_price') }}" min="0">
              </div>
            </div>

            <div class="col-6 col-lg-3 mb-3">
              <label for="wholesale_min_quantity" class="form-label">Mínimo mayorista</label>
              <input type="number" class="form-control" id="wholesale_min_quantity" name="wholesale_min_quantity" value="{{ old('wholesale_min_quantity') }}" min="1" placeholder="Ej. 6">
            </div>
  
            <div class="col-6 col-lg-3 mb-3 js-stock-col">
              <label for="stock" class="form-label">Stock</label>
              <input type="number" class="form-control" id="stock" name="stock" value="{{ old('stock') }}" min="0">
            </div>

            <div class="col-6 col-lg-3 mb-3">
              <label for="sku" class="form-label">SKU - Código</label>
              <input type="text" class="form-control" id="sku" name="sku" value="{{ old('sku') }}">
            </div>            
          </div>

        </div>
      </div>

          <div class="block block-rounded">
            <div class="block-header block-header-default">
              <h3 class="block-title">Meta</h3>
            </div>
            <div class="block-content block-content-full">
              <div class="mb-3">
                <label for="meta_title" class="form-label">Meta Título</label>
                <input type="text" class="form-control" id="meta_title" name="meta_title" value="{{ old('meta_title') }}">
              </div>
              <div class="mb-3">
                <label for="meta_keywords" class="form-label">Meta Keywords</label>
                <input type="text" class="form-control" id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords') }}">
              </div>
              <div class="mb-3">
                <label for="meta_description" class="form-label">Meta Descripción</label>
                <textarea class="form-control" id="meta_description" name="meta_description">{{ old('meta_description') }}</textarea>
              </div>
            </div>
          </div>

          <div class="block block-rounded">
            <div class="block-header block-header-default">
              <h3 class="block-title">Galería de imágenes</h3>
            </div>
            
            <div class="block-content block-content-full dropzone" id="dropzone"></div>
            <input type="hidden" name="gallery_images" id="gallery_images" value="[]">
          </div>          

          <div class="d-flex mb-5">
            <a href="{{ route('admin.products.index') }}" class="btn btn-alt-primary me-2">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar</button>
          </div> 
    </div>

    <!-- SIDEBAR -->
    <div class="col-lg-4">

    <div class="block block-rounded">
      <div class="block-header block-header-default">
        <h3 class="block-title">FOTOGRAFÍA DESTACADA</h3>
      </div>
      <div class="block-content block-content-full">
        <div class="form-group">
          <div class="image-preview" id="imagePreview">
            <img src="#" alt="Vista previa" class="image-preview__image" style="display: none;" />
            <span class="image-preview__default-text">Sin imagen seleccionada</span>
          </div>
          <input type="file" name="featured_image" id="featuredImage" class="form-control mt-2" accept="image/*" />
        </div>
      </div>
    </div>

      <div class="block block-rounded">
        <div class="block-header block-header-default">
          <h3 class="block-title">Categorías y Atributos</h3>
        </div>
        <div class="block-content block-content-full">
          <div class="mb-3">
            <label for="categories" class="form-label">Categorías</label>
            <select name="categories[]" id="categories" class="form-select" multiple size="{{ min(max(count($categories), 6), 12) }}">
              @foreach($categories as $category)
                <option value="{{ $category->id }}"
                  {{ in_array($category->id, old('categories', isset($product) ? $product->categories->pluck('id')->toArray() : [])) ? 'selected' : '' }}>
                  {{ $category->name }}
                </option>
              @endforeach
            </select>
            <div class="form-text">Podés seleccionar varias categorías manteniendo presionada la tecla Ctrl o Cmd.</div>
          </div>
          <div id="attributes-container"></div>
        </div>
      </div>

      <div class="block block-rounded">
        <div class="block-header block-header-default">
          <h3 class="block-title">Datos de Envío</h3>
        </div>
        <div class="block-content block-content-full">
          <div class="row mb-3 js-shipping-fields">
              <div class="col-12 col-lg-6 mb-3">
                <label class="form-label">Alto (cm)</label>
                <input type="number" step="0.01" class="form-control js-shipping-input" name="height" value="{{ old('height') }}">
              </div>
              <div class="col-12 col-lg-6 mb-3">
                <label class="form-label">Ancho (cm)</label>
                <input type="number" step="0.01" class="form-control js-shipping-input" name="width" value="{{ old('width') }}">
              </div>
          </div>
          <div class="row mb-3 js-shipping-fields">
              <div class="col-12 col-lg-6 mb-3">
                <label class="form-label">Largo (cm)</label>
                <input type="number" step="0.01" class="form-control js-shipping-input" name="length" value="{{ old('length') }}">
              </div>
              <div class="col-12 col-lg-6 mb-3">
                <label class="form-label">Peso (kg)</label>
                <input type="number" step="0.01" class="form-control js-shipping-input" name="weight" value="{{ old('weight') }}">
              </div>
          </div>
          <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" id="is_digital" name="is_digital" value="1" {{ old('is_digital') ? 'checked' : '' }}>
            <label class="form-check-label" for="is_digital">Producto Digital</label>
          </div>
          <div class="mb-2">
            <label for="downloadable_files" class="form-label">Archivos descargables (PDF/ZIP)</label>
            <input type="file" class="form-control" id="downloadable_files" name="downloadable_files[]" accept=".pdf,.zip,application/pdf,application/zip" multiple>
            <div class="form-text">Podés subir uno o varios archivos. Si cargás archivos, el producto quedará marcado como digital.</div>
          </div>
        </div>
      </div>

      <div class="block block-rounded">
        <div class="block-header block-header-default">
          <h3 class="block-title">Estados</h3>
        </div>
        <div class="block-content block-content-full">
          <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Disponible</label>
          </div>          
          <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" id="is_new" name="is_new" value="1" {{ old('is_new') ? 'checked' : '' }}>
            <label class="form-check-label" for="is_new">Nuevo</label>
          </div>
          <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
            <label class="form-check-label" for="is_featured">Destacado</label>
          </div>
        </div>
      </div>



    </div>

  </div>
</form>

</div>

<style>
  .dropzone{
    border: none;
  }
  .dz-drag-hover {
    border-color: #D4428B !important;
    border-style: dotted !important;
    border: 1px;
    border-bottom-right-radius: 0.375rem;
    border-bottom-left-radius: 0.375rem;
    border-top-right-radius: 0;
    border-top-left-radius: 0 ;
    background-color: #fefefe;
  }

  /* Tamaño de cada preview */
  .dropzone .dz-preview {
    width: 15%;
    height: 120px; 
    margin: 0.5rem;
    display: inline-block;
    border: 1px solid #ddd;
    border-radius: 0.375rem;
    overflow: hidden;
  }

  /* Imagen ajustada dentro del contenedor */
  .dropzone .dz-preview .dz-image {
    width: 100%;
    height: 100%;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .dropzone .dz-preview .dz-image img {
    width: 100%;
    height: 100%;
    object-fit: contain; /* 🔑 Ajusta la imagen dentro del contenedor sin recortar */
  }

  .dropzone .dz-remove {
    display: inline-block;
    margin-top: 8px;
    text-decoration: none;
    color: #D4428B  ; /* rojo Bootstrap */
    font-weight: bold;
    cursor: pointer;
  position: absolute;
      bottom: 10px;
      margin: auto;
      width: 100%;
      z-index: 9999;
    
  }

  .dropzone .dz-remove:hover {
    text-decoration: underline;
  }


.image-preview {
  width: 100%;
  min-height: 200px;
  border: 2px dashed #dddddd;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #cccccc;
  position: relative;
}

.image-preview__image {
  width: 100%;
  height: auto;
  max-height: 250px;
  object-fit: contain;
}

.image-preview__default-text {
  font-size: 1rem;
}
</style>
@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>
<script>
  Dropzone.autoDiscover = false;

  $(document).ready(function() {
    const $digitalToggle = $('#is_digital');
    const $downloadableFiles = $('#downloadable_files');
    const $stockInput = $('#stock');
    const $stockCol = $('.js-stock-col');
    const $shippingFields = $('.js-shipping-fields');
    const $shippingInputs = $('.js-shipping-input');

    function toggleDigitalProductFields() {
      const hasDownloadable = $downloadableFiles.length && $downloadableFiles[0].files && $downloadableFiles[0].files.length > 0;
      const isDigital = $digitalToggle.is(':checked') || hasDownloadable;

      $stockCol.toggleClass('d-none', isDigital);
      $shippingFields.toggleClass('d-none', isDigital);

      $stockInput.prop('disabled', isDigital).prop('required', !isDigital);
      $shippingInputs.prop('disabled', isDigital);
    }

    $digitalToggle.on('change', toggleDigitalProductFields);
    $downloadableFiles.on('change', function() {
      if (this.files && this.files.length > 0) {
        $digitalToggle.prop('checked', true);
      }
      toggleDigitalProductFields();
    });

    toggleDigitalProductFields();

    $('#categories').on('change', function() {
      const selected = $(this).val() || [];
      let selectedAttributeValues = [];
      $('input[name="attribute_values[]"]:checked').each(function() {
        selectedAttributeValues.push($(this).val());
      });

      if (selected.length) {
        $.ajax({
          url: '{{ route("admin.products.getAttributesByCategories") }}',
          method: 'POST',
          data: { categories: selected, _token: '{{ csrf_token() }}' },
          success: function(data) {
            $('#attributes-container').html(data.html);
            selectedAttributeValues.forEach(function(value) {
              $('input[name="attribute_values[]"][value="' + value + '"]').prop('checked', true);
            });
          }
        });
      } else {
        $('#attributes-container').html('');
      }
    });


  if ($('#dropzone').length) {
    let uploadedImages = [];

    const dz = new Dropzone("#dropzone", {
      url: "{{ route('admin.products.uploadTempImage') }}",
      paramName: "file",
      maxFilesize: 8,
      acceptedFiles: "image/*",
      addRemoveLinks: true,
      dictDefaultMessage: "<i class='fa fa-upload'></i>",
      headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
      params: {
        product_id: "{{ $product->id ?? '' }}"
      },      
      success: function(file, response) {
        uploadedImages.push(response.path);
        $('#gallery_images').val(JSON.stringify(uploadedImages));
      },
      removedfile: function(file) {
        // Opcional: elimina del array
        let name = file.upload.filename;
        uploadedImages = uploadedImages.filter(path => !path.includes(name));
        $('#gallery_images').val(JSON.stringify(uploadedImages));

        // Remueve vista previa
        var _ref;
        return (_ref = file.previewElement) != null ? _ref.parentNode.removeChild(file.previewElement) : void 0;
      }
    });
  }    

  });


document.addEventListener("DOMContentLoaded", function() {
  const featuredImageInput = document.getElementById("featuredImage");
  const previewContainer = document.getElementById("imagePreview");
  const previewImage = previewContainer.querySelector(".image-preview__image");
  const previewDefaultText = previewContainer.querySelector(".image-preview__default-text");

  featuredImageInput.addEventListener("change", function() {
    const file = this.files[0];

    if (file) {
      const reader = new FileReader();

      previewDefaultText.style.display = "none";
      previewImage.style.display = "block";

      reader.addEventListener("load", function() {
        previewImage.setAttribute("src", this.result);
      });

      reader.readAsDataURL(file);
    } else {
      previewDefaultText.style.display = "block";
      previewImage.style.display = "none";
      previewImage.setAttribute("src", "#");
    }
  });
});
</script>
@endsection
