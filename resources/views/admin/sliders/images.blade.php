@extends('layouts.backend')

@section('content')
<!-- Hero -->
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex justify-content-between align-items-center py-2">
            <div>
                <h1 class="h3 fw-bold mb-1">Imágenes del Slider</h1>
                <h2 class="fs-base text-muted mb-0">{{ $slider->nombre }}</h2>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-alt">
                    <li class="breadcrumb-item"><a href="{{ route('admin.sliders.index') }}">Sliders</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Imágenes</li>
                </ol>
            </nav>
        </div>
    </div>
</div>
<!-- END Hero -->

<div class="content">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Formulario para subir una imagen -->
    <div class="block block-rounded">
        <div class="block-header">
            <h3 class="block-title">Agregar Imagen</h3>
        </div>
        <div class="block-content">
            <form action="{{ route('admin.sliders.images.store', $slider) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row items-push">
                    <div class="col-lg-8">
                        <div class="mb-3">
                            <label for="imagen" class="form-label">Imagen (JPG/PNG)</label>
                            <input class="form-control" type="file" id="imagen" name="imagen" required>
                        </div>

                        <div class="mb-3">
                            <label for="orden" class="form-label">Orden</label>
                            <input class="form-control" type="number" name="orden" id="orden" value="{{ old('orden', 0) }}">
                        </div>

                        <div class="mb-3">
                            <label for="hero_title" class="form-label">Título del slide</label>
                            <input class="form-control" type="text" name="hero_title" id="hero_title" value="{{ old('hero_title') }}" maxlength="255">
                        </div>

                        <div class="mb-4">
                            <label for="hero_text" class="form-label">Texto del slide</label>
                            <textarea class="form-control" name="hero_text" id="hero_text" rows="4">{{ old('hero_text') }}</textarea>
                        </div>

                        <div class="border rounded p-3">
                            <h4 class="h5 mb-3">Botones CTA</h4>
                            <p class="text-muted mb-3">Podés cargar hasta 5 botones. Si dejás el texto o el link vacío, ese botón no se muestra.</p>

                            @for($i = 0; $i < 5; $i++)
                                <div class="row g-3 mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label" for="cta_buttons_{{ $i }}_label">Texto botón {{ $i + 1 }}</label>
                                        <input
                                            class="form-control"
                                            type="text"
                                            id="cta_buttons_{{ $i }}_label"
                                            name="cta_buttons[{{ $i }}][label]"
                                            value="{{ old("cta_buttons.$i.label") }}"
                                            maxlength="80"
                                        >
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label" for="cta_buttons_{{ $i }}_url">Link botón {{ $i + 1 }}</label>
                                        <input
                                            class="form-control"
                                            type="text"
                                            id="cta_buttons_{{ $i }}_url"
                                            name="cta_buttons[{{ $i }}][url]"
                                            value="{{ old("cta_buttons.$i.url") }}"
                                            placeholder="/categoria/todas o https://..."
                                            maxlength="255"
                                        >
                                    </div>
                                </div>
                            @endfor
                        </div>

                        <button type="submit" class="btn btn-primary">Subir Imagen</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de imágenes existentes -->
    <div class="block block-rounded">
        <div class="block-header">
            <h3 class="block-title">Imágenes Actuales (arrastrá para reordenar)</h3>
        </div>
        <div class="block-content">
            @if($images->isEmpty())
                <p class="text-muted">No hay imágenes en este slider.</p>
            @else
                <div class="row" id="sortable-images">
                    @foreach($images as $image)
                        @php
                            $ctaButtons = collect($image->cta_buttons ?? [])
                                ->take(5)
                                ->pad(5, ['label' => '', 'url' => ''])
                                ->values();
                        @endphp
                        <div class="col-xl-6 mb-4 sortable-item" data-id="{{ $image->id }}">
                            <div class="border p-3 rounded h-100 bg-body-light">
                                <img src="{{ asset('storage/' . $image->imagen) }}" class="img-fluid rounded mb-3" alt="Imagen Slider">
                                <p class="mb-3 slider-image-order"><strong>Orden:</strong> {{ $image->orden }}</p>

                                <form action="{{ route('admin.sliders.images.update', [$slider, $image]) }}" method="POST" enctype="multipart/form-data" class="mb-3">
                                    @csrf
                                    @method('PUT')

                                    <div class="mb-3">
                                        <label class="form-label" for="image_{{ $image->id }}">Reemplazar imagen</label>
                                        <input class="form-control" type="file" id="image_{{ $image->id }}" name="imagen">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="order_{{ $image->id }}">Orden</label>
                                        <input class="form-control" type="number" name="orden" id="order_{{ $image->id }}" value="{{ old('orden', $image->orden) }}">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="title_{{ $image->id }}">Título del slide</label>
                                        <input class="form-control" type="text" name="hero_title" id="title_{{ $image->id }}" value="{{ old('hero_title', $image->hero_title) }}" maxlength="255">
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label" for="text_{{ $image->id }}">Texto del slide</label>
                                        <textarea class="form-control" name="hero_text" id="text_{{ $image->id }}" rows="4">{{ old('hero_text', $image->hero_text) }}</textarea>
                                    </div>

                                    <div class="border rounded p-3 mb-3">
                                        <h4 class="h5 mb-3">Botones CTA</h4>
                                        @foreach($ctaButtons as $index => $button)
                                            <div class="row g-3 mb-3">
                                                <div class="col-md-4">
                                                    <label class="form-label" for="image_{{ $image->id }}_cta_label_{{ $index }}">Texto botón {{ $index + 1 }}</label>
                                                    <input
                                                        class="form-control"
                                                        type="text"
                                                        id="image_{{ $image->id }}_cta_label_{{ $index }}"
                                                        name="cta_buttons[{{ $index }}][label]"
                                                        value="{{ old("cta_buttons.$index.label", $button['label'] ?? '') }}"
                                                        maxlength="80"
                                                    >
                                                </div>
                                                <div class="col-md-8">
                                                    <label class="form-label" for="image_{{ $image->id }}_cta_url_{{ $index }}">Link botón {{ $index + 1 }}</label>
                                                    <input
                                                        class="form-control"
                                                        type="text"
                                                        id="image_{{ $image->id }}_cta_url_{{ $index }}"
                                                        name="cta_buttons[{{ $index }}][url]"
                                                        value="{{ old("cta_buttons.$index.url", $button['url'] ?? '') }}"
                                                        placeholder="/categoria/todas o https://..."
                                                        maxlength="255"
                                                    >
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <button class="btn btn-primary w-100" type="submit">
                                        <i class="fa fa-save me-1"></i> Guardar cambios
                                    </button>
                                </form>

                                <form action="{{ route('admin.sliders.images.destroy', [$slider, $image]) }}" method="POST" onsubmit="return confirm('¿Eliminar esta imagen?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger w-100" type="submit">
                                        <i class="fa fa-times me-1"></i> Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script>
    $(function () {
        $('#sortable-images').sortable({
            update: function (event, ui) {
                let orden = [];
                $('.sortable-item').each(function (index) {
                    orden.push({
                        id: $(this).data('id'),
                        orden: index + 1
                    });
                });

                $.ajax({
                    url: "{{ route('admin.sliders.images.sort', $slider) }}",
                    method: 'POST',
                    data: {
                        orden: orden,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (res) {
                        $('.sortable-item').each(function (index) {
                            $(this).find('.slider-image-order').html('<strong>Orden:</strong> ' + (index + 1));
                            $(this).find('input[name="orden"]').val(index + 1);
                        });
                    },
                    error: function () {
                        alert('Error al actualizar el orden');
                    }
                });
            }
        });
    });
</script>
@endpush
