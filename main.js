/* ============================================================
   main.js — Interazioni frontend Home Server Dashboard
   ============================================================ */

'use strict';

/* ── DOMContentLoaded ────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  initCardReveal();
  initCopyButtons();
  initPasswordHover();
});

/* ── Rivelazione card con stagger ────────────────────────── */
function initCardReveal() {
  const cards = document.querySelectorAll('.service-card');
  if (!cards.length) return;

  cards.forEach((card, i) => {
    setTimeout(() => {
      card.classList.add('is-visible');
    }, 60 + i * 55); // stagger 55ms per card
  });
}

/* ── Copia credenziali negli appunti ─────────────────────── */
function copyText(event, btn) {
  event.preventDefault(); // non aprire il link della card
  event.stopPropagation();

  const text = btn.dataset.copy ?? '';
  if (!text) return;

  navigator.clipboard.writeText(text)
    .then(() => {
      btn.classList.add('copied');
      showToast('Copiato!');
      setTimeout(() => btn.classList.remove('copied'), 2000);
    })
    .catch(() => {
      // Fallback per browser più datati
      const ta = document.createElement('textarea');
      ta.value = text;
      ta.style.cssText = 'position:fixed;opacity:0;pointer-events:none';
      document.body.appendChild(ta);
      ta.select();
      document.execCommand('copy');
      document.body.removeChild(ta);
      showToast('Copiato!');
    });
}

/* ── Toast notifica ──────────────────────────────────────── */
function showToast(message) {
  const toast = document.getElementById('copy-toast');
  if (!toast) return;

  toast.textContent = message;
  toast.classList.add('visible');

  clearTimeout(toast._timeout);
  toast._timeout = setTimeout(() => toast.classList.remove('visible'), 2200);
}

/* ── Rivelazione password al hover mobile (touch) ───────── */
function initPasswordHover() {
  // Su touch device, il hover CSS non funziona → toggle al tap sulla card
  const isTouchDevice = window.matchMedia('(hover: none)').matches;
  if (!isTouchDevice) return;

  document.querySelectorAll('.service-card').forEach(card => {
    card.addEventListener('click', e => {
      // Se ha cliccato su un pulsante copia → non fare nulla
      if (e.target.closest('.copy-btn')) return;

      // Se le credenziali sono già visibili → apri il link
      if (card.classList.contains('creds-open')) return;

      e.preventDefault();
      card.classList.toggle('creds-open');

      // Rimuovi da tutte le altre card
      document.querySelectorAll('.service-card').forEach(c => {
        if (c !== card) c.classList.remove('creds-open');
      });
    });
  });
}

/* ── Esportazione globale (usata inline negli HTML) ─────── */
window.copyText  = copyText;
window.showToast = showToast;
