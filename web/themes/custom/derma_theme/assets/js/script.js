const header = document.querySelector(".du-header");
const menuToggle = document.getElementById("duMenuToggle");
const navList = document.getElementById("duNavList");
const navOverlay = document.getElementById("duNavOverlay");

const openMenu = () => {
  header.setAttribute("data-open", "true");
  navList.classList.add("du-header__nav-list--open");
  navOverlay.classList.add("du-header__nav-overlay--active");
  document.body.style.overflow = "hidden"; // Evita scroll al estar abierto
};

const closeMenu = () => {
  header.setAttribute("data-open", "false");
  navList.classList.remove("du-header__nav-list--open");
  navOverlay.classList.remove("du-header__nav-overlay--active");
  document.body.style.overflow = "auto";
};

if (menuToggle) {
  menuToggle.addEventListener("click", openMenu);
  const menuClose = document.getElementById("duMenuClose");
  if (menuClose) menuClose.addEventListener("click", closeMenu);
}

if (navOverlay) navOverlay.addEventListener("click", closeMenu);


/* Swipers  sliders */

const duSwiperHero = new Swiper(".du-hero-swiper .swiper", {
  loop: false,
  speed: 800,
  autoplay: { delay: 5000, disableOnInteraction: false },
  pagination: { el: ".du-hero-swiper .swiper-pagination", clickable: true },
  effect: "fade",
  fadeEffect: { crossFade: true },
});

const duSwiperAgreement = new Swiper(".du-agreements .swiper", {
  loop: false,
  autoplay: { delay: 3000 },
  breakpoints: {
    320: {
      slidesPerView: 1,
      spaceBetween: 20,
      navigation: {
        nextEl: ".du-agreements .swiper-button-next",
        prevEl: ".du-agreements .swiper-button-prev",
      },
      pagination: { el: ".du-agreements .swiper-pagination", clickable: true },
    },
    992: {
      slidesPerView: 4,
      spaceBetween: 40,
      allowTouchMove: false,
    },
  },
});

const duSwiperTestimonials = new Swiper("#general-testimonial-swiper", {
  slidesPerView: 1,
  spaceBetween: 30,
  loop: false,
  autoplay: { delay: 5000, disableOnInteraction: false },
  pagination: { el: ".du-testimonials__pagination", clickable: true },
  navigation: {
    nextEl: ".du-testimonials__next",
    prevEl: ".du-testimonials__prev",
  },
  breakpoints: {
    992: { slidesPerView: 2, spaceBetween: 40 },
  },
});


const duSwiperExperts = new Swiper('.du-swiper-expert .swiper', {
  slidesPerView: 1,
  spaceBetween: 20,
  loop: false,
  navigation: {
    nextEl: '.du-swiper-expert .swiper-button-next',
    prevEl: '.du-swiper-expert .swiper-button-prev',
  },
  pagination: {
    el: '.du-swiper-expert .swiper-pagination',
    clickable: true,
    renderBullet: function (index, className) {
      let bullet = `<span class="${className}" data-swiper-slide-index="${index}">${index + 1}</span>`;
      /*let totalSlides = this.slides.length;
     if (index === 0)  bullet = `<span class="nav-label" onclick="duSwiperExperts.slideTo(0)" data-swiper-slide-index="${index}"> < </span>` + bullet;
      if (index === totalSlides - 1)  bullet = bullet + `<span class="nav-label" onclick="duSwiperExperts.slideTo(${totalSlides - 1})" data-swiper-slide-index="${index}"> > </span>`;*/
      return bullet;
    },
  },
  breakpoints: {
    1024: { slidesPerView: 3, spaceBetween: 30 }
  }
});


const duSwiperOferta = new Swiper(".du-swiper-oferta .swiper", {
  loop: false,
  autoplay: { delay: 3000 },
  breakpoints: {
    320: {
      slidesPerView: 1.2,
      spaceBetween: 20,
      pagination: { el: ".du-swiper-oferta .swiper-pagination", clickable: true },
    },
    992: {
      slidesPerView: 4,
      spaceBetween: 40,
      allowTouchMove: false,
    },
  },
});


/* FAQ Interacciones */
document.addEventListener("DOMContentLoaded", () => {
  // Tabs Desktop
  const tabBtns = document.querySelectorAll(".du-faq__tab-btn");
  const groups = document.querySelectorAll(".du-faq__group-wrapper");

  if (tabBtns.length > 0)
    tabBtns.forEach((btn) => {
      btn.addEventListener("click", () => {
        const target = btn.getAttribute("data-tab");
        tabBtns.forEach((b) => b.classList.remove("active"));
        groups.forEach((g) => {
          g.classList.remove("active");
          g.classList.remove("open"); // Reset en caso de venir de móvil
        });

        btn.classList.add("active");
        document.getElementById(target).classList.add("active");
      });
    });

  // Mobile Acordeón Categorías
  document.querySelectorAll(".du-faq__mobile-trigger").forEach((trigger) => {
    trigger.addEventListener("click", () => {
      trigger.parentElement.classList.toggle("is-open");
    });
  });

  // Preguntas Internas
  document.querySelectorAll(".du-accordion-header").forEach((header) => {
    header.addEventListener("click", () => {
      const item = header.parentElement;
      item.classList.toggle("is-open");
    });
  });
});

/* Acordeón General */
document.addEventListener('DOMContentLoaded', () => {
  const accordionItems = document.querySelectorAll('.du-accordion-block__item');
  if (accordionItems.length > 1)
    accordionItems.forEach(item => {
      const header = item.querySelector('.du-accordion-block__header');
      header.addEventListener('click', () => {
        const isOpen = item.classList.contains('is-open');
        accordionItems.forEach(i => i.classList.remove('is-open'));
        if (!isOpen) item.classList.add('is-open');
      });
    });
});

/* Panel Acordeón */
document.querySelectorAll('.du-panel-block__trigger').forEach(trigger => {
  trigger.addEventListener('click', () => {
    const parent = trigger.parentElement;
    const isOpen = parent.classList.contains('is-open');
    document.querySelectorAll('.du-panel-block__item').forEach(item => {
      item.classList.remove('is-open');
    });
    if (!isOpen) parent.classList.add('is-open');
  });
});


/* du-congreso__card-trigger  */
document.querySelectorAll('.du-congreso__card-trigger').forEach(trigger => {
  trigger.addEventListener('click', () => {
    const parent = trigger.parentElement.parentElement;
    const isOpen = parent.classList.contains('is-open');
    document.body.style.overflow = 'auto';
    document.querySelectorAll('.du-calendar__item-card').forEach(item => {
      item.classList.remove('is-open');
    });
    if (!isOpen) {
      parent.classList.add('is-open');
      document.body.style.overflow = 'hidden';
    }
  });
});



/* btn action form float */
document.addEventListener("DOMContentLoaded", () => {
  const btnChat = document.querySelector(".du-float-chat");
  const btnTitle = document.querySelector(".du-form-register__subtitle");
  const isMobile = window.innerWidth < 992;
  const toScroll = 150;
  if (!btnChat || isMobile) return;

  let lastScrollState = null; // null, "hidden", "shown"

  function formShow(isUser = false) {
    if (isMobile) return;
    const form = document.querySelector(".du-form-register__form");
    if (form && isUser) form.classList.toggle("max-h-0");
    else if (form && !isUser) {
      if (lastScrollState === "hidden") form.classList.add("max-h-0");
      else form.classList.remove("max-h-0");
    }
  }

  // Acción al hacer click (usuario)
  btnChat.addEventListener("click", (e) => {
    e.preventDefault();
    formShow(true);
  });

  btnTitle.addEventListener("click", () => {
    formShow(true);
  });

  // Mostrar/ocultar según scroll (automático)
  window.addEventListener("scroll", () => {
    if (isMobile) return;
    const scrollTop = window.scrollY || document.documentElement.scrollTop;
    if (scrollTop >= toScroll && lastScrollState !== "hidden") {
      btnChat.classList.add("enfasis");
      lastScrollState = "hidden";
      formShow(false);
    } else if (scrollTop < toScroll && lastScrollState !== "shown") {
      // mostrar solo una vez
      btnChat.classList.remove("enfasis");
      lastScrollState = "shown";
      formShow(false);
    }
  });
});

(function () {

  function initCalendarTabs() {
    const menuLinks = document.querySelectorAll('.du-bar-menu__link');
    const secciones = document.querySelectorAll('.du-congreso-section-calendar');

    if (!menuLinks.length || !secciones.length) return;

    function activarDia(diaId) {
      // Actualizar estado activo en el menú
      menuLinks.forEach(function (link) {
        const item = link.closest('.du-bar-menu__item');
        if (link.getAttribute('href') === '#' + diaId) {
          item.classList.add('active');
        } else {
          item.classList.remove('active');
        }
      });

      // Mostrar solo la sección del día seleccionado
      secciones.forEach(function (seccion) {
        if (seccion.id === diaId) {
          seccion.style.display = 'block';
        } else {
          seccion.style.display = 'none';
        }
      });
    }

    // Activar el primer día por defecto
    const primerDia = secciones[0].id;
    activarDia(primerDia);

    // Escuchar clicks en el menú
    menuLinks.forEach(function (link) {
      link.addEventListener('click', function (e) {
        e.preventDefault();
        const diaId = this.getAttribute('href').replace('#', '');
        activarDia(diaId);
      });
    });
  }

  function deduplicarSelect(selectId) {
    const select = document.getElementById(selectId);
    if (!select) return;

    const vistas = new Set();
    const opciones = select.querySelectorAll('option');

    opciones.forEach(function (opcion) {
      const valor = opcion.value;
      if (valor === '') return; // dejar siempre la opción vacía

      if (vistas.has(valor)) {
        opcion.remove();
      } else {
        vistas.add(valor);
      }
    });
  }

  function initFiltrosBusqueda() {
    const selectSimposio     = document.getElementById('simposio');
    const selectTema         = document.getElementById('tema');
    const selectSala         = document.getElementById('sala');
    const selectConferencista = document.getElementById('conferencista');
    const items              = document.querySelectorAll('.du-panel-block__item');

    if (!items.length) return;

    function filtrar() {
      const valorSimposio      = selectSimposio ? selectSimposio.value : '';
      const valorTema          = selectTema ? selectTema.value : '';
      const valorSala          = selectSala ? selectSala.value : '';
      const valorConferencista = selectConferencista ? selectConferencista.value : '';

      items.forEach(function (item) {
        const tipo          = item.dataset.tipo || '';
        const tematica      = item.dataset.tematica || '';
        const sala          = item.dataset.sala || '';
        const conferencistas = item.dataset.conferencistas || '';

        const matchSimposio      = !valorSimposio      || tipo === valorSimposio;
        const matchTema          = !valorTema          || tematica === valorTema;
        const matchSala          = !valorSala          || sala === valorSala;
        const matchConferencista = !valorConferencista || conferencistas.split('|').some(function (c) {
          return c.trim() === valorConferencista.trim();
        });

        if (matchSimposio && matchTema && matchSala && matchConferencista) {
          item.style.display = 'block';
        } else {
          item.style.display = 'none';
        }
      });

      mostrarMensajeVacio();
    }

    function mostrarMensajeVacio() {
      const wrapper    = document.querySelector('.du-panel-block__wrapper');
      if (!wrapper) return;

      const existente  = wrapper.querySelector('.du-panel-block__empty');
      const hayVisibles = Array.from(items).some(function (item) {
        return item.style.display !== 'none';
      });

      if (!hayVisibles) {
        if (!existente) {
          const msg = document.createElement('p');
          msg.className = 'du-panel-block__empty';
          msg.textContent = 'No se encontraron eventos con los filtros seleccionados.';
          wrapper.appendChild(msg);
        }
      } else {
        if (existente) existente.remove();
      }
    }

    [selectSimposio, selectTema, selectSala, selectConferencista].forEach(function (select) {
      if (select) select.addEventListener('change', filtrar);
    });
  }

  function initFiltrosAgenda() {
    const selectedRoom = document.getElementById('sala-cal');
    const selectedHour = document.getElementById('horario-cal');
    const items_cal = document.querySelectorAll('.du-calendar__item-card');

    if (!items_cal.length) return;

    function tieneEvento(item) {
      const card = item.querySelector('.du-congreso__card');
      // Si no existe el card, o existe pero no tiene hijos, está vacío
      return !!(card && card.children.length > 0);
    }

    function filtrar_agenda() {
      const valorSala = selectedRoom ? selectedRoom.value : '';
      const valorHora = selectedHour ? selectedHour.value : '';

      items_cal.forEach(function (item) {
        const sala = item.dataset.sala || '';
        const hora = item.dataset.hora || '';

        const matchHora = !valorHora || hora === valorHora;
        const matchSala = !valorSala || sala === valorSala;
        const conEvento = tieneEvento(item);

        if (matchSala && matchHora && conEvento) {
          item.style.display = 'block';
        } else {
          item.style.display = 'none';
        }
      });

      mensajeVacio();
    }

    function mensajeVacio() {
      const wrapper = document.querySelector('.du-congreso-section-calendar__wrapper');
      if (!wrapper) return;

      const existente = wrapper.querySelector('.du-panel-block__empty');
      const hayVisibles = Array.from(items_cal).some(function (item) {
        return item.style.display !== 'none';
      });

      if (!hayVisibles) {
        if (!existente) {
          const msg = document.createElement('p');
          msg.className = 'du-panel-block__empty';
          msg.textContent = 'No se encontraron eventos con los filtros seleccionados.';
          wrapper.appendChild(msg);
        }
      } else {
        if (existente) existente.remove();
      }
    }

    [selectedRoom, selectedHour].forEach(function (select) {
      if (select) select.addEventListener('change', filtrar_agenda);
    });
  }


  document.addEventListener('DOMContentLoaded', function () {
    deduplicarSelect('simposio');
    deduplicarSelect('tema');
    deduplicarSelect('sala');
    deduplicarSelect('conferencista');
    deduplicarSelect('sala-cal');
    deduplicarSelect('horario-cal');
    initFiltrosBusqueda();
    initFiltrosAgenda();
    initCalendarTabs();
  });
})();