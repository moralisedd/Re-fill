/**
 * Re-fill — Global JavaScript
 *
 * Shared utilities exposed on window.Refill so inline page scripts can access
 * them without polluting the global scope. Loaded in <head> so window.Refill
 * is always defined before any inline script runs.
 */

'use strict';

// QrTimer: IIFE keeps intervalId and secondsLeft private so no external script
// can accidentally clear the interval or corrupt the countdown state.
const QrTimer = (() => {
  let intervalId  = null;
  let secondsLeft = 0;

  function init(durationSeconds, expiresAtISO) {
    const countdownEl = document.getElementById('qr-countdown');
    const refreshBtn  = document.getElementById('refresh-qr');

    if (!countdownEl) return;

    // Calculate from the server expiry timestamp rather than durationSeconds
    // to correct for network latency and page render time.
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
        if (refreshBtn) refreshBtn.focus(); // save keyboard users a tab stop
      }
    }, 1000);
  }

  function stop() {
    if (intervalId) clearInterval(intervalId);
  }

  return { init, stop };
})();


// Dynamic live region: created once and reused across announcements.
// Clearing textContent before setting it again forces screen readers to
// re-announce even when the message text is unchanged.
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


// DOMContentLoaded wrapper required because app.js is loaded in <head>
// and the DOM elements don't exist until parsing is complete.
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
