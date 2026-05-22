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

  // Erkennt ob die Seite einen hellen Hintergrund hat (Kursübersicht = light)
  var isLightBg = document.body.classList.contains('post-type-archive-kurs');
  var inaktivColor      = isLightBg ? 'rgba(0,0,0,0.55)'   : 'rgba(255,255,255,0.6)';
  var inaktivBorder     = isLightBg ? 'rgba(0,0,0,0.2)'    : 'rgba(255,255,255,0.2)';
  var allAktivBg        = isLightBg ? '#111'                : '#fff';
  var allAktivColor     = isLightBg ? '#fff'                : '#111';

  function updateButtons(aktiverFilter) {
    buttons.forEach(function (btn) {
      var filter   = btn.dataset.filter;
      var farbeHex = btn.dataset.farbeHex || '';
      var istAktiv = filter === aktiverFilter;

      btn.classList.toggle('is-active', istAktiv);

      if (filter === 'all') {
        // Alle-Button
        btn.style.background   = istAktiv ? allAktivBg    : 'transparent';
        btn.style.color        = istAktiv ? allAktivColor  : inaktivColor;
        btn.style.borderColor  = istAktiv ? allAktivBg     : inaktivBorder;
      } else {
        // Fachbereich-Button: Fachbereich-Farbe wenn aktiv
        btn.style.background   = istAktiv ? farbeHex       : 'transparent';
        btn.style.color        = istAktiv ? '#111'         : inaktivColor;
        btn.style.borderColor  = istAktiv ? farbeHex       : inaktivBorder;
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
