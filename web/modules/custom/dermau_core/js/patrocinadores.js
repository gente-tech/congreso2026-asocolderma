(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.dermauPatrocinadoresGrid = {
    attach(context) {
      once(
        'dermau-patrocinadores-search',
        '#du-patrocinadores-search',
        context
      ).forEach((searchInput) => {
        const cards = document.querySelectorAll(
          '[data-patrocinador-card]'
        );

        const emptyMessage = document.querySelector(
          '[data-patrocinadores-empty]'
        );

        searchInput.addEventListener('input', () => {
          const query = searchInput.value
            .trim()
            .toLocaleLowerCase('es');

          let visibleCards = 0;

          cards.forEach((card) => {
            const name = (
              card.dataset.patrocinadorName || ''
            ).toLocaleLowerCase('es');

            const visible = !query || name.includes(query);

            card.hidden = !visible;

            if (visible) {
              visibleCards += 1;
            }
          });

          if (emptyMessage) {
            emptyMessage.hidden = visibleCards > 0;
          }
        });
      });
    },
  };
})(Drupal, once);
