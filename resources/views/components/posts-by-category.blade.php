@if($categoria && $posts->count())

<section class="pt-0 beneficios-exclusivos">
  <div class="container mb-5">
    <div class="row d-flex align-items-center">
        <div class="col-6">
            <h2 class="section-title-dark" style="border-left: 5px solid #D52B1E">
              <?=resaltarMitad("$categoria->nombre");?>
              @if(isset($logo))

                @if(isset($link))
                  <a href="{{ $link }}" target="__blank">
                @endif
                <img src="{{ asset('images/'.$logo) }}" alt="" style="height:25px !important; width:auto; margin-bottom:12px">
                @if(isset($link))
                  </a>
                @endif
              @endif
            </h2>
        </div>
          <div class="col-6 text-end">
              <a href="{{ route('post.category', $categoria->slug) }}" class="btn btn-outline-secondary">+</a>
          </div>        
    </div>
  </div>

  <div class="container swiper swiper-four">
    <div class="swiper-wrapper">
      @foreach($posts as $post)
        <div class="swiper-slide">
          <a href="{{ route('post.show', ['slug' => $post->slug]) }}" class="card">
            @if($post->imagen_destacada)
              <img src="{{ asset('storage/' . $post->imagen_destacada) }}" alt="{{ $post->titulo }}">
            @endif
            <div class="card-body">
              <h6>{{ Str::limit($post->bajada, 100) }}</h6>
              <h1>{{ $post->titulo }}</h1>
            </div>
          </a>
        </div>
      @endforeach
    </div>

    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>

  </div>
</section>

  

@endif