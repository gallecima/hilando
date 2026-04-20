@extends('layouts.backend')

@section('actions')
    <a href="{{ route('admin.sliders.create') }}" type="button" class="ms-2 btn btn-sm btn-alt-primary">Nuevo Slider</a>
@endsection

@section('content')
<!-- Hero -->
<div class="bg-body-light">
    <div class="content content-full">
        <div class="d-flex flex-column flex-sm-row justify-content-sm-between align-items-sm-center py-2">
            <div class="flex-grow-1">
                <h1 class="h3 fw-bold mb-1">
                    Sliders
                </h1>
                <h2 class="fs-base lh-base fw-medium text-muted mb-0">
                    Gestor de banners para el sitio web
                </h2>
            </div>
            <nav class="flex-shrink-0 mt-3 mt-sm-0 ms-sm-3" aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-alt">
                    <li class="breadcrumb-item">
                        <a class="link-fx" href="javascript:void(0)">Contenido</a>
                    </li>
                    <li class="breadcrumb-item" aria-current="page">
                        Sliders
                    </li>
                </ol>
            </nav>
        </div>
    </div>
</div>
<!-- END Hero -->

<div class="content">

    @if(session('success'))
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            One.helpers('jq-notify', {
                type: 'success',
                icon: 'fa fa-check-circle me-1',
                from: 'bottom',
                message: @json(session('success'))
            });
        });
    </script>
    @endif

    @if(session('error'))
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            One.helpers('jq-notify', {
                type: 'danger',
                icon: 'fa fa-times-circle me-1',
                from: 'bottom',
                message: @json(session('error'))
            });
        });
    </script>
    @endif

    <div class="block block-rounded">
        <div class="block-content block-content-full overflow-x-auto">
            <table class="table table-striped table-vcenter js-dataTable-full">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Slug</th>
                        <th>Activo</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sliders as $slider)
                    <tr>
                        <td>{{ $slider->nombre }}</td>
                        <td><code>{{ $slider->slug }}</code></td>
                        <td>
                            <span class="badge {{ $slider->activo ? 'bg-success' : 'bg-danger' }}">
                                {{ $slider->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group">
                                <a href="{{ route('admin.sliders.edit', $slider) }}" class="btn btn-sm btn-alt-secondary" data-bs-toggle="tooltip" title="Editar Slider">
                                    <i class="fa fa-fw fa-pencil-alt"></i>
                                </a>
                                <a href="{{ route('admin.sliders.images', $slider) }}" class="btn btn-sm btn-alt-secondary"  data-bs-toggle="tooltip" title="Imágenes del Slider"><i class="fa fa-images"></i></a>
                                <form action="{{ route('admin.sliders.destroy', $slider) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-alt-secondary" data-bs-toggle="tooltip" title="Eliminar Slider" onclick="return confirm('¿Estás seguro de eliminar este slider?')">
                                        <i class="fa fa-fw fa-times"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    $(document).ready(function(){
        var table = $('.js-dataTable-full').DataTable({
            order: [[0, 'asc']],
            paging: false,
            dom: 'lrtip'
        });

        $('#customSearch').on('keyup', function () {
            table.search(this.value).draw();
        });
        $('#customSearch').focus();
    });
</script>
@endsection