@extends('layouts.backend')

@section('content')
<!-- Hero -->
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-1">Categorías</h1>
                <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                    Administración de categorías de productos
                </h2>
            </div>
            <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-alt">
                    <li class="breadcrumb-item">
                        <a class="link-fx" href="javascript:void(0)">Comercio</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a class="link-fx" href="{{ route('admin.categories.index') }}">Categorías</a>
                    </li>
                    <li class="breadcrumb-item" aria-current="page">
                        {{ $category->name }}
                    </li>
                </ol>
            </nav>
        </div>
    </div>
</div>
<!-- END Hero -->

<div class="content">

    <form action="{{ route('admin.categories.update', $category) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
            <div class="row">


                <div class="col-lg-8">

                    <div class="block block-rounded">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">Editar Categoría</h3>
                        </div>
                        <div class="block-content block-content-full">

                            <div class="mb-3">
                                <label for="name" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="{{ old('name', $category->name) }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="parent_id" class="form-label">Categoría Padre</label>
                                <select class="form-select" id="parent_id" name="parent_id">
                                    <option value="">-- Ninguna --</option>
                                    @foreach($categories as $parent)
                                        <option value="{{ $parent->id }}"
                                            {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>
                                            {{ $parent->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="attributes" class="form-label">Atributos disponibles</label>
                                <select class="form-select" id="attributes" name="attributes[]" multiple size="{{ min(max(count($attributes), 6), 12) }}">
                                    @foreach($attributes as $attribute)
                                        <option value="{{ $attribute->id }}"
                                            {{ in_array($attribute->id, old('attributes', $category->attributes->pluck('id')->toArray())) ? 'selected' : '' }}>
                                            {{ $attribute->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Podés seleccionar varios manteniendo presionada la tecla Ctrl o Cmd.</div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Descripción</label>
                                <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $category->description) }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label for="order" class="form-label">Orden</label>
                                <input type="number" class="form-control" id="order" name="order"
                                    value="{{ old('order', $category->order) }}">
                            </div>

                        </div>
                    </div>

                                        <div class="d-flex">
                                            <a href="{{ route('admin.categories.index') }}" class="btn btn-alt-primary me-2">Cancelar</a>
                                            <button type="submit" class="btn btn-primary">Guardar</button>
                                        </div>                    
                    
                </div>


                <div class="col-lg-4">

                    <div class="block block-rounded">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">Estado</h3>
                        </div>
                        <div class="block-content block-content-full">

                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                    {{ $category->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Activo</label>
                            </div>                        

                        </div>
                    </div>

                    <div class="block block-rounded">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">Imágen Principal</h3>
                        </div>
                        <div class="block-content block-content-full">


                            <div class="image-preview mb-2" id="imagePreview">
                                <img src="{{ $category->image ? asset('storage/'.$category->image) : '#' }}"
                                    alt="Vista previa"
                                    class="image-preview__image"
                                    style="{{ $category->image ? '' : 'display:none;' }}"
                                    id="image-preview-click" />
                                <span class="image-preview__default-text">{{ $category->image ? '' : 'Sin imagen seleccionada' }}</span>
                            </div>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                                
                        </div>
                    </div>

                    <div class="block block-rounded">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">Icono de Categoría</h3>
                        </div>
                        <div class="block-content block-content-full">                    


                            <div class="image-preview mb-2" id="iconPreview">
                                <img src="{{ $category->icon ? asset('storage/'.$category->icon) : '#' }}"
                                    alt="Vista previa"
                                    class="image-preview__image"
                                    style="{{ $category->icon ? '' : 'display:none;' }}"
                                    id="icon-preview-click" />
                                <span class="image-preview__default-text">{{ $category->icon ? '' : 'Sin icono seleccionado' }}</span>
                            </div>
                            <input type="file" class="form-control" id="icon" name="icon" accept="image/*">
                                              
                                                
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

            </div>
    </form>

</div>

<!-- Modal Cropper -->
<div class="modal fade" id="modal-cropper" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Imagen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" style="padding: 0px;">
                <img id="cropper-image" src="" alt="Cropper" style="max-width: 100%;">
            </div>
            <div class="modal-footer">
                <button type="button" id="cropper-save" class="btn btn-primary">Guardar</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<style>
.image-preview {
    width: 100%;
    min-height: 180px;
    border: 2px dashed #ddd;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #999;
    position: relative;
}

.image-preview__image {
    width: auto;
    max-height: 150px;
    object-fit: contain;
}

.image-preview__default-text {
    font-size: .9rem;
    color: #666;
}
</style>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Vista previa dinámica
    $('#image').on('change', function() {
        previewImage(this, '#imagePreview');
    });
    $('#icon').on('change', function() {
        previewImage(this, '#iconPreview');
    });

    function previewImage(input, container) {
        const previewContainer = $(container);
        const previewImage = previewContainer.find('.image-preview__image');
        const previewDefaultText = previewContainer.find('.image-preview__default-text');

        const file = input.files[0];
        if (file) {
            const reader = new FileReader();
            previewDefaultText.hide();
            previewImage.show();
            reader.onload = function(e) {
                previewImage.attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        } else {
            previewDefaultText.show();
            previewImage.hide();
            previewImage.attr('src', '#');
        }
    }

    // Cropper modal
    let cropper = null;
    let cropTarget = 'image';

    $(document).on('click', '#image-preview-click', function() {
        cropTarget = 'image';
        openCropperModal($(this).attr('src'));
    });

    $(document).on('click', '#icon-preview-click', function() {
        cropTarget = 'icon';
        openCropperModal($(this).attr('src'));
    });

    function openCropperModal(src) {
        const bustSrc = src.split('?')[0] + '?cb=' + Date.now();
        $('#cropper-image').attr('src', bustSrc);

        const modal = new bootstrap.Modal(document.getElementById('modal-cropper'));
        modal.show();

        setTimeout(() => {
            const image = document.getElementById('cropper-image');
            if (cropper) cropper.destroy();

            cropper = new Cropper(image, {
                aspectRatio: cropTarget === 'icon' ? 1 : 16 / 9,
                viewMode: 1,
                autoCropArea: 1
            });
        }, 500);
    }

    $('#modal-cropper').on('hidden.bs.modal', function () {
        if (cropper) { cropper.destroy(); cropper = null; }
    });

    $(document).on('click', '#cropper-save', function() {
        cropper.getCroppedCanvas().toBlob(function(blob) {
            const formData = new FormData();
            formData.append('cropped_image', blob);
            formData.append('target', cropTarget);
            formData.append('_token', '{{ csrf_token() }}');

            fetch("{{ route('admin.categories.crop', $category) }}", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) location.reload();
                else alert('Error al guardar.');
            })
            .catch(() => alert('Error en la red.'));
        });
    });
});
</script>
@endsection
