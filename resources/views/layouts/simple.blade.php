<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  @php
    $baseSiteTitle = $siteTitle ?? config('app.name', 'Tienda');
    $pageTitle = trim((string) $__env->yieldContent('title'));
    $fullTitle = $pageTitle !== '' ? ($baseSiteTitle . ' | ' . $pageTitle) : $baseSiteTitle;
  @endphp
  <title>{{ $fullTitle }}</title>
  <link rel="shortcut icon" href="{{ asset('media/favicons/favicon.png') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  @include('front.partials.typography-styles')
  @stack('styles')
</head>
<body class="@yield('body_class')">
  <main class="container py-5">
    @yield('content')
  </main>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  @stack('scripts')
</body>
</html>
