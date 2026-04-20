@extends('layouts.front')

@section('title', 'Sobre Hilando')
@section('body_class', 'page-hero about-page')

@section('content')
  @php
    $breadcrumbs = [
      ['label' => 'Inicio', 'url' => route('home')],
      ['label' => 'Sobre Hilando'],
    ];

    $defaultSections = [
      'quienes-somos' => [
        'label' => 'Quienes somos',
        'title' => 'Quienes somos',
        'subtitle' => 'Una marca que pone en valor el oficio, la materia y las historias que atraviesan cada pieza.',
        'body' => [
          'Hilando es un proyecto que cruza diseno, produccion y cultura material para construir una tienda con identidad propia. Reunimos productos, relatos y procesos que nacen del trabajo consciente, de la seleccion de materiales y de una mirada contemporanea sobre lo artesanal.',
          'La marca se apoya en una idea simple: cada objeto cotidiano puede ser tambien una forma de narrar de donde venimos, como producimos y que valores queremos sostener en el tiempo.',
        ],
      ],
      'proposito' => [
        'label' => 'Proposito',
        'title' => 'Proposito',
        'subtitle' => 'Crear un puente entre el hacer, el habitar y una forma mas sensible de elegir lo que nos rodea.',
        'body' => [
          'Nuestro proposito es acercar piezas con caracter, con una presencia sobria y una historia clara detras. Queremos que el catalogo no sea solo un listado de productos, sino un espacio para descubrir texturas, tecnicas, decisiones de produccion y formas de uso.',
          'Trabajamos para que la experiencia de compra sea tan cuidada como el objeto final: simple, honesta y alineada con una estetica calida, atemporal y cercana.',
        ],
      ],
      'manifesto' => [
        'label' => 'Manifesto',
        'title' => 'Manifesto',
        'subtitle' => 'Una serie de ideas que ordenan nuestra forma de disenar, producir y compartir.',
        'body' => [
          'Creemos en las piezas que envejecen bien, en los materiales que ganan valor con el uso y en las decisiones visuales que no dependen de una tendencia momentanea.',
          'Elegimos una comunicacion clara, una experiencia de marca serena y un catalogo que prioriza la calidad por sobre el exceso. Preferimos lo esencial, lo legible y lo bien resuelto.',
          'Valoramos el trabajo hecho con criterio, la colaboracion con sentido y la posibilidad de construir una identidad que se reconozca en cada detalle.',
        ],
      ],
      'faqs' => [
        'label' => 'FAQs',
        'title' => 'Preguntas frecuentes',
        'subtitle' => 'Una primera guia para responder dudas habituales sobre la marca, los productos y la compra.',
        'body' => [
          'Que tipo de productos ofrece Hilando? Trabajamos con un catalogo curado de piezas textiles, objetos y recursos vinculados al habitar, al oficio y a la cultura material.',
          'Como se actualiza este contenido? Cada bloque de esta pagina puede vincularse a posts especificos para editarlo desde administracion, manteniendo la estructura del front intacta.',
          'Puedo sumar nuevas secciones en el futuro? Si. Esta pagina queda preparada para crecer y reorganizarse sin perder la logica editorial general.',
        ],
      ],
    ];

    $sectionOrder = ['quienes-somos', 'proposito', 'manifesto', 'faqs'];
    $aboutPosts = isset($aboutPosts) && $aboutPosts instanceof \Illuminate\Support\Collection ? $aboutPosts : collect();
    $aboutPostImages = isset($aboutPostImages) && $aboutPostImages instanceof \Illuminate\Support\Collection ? $aboutPostImages : collect();
    $aboutHeroSlides = isset($aboutHeroSlides) && $aboutHeroSlides instanceof \Illuminate\Support\Collection ? $aboutHeroSlides : collect($aboutHeroSlides ?? []);
  @endphp

  @include('front.partials.page-header', [
    'variant' => 'hero',
    // 'eyebrow' => 'Sobre Hilando',
    'title' => 'Sobre Hilando',
    // 'subtitle' => 'Una pagina editorial para contar quienes somos, que buscamos construir y como pensamos la marca.',
    // 'breadcrumbs' => $breadcrumbs,
    'slides' => $aboutHeroSlides,
    'backgroundImage' => $aboutHeroBackgroundImage ?? ($aboutHeroSlides->first()['src'] ?? null),
    'heroId' => 'aboutHilandoHeroCarousel',
  ])

  <section class="checkout-flow-section about-content-section">
    <div class="checkout-flow-shell">
      <div class="container">
        <div class="about-section-stack">
          @foreach($sectionOrder as $slug)
            @php
              $post = $aboutPosts->get($slug);
              $imagePost = $aboutPostImages->get($slug);
              $fallback = $defaultSections[$slug];
              $sectionTitle = trim((string) ($post->titulo ?? $fallback['title']));
              $sectionSubtitle = trim((string) ($post->bajada ?? $fallback['subtitle']));
              $sectionBody = trim((string) ($post->descripcion ?? ''));
              $bodyParagraphs = $sectionBody !== ''
                ? preg_split("/\\n\\s*\\n/", $sectionBody)
                : $fallback['body'];
              $featuredImage = filled($imagePost?->imagen_destacada)
                ? asset('storage/' . ltrim((string) $imagePost->imagen_destacada, '/'))
                : null;
            @endphp

            <section id="{{ $slug }}" class="about-section-block">
              <div class="about-section-row {{ $featuredImage ? '' : 'about-section-row--text-only' }}">
                <div class="about-section-copy">
                  {{-- <p class="about-section-kicker mb-2">{{ $fallback['label'] }}</p> --}}
                  <h2 class="about-section-title mb-3">{{ $sectionTitle }}</h2>

                  @if($sectionSubtitle !== '')
                    <p class="about-section-subtitle mb-4">{{ $sectionSubtitle }}</p>
                  @endif

                  <div class="about-section-body">
                    @foreach($bodyParagraphs as $paragraph)
                      @php($paragraph = trim((string) $paragraph))
                      @if($paragraph !== '')
                        <p class="product-showcase-summary">{{ $paragraph }}</p>
                      @endif
                    @endforeach
                  </div>
                </div>

                @if($featuredImage)
                  <div class="about-section-media">
                    <img src="{{ $featuredImage }}" alt="{{ $sectionTitle }}" class="about-section-image">
                  </div>
                @endif
              </div>
            </section>
          @endforeach
        </div>
      </div>
    </div>
  </section>
@endsection
