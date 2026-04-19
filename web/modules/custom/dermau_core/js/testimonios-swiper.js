(function (Drupal) {
  Drupal.behaviors.testimoniosSwiper = {
    attach: function (context) {

      const swiperElement = context.querySelector('#block-testimonial-swiper');
      if (!swiperElement || swiperElement.classList.contains('swiper-initialized')) {
        return;
      }

      new Swiper('#block-testimonial-swiper', {
        loop: true,
        slidesPerView: 1,
        navigation: {
          nextEl: '.du-testimonials__next',
          prevEl: '.du-testimonials__prev',
        },
        pagination: {
          el: '.du-testimonials__pagination',
          clickable: true,
        },
      });

    }
  };
})(Drupal);
