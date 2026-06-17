(function (Drupal) {
  'use strict';

  const FLAG_CDN = 'https://flagcdn.com';

  function isoToEmoji(code) {
    return code.toUpperCase().split('').map(
      char => String.fromCodePoint(char.charCodeAt(0) + 127397)
    ).join('');
  }

  function renderFlags(context) {
    const elements = context.querySelectorAll('[class*="du-icon__flag-"]');

    elements.forEach(function (el) {
      if (el.dataset.flagRendered) return;
      el.dataset.flagRendered = 'true';

      const flagClass = Array.from(el.classList).find(
        cls => cls.startsWith('du-icon__flag-')
      );
      if (!flagClass) return;

      const isoCode = flagClass.replace('du-icon__flag-', '').toLowerCase();
      if (!isoCode) return;

      const img = document.createElement('img');

      // Usamos 40x30 del CDN (suficiente resolución para escalar a cualquier tamaño pequeño)
      img.src = `${FLAG_CDN}/40x30/${isoCode}.png`;
      img.alt = isoCode.toUpperCase();
      img.title = isoCode.toUpperCase();

      // Replica exactamente tu CSS actual
      img.style.cssText = `
        display: inline-block;
        width: 1em;
        height: 1em;
        object-fit: cover;
        object-position: center;
        margin-top: 2px;
        vertical-align: middle;
        border-radius: 25px;
      `;

      img.onerror = function () {
        el.textContent = isoToEmoji(isoCode);
      };

      // Limpia el contenido anterior del elemento (el fondo CSS ya no aplica)
      el.style.backgroundImage = 'none';
      el.appendChild(img);
    });
  }

  Drupal.behaviors.countryFlags = {
    attach: function (context) {
      renderFlags(context);
    }
  };

})(Drupal);