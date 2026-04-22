/**
 * Re-fill — Global JavaScript
 *
 * I put everything that's needed across multiple pages here and expose it
 * on window.Refill so inline page scripts can reference it without globals.
 * app.js is loaded in <head> so window.Refill is always defined before any
 * inline script runs.
 */

'use strict';

// ── QR Countdown Timer ───────────────────────────────────────────────────────
// I use a module pattern (IIFE) so intervalId and secondsLeft are private —
// no other script can accidentally clear the interval or reset the counter.

const QrTimer = (() => {
  let intervalId  = null;
  let secondsLeft = 0;

  function init(durationSeconds, expiresAtISO) {
    const countdownEl = document.getElementById('qr-countdown');
    const refreshBtn  = document.getElementById('refresh-qr');

    if (!countdownEl) return;

    // I calculate from the server's expiry timestamp rather than counting down
    // from durationSeconds — this corrects for network latency and page render time.
    const expiresAt = new Date(expiresAtISO).getTime();
    secondsLeft = Math.max(0, Math.round((expiresAt - Date.now()) / 1000));

    intervalId = setInterval(() => {
      secondsLeft = Math.max(0, secondsLeft - 1);
      countdownEl.textContent = secondsLeft + 's';

      // Visual warning — turn the countdown red in the last 10 seconds
      if (secondsLeft <= 10) {
        countdownEl.classList.add('expiring');
      }

      // Periodic screen reader announcement — every 15 seconds feels non-intrusive
      if (secondsLeft % 15 === 0 && secondsLeft > 0) {
        announceToScreenReader(`QR code refreshes in ${secondsLeft} seconds`);
      }

      if (secondsLeft === 0) {
        clearInterval(intervalId);
        countdownEl.textContent = 'Expired';
        announceToScreenReader('QR code has expired. Please refresh.');
        // Move focus to the refresh button so keyboard users don't have to tab to it
        if (refreshBtn) refreshBtn.focus();
      }
    }, 1000);
  }

  function stop() {
    if (intervalId) clearInterval(intervalId);
  }

  return { init, stop };
})();


// ── Screen reader live region ────────────────────────────────────────────────
// I create the live region dynamically rather than baking it into every page's HTML.
// Clearing textContent before setting it again forces screen readers to re-announce
// even if the message is the same as before.
function announceToScreenReader(message) {
  let liveRegion = document.getElementById('sr-live');

  if (!liveRegion) {
    liveRegion = document.createElement('div');
    liveRegion.id = 'sr-live';
    liveRegion.setAttribute('aria-live', 'polite');
    liveRegion.setAttribute('aria-atomic', 'true');
    liveRegion.className = 'sr-only';
    document.body.appendChild(liveRegion);
  }

  liveRegion.textContent = '';
  // 50ms gap ensures screen readers detect the content change as a new announcement
  setTimeout(() => { liveRegion.textContent = message; }, 50);
}


// ── Alert auto-dismiss ───────────────────────────────────────────────────────
// I wrap this in DOMContentLoaded because app.js is in <head> — the DOM
// isn't ready until the event fires, so I can't query elements immediately.
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('[data-dismiss="5000"]').forEach(el => {
    setTimeout(() => {
      el.style.transition = 'opacity .4s';
      el.style.opacity    = '0';
      setTimeout(() => el.remove(), 400);
    }, 5000);
  });
});


// ── Form validation helpers ──────────────────────────────────────────────────
function showFieldError(fieldId, message) {
  const field    = document.getElementById(fieldId);
  const errorId  = fieldId + '-error';
  let errorEl    = document.getElementById(errorId);

  if (!errorEl) {
    errorEl = document.createElement('span');
    errorEl.id        = errorId;
    errorEl.className = 'form-error';
    errorEl.setAttribute('role', 'alert');
    field.parentNode.appendChild(errorEl);
  }

  errorEl.textContent = message;
  field.setAttribute('aria-describedby', errorId);
  field.setAttribute('aria-invalid', 'true');
  field.classList.add('field-error');
}

function clearFieldError(fieldId) {
  const field    = document.getElementById(fieldId);
  const errorId  = fieldId + '-error';
  const errorEl  = document.getElementById(errorId);

  if (errorEl) errorEl.remove();
  if (field) {
    field.removeAttribute('aria-describedby');
    field.removeAttribute('aria-invalid');
    field.classList.remove('field-error');
  }
}


// ── Expose to page scripts ───────────────────────────────────────────────────
window.Refill = {
  QrTimer,
  announceToScreenReader,
  showFieldError,
  clearFieldError,
};
