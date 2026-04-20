
@if($orientacion == "horizontal")
<!---- BANNER NUEVO ----->
<section id="banner-nuevo" class="pt-0 d-none d-lg-block">
  <div class="container swiper swiper-one {{ $class }}">
    <div class="swiper-wrapper">
      @foreach($slider->images->sortBy('orden') as $image)
        <div class="swiper-slide">
          <img src="{{ asset('storage/' . $image->imagen) }}" class="img-fluid w-100 banner-img" alt="Slide">
        </div>
      @endforeach
    </div>
    <div class="swiper-pagination"></div>
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
  </div>
</section>
<!---- BANNER NUEVO ----->
@endif

@if($orientacion == "vertical")
<!---- BANNER NUEVO ----->
<section id="banner-nuevo" class="pt-0 d-block d-sm-none">
  <div class="container swiper swiper-one {{ $class }}">
    <div class="swiper-wrapper">
      @foreach($slider->images->sortBy('orden') as $image)
        <div class="swiper-slide">
          <img src="{{ asset('storage/' . $image->imagen) }}" class="img-fluid w-100 banner-img" alt="Slide">
        </div>
      @endforeach
    </div>
    <div class="swiper-pagination"></div>
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
  </div>
</section>
<!---- BANNER NUEVO ----->
@endif