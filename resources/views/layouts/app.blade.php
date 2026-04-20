<!doctype html>
<html lang="{{ config('app.locale') }}">

<head>
  <meta charset="utf-8">
  <!--
    Available classes for <html> element:

    'dark'                  Enable dark mode - Default dark mode preference can be set in app.js file (always saved and retrieved in localStorage afterwards):
                              window.One = new App({ darkMode: "system" }); // "on" or "off" or "system"
    'dark-custom-defined'   Dark mode is always set based on the preference in app.js file (no localStorage is used)
  -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">

  @php
    $baseSiteTitle = $siteTitle ?? config('app.name', 'Tienda');
    $pageTitle = trim((string) $__env->yieldContent('title'));
    $fullTitle = $pageTitle !== '' ? ($baseSiteTitle . ' | ' . $pageTitle) : $baseSiteTitle;
  @endphp
  <title>{{ $fullTitle }}</title>

  <meta name="description" content="OneUI - Bootstrap 5 Admin Template &amp; UI Framework created by pixelcave">
  <meta name="author" content="pixelcave">
  <meta name="robots" content="index, follow">

  <!-- Icons -->
  <link rel="shortcut icon" href="{{ asset('media/favicons/favicon.png') }}">
  <link rel="icon" sizes="192x192" type="image/png" href="{{ asset('media/favicons/favicon-192x192.png') }}">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('media/favicons/apple-touch-icon-180x180.png') }}">


  <!-- Modules -->
  @yield('css')
  @vite(['resources/sass/main.scss', 'resources/js/oneui/app.js'])
</head>
<body>
    <div id="page-container">
        @yield('content')
    </div>


    
</body>
</html>
