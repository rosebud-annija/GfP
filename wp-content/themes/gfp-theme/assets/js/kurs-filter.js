/**
 * kurs-filter.js
 * GfP — Client-seitiger Fachbereich-Filter auf der Kursübersicht
 * Kein Framework, kein Build-Step, läuft direkt im Browser.
 */

(function () {
  'use strict';

  const grid    = document.getElementById('kurse-grid');
  const buttons = document.querySelectorAll('.kurs-filter__btn');

  if (!grid || !buttons.length) return;

  const cards = Array.from(grid.querySelectorAll('.kurs-card'));

  // ── Filter anwenden ─────────────────────────────────────────────────────────

  function filterKurse(fachbereich) {
    cards.forEach(function (card) {
      const fb = card.dataset.fachbereich || '';

      if (fachbereich === 'all' || fb === fachbereich) {
        card.style.display = '';
        // Kurze Einblend-Animation
        card.style.opacity = '0';
        card.style.transform = 'translateY(8px)';
        requestAnimationFrame(function () {
          requestAnimationFrame(function () {
            card.style.transition = 'opacity 0.2s, transform 0.2s';
            card.style.opacity    = '1';
            card.style.transform  = 'translateY(0)';
          });
        });
      } else {
        card.style.display = 'none';
      }
    });
  }

  // ── Button-Styles aktualisieren ─────────────────────────────────────────────

  var inaktivColor  = 'rgba(0,0,0,0.45)';
  var inaktivBorder = 'rgba(0,0,0,0.2)';

  function updateButtons(aktiverFilter) {
    buttons.forEach(function (btn) {
      var filter   = btn.dataset.filter;
      var farbeHex = btn.dataset.farbeHex || '';
      var istAktiv = filter === aktiverFilter;

      btn.classList.toggle('is-active', istAktiv);

      if (filter === 'all') {
        btn.style.background  = istAktiv ? '#111'        : 'transparent';
        btn.style.color       = istAktiv ? '#fff'        : inaktivColor;
        btn.style.borderColor = istAktiv ? '#111'        : inaktivBorder;
      } else {
        btn.style.background  = istAktiv ? farbeHex      : 'transparent';
        btn.style.color       = istAktiv ? '#111'        : inaktivColor;
        btn.style.borderColor = istAktiv ? farbeHex      : inaktivBorder;
      }
    });
  }

  // ── Event-Listener ──────────────────────────────────────────────────────────

  buttons.forEach(function (btn) {
    btn.addEventListener('click', function () {
      const filter = btn.dataset.filter;
      filterKurse(filter);
      updateButtons(filter);

      // URL-Hash für Direktlinks (optional, ohne Seitenreload)
      if (history.replaceState) {
        const hash = filter === 'all' ? '' : '#' + encodeURIComponent(filter);
        history.replaceState(null, '', window.location.pathname + hash);
      }
    });
  });

  // ── Hash beim Laden auswerten (z.B. Link von Fachbereich-Seite) ─────────────

  (function initFromHash() {
    const hash = decodeURIComponent(window.location.hash.replace('#', ''));
    if (!hash) return;

    const matchingBtn = Array.from(buttons).find(function (b) {
      return b.dataset.filter === hash;
    });
    if (matchingBtn) {
      filterKurse(hash);
      updateButtons(hash);
    }
  })();

})();
