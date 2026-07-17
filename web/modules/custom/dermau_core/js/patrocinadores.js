(function (Drupal, once) {
  'use strict';

  Drupal.behaviors.dermauPatrocinadores = {
    attach(context) {
      const modal = document.getElementById(
        'du-patrocinador-modal'
      );

      const modalContent = modal
        ? modal.querySelector(
          '[data-patrocinador-modal-content]'
        )
        : null;

      let activeTrigger = null;
      let activeRequest = null;

      const closeModal = () => {
        if (!modal) {
          return;
        }

        if (activeRequest) {
          activeRequest.abort();
          activeRequest = null;
        }

        if (typeof modal.close === 'function' && modal.open) {
          modal.close();
        }
        else {
          modal.removeAttribute('open');
        }

        document.documentElement.classList.remove(
          'du-patrocinador-is-open'
        );

        document.body.classList.remove(
          'du-patrocinador-is-open'
        );

        if (activeTrigger) {
          activeTrigger.focus();
          activeTrigger = null;
        }
      };

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

      once(
        'dermau-patrocinador-open',
        '.js-patrocinador-open',
        context
      ).forEach((trigger) => {
        trigger.addEventListener('click', async () => {
          if (!modal || !modalContent) {
            return;
          }

          const url = trigger.dataset.patrocinadorUrl;

          if (!url) {
            return;
          }

          activeTrigger = trigger;

          modalContent.innerHTML = `
            <div class="du-patrocinador-modal__loading">
              <span class="du-patrocinador-modal__spinner"></span>
              <span>Cargando información...</span>
            </div>
          `;

          if (typeof modal.showModal === 'function') {
            modal.showModal();
          }
          else {
            modal.setAttribute('open', 'open');
          }

          document.documentElement.classList.add(
            'du-patrocinador-is-open'
          );

          document.body.classList.add(
            'du-patrocinador-is-open'
          );

          if (activeRequest) {
            activeRequest.abort();
          }

          activeRequest = new AbortController();

          try {
            const response = await fetch(url, {
              method: 'GET',
              credentials: 'same-origin',
              headers: {
                'X-Requested-With': 'XMLHttpRequest',
              },
              signal: activeRequest.signal,
            });

            if (!response.ok) {
              throw new Error(
                `HTTP ${response.status}`
              );
            }

            modalContent.innerHTML = await response.text();
          }
          catch (error) {
            if (error.name === 'AbortError') {
              return;
            }

            modalContent.innerHTML = `
              <div class="du-patrocinador-modal__error">
                No fue posible cargar la información.
              </div>
            `;
          }
          finally {
            activeRequest = null;
          }
        });
      });

      if (!modal) {
        return;
      }

      once(
        'dermau-patrocinador-modal',
        modal,
        context
      ).forEach(() => {
        modal
          .querySelectorAll('[data-patrocinador-close]')
          .forEach((button) => {
            button.addEventListener('click', closeModal);
          });

        modal.addEventListener('click', (event) => {
          if (event.target === modal) {
            closeModal();
          }
        });

        modal.addEventListener('cancel', (event) => {
          event.preventDefault();
          closeModal();
        });
      });
    },
  };
})(Drupal, once);
