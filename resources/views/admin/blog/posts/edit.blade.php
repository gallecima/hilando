@extends('layouts.backend')

@section('content')
<!-- Hero -->
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-1">Editar Post</h1>
                <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                    Modificá la información del post del blog
                </h2>
            </div>
            <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-alt">
                    <li class="breadcrumb-item"><a class="link-fx" href="#">CMS</a></li>
                    <li class="breadcrumb-item"><a class="link-fx" href="{{ route('admin.blog.posts.index') }}">Posts</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $post->titulo }}</li>
                </ol>
            </nav>
        </div>
    </div>
</div>
<!-- END Hero -->

<div class="content">
    <div class="block block-rounded">
        <div class="block-header block-header-default">
            <h3 class="block-title">Editar Post</h3>
        </div>
        <div class="block-content">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.blog.posts.update', $post) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" value="{{ old('titulo', $post->titulo) }}" required>
                        </div>

                        <div class="mb-3">
                            <code>{{ route('post.show', $post->slug) }}</code>
                        </div>
                        
                        <div class="mb-3">
                            <label for="bajada" class="form-label">Bajada</label>
                            <input type="text" class="form-control" id="bajada" name="bajada" value="{{ old('bajada', $post->bajada) }}">
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea name="descripcion" id="descripcion" rows="8" class="form-control">{{ old('descripcion', $post->descripcion) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="imagen_destacada" class="form-label">Imagen destacada (URL o subir)</label>
                            @if ($post->imagen_destacada)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $post->imagen_destacada) }}" alt="Imagen destacada" style="max-height: 150px;">
                                </div>
                            @endif
                            <input type="file" class="form-control" name="imagen_destacada" id="imagen_destacada">
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1" {{ old('activo', $post->activo) ? 'checked' : '' }}>
                            <label class="form-check-label" for="activo">Publicar</label>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="fecha" class="form-label">Fecha de publicación</label>
                            <input type="date" class="form-control" id="fecha" name="fecha" value="{{ old('fecha', $post->fecha->format('Y-m-d')) }}">
                        </div>

                        <div class="mb-3">
                            <label for="blog_category_id" class="form-label">Categoría</label>
                            <select name="blog_category_id" id="blog_category_id" class="form-select" required>
                                <option value="">-- Seleccionar --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('blog_category_id', $post->blog_category_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @php
                            $selectedProducts = old('products', $selectedProductIds ?? []);
                        @endphp

                        @if(($canLinkProducts ?? false) === true)
                            <div class="mb-3">
                                <label for="products" class="form-label">Productos vinculados</label>
                                <select name="products[]" id="products" class="form-select" multiple size="10">
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" {{ in_array($product->id, $selectedProducts) ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Mantené presionado Ctrl/Cmd para seleccionar varios.</div>
                            </div>
                        @else
                            <div class="alert alert-warning py-2 px-3 small">
                                Para vincular productos, primero ejecutá la migración de `blog_post_product`.
                            </div>
                        @endif
                    </div>
                </div>

                <div class="d-flex mb-4">
                    <a href="{{ route('admin.blog.posts.index') }}" class="btn btn-alt-secondary me-2">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
