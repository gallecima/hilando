document.querySelectorAll('.swiper-four').forEach((el) => {
  const slideCount = el.querySelectorAll('.swiper-slide').length;
  if (!slideCount) return;

  const maxDesktop = 4;
  const maxTablet  = 3;

  const slidesPerViewDesktop = Math.min(maxDesktop, slideCount);
  const slidesPerViewTablet  = Math.min(maxTablet, slideCount);

  const nextEl = el.querySelector('.swiper-button-next');
  const prevEl = el.querySelector('.swiper-button-prev');
  const paginationEl = el.querySelector('.swiper-pagination');

  const options = {
    spaceBetween: 25,
    slidesPerView: slidesPerViewDesktop,
    loop: false,          // nunca clonar slides
    rewind: true,         // vuelve al inicio sin clones
    watchOverflow: true,
    autoplay: slideCount > 1 ? { delay: 6000 } : false,
    breakpoints: {
      0: {
        slidesPerView: 'auto',
        spaceBetween: 25,
      },
      768: {
        slidesPerView: slidesPerViewTablet,
        spaceBetween: 25,
      },
      1024: {
        slidesPerView: slidesPerViewDesktop,
        spaceBetween: 25,
      },
    },
    on: {
      init() {
        if (!this.params.autoplay) return;
        this.el.addEventListener('mouseenter', () => this.autoplay.stop());
        this.el.addEventListener('mouseleave', () => this.autoplay.start());
      },
    },
  };

  if (paginationEl) {
    options.pagination = {
      el: paginationEl,
      clickable: true,
    };
  }

  if (nextEl && prevEl) {
    options.navigation = {
      nextEl,
      prevEl,
    };
  }

  new Swiper(el, options);
});
