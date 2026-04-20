var swiper = new Swiper(".swiper-one", {
  slidesPerView: 1,
  spaceBetween: 25,
  loop: true,
  autoplay: {
    delay: 6000,
  },
  breakpoints: {
    0: {
      slidesPerView: 1,
      spaceBetween: 25
    },
    768: {
      slidesPerView: 1,
      spaceBetween: 25
    },
    1024: {
      slidesPerView: 1,
      spaceBetween: 25
    },
  },
  pagination: {
    el: ".swiper-pagination",
    clickable: true,
  },
  navigation: {
    nextEl: ".swiper-button-next",
    prevEl: ".swiper-button-prev",
  },
  on: {
    init() {
      this.el.addEventListener('mouseenter', () => {
        this.autoplay.stop();
      });

      this.el.addEventListener('mouseleave', () => {
        this.autoplay.start();
      });
    }
  }
  });