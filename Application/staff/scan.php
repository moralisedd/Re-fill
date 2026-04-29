<?php
/**
 * Re-fill — Staff QR Scan Page
 *
 * JS is split into two independent scripts so the camera can never block
 * manual entry:
 *   1. Camera script — auto-loads html5-qrcode CDN on page load and asks for
 *      camera permission immediately, matching the original UX.
 *   2. Manual form script — completely self-contained; works regardless of
 *      CDN availability, camera permission, or any camera-side errors.
 */
require_once __DIR__ . '/../includes/auth.php';
require_staff();

$page_title = 'Scan QR Code';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-scan">
    <h1>Scan customer QR</h1>
    <p class="page-meta">
        Point the camera at the customer's QR code, or enter their short code manually below.
    </p>

    <!-- Result area — populated by JS after API call -->
    <div id="scan-result" class="scan-result" role="alert" aria-live="assertive"></div>

    <!-- Camera viewfinder (shown above manual entry) -->
    <div class="card card--compact mb-3">
        <div id="camera-view"
             class="camera-view"
             aria-label="Camera viewfinder for scanning QR codes"></div>
        <p id="camera-status" class="camera-status">Starting camera…</p>
    </div>

    <hr class="scan-divider">

    <!-- Manual short code entry -->
    <div class="card">
        <h2 class="card-subheading">Or enter the code manually</h2>
        <form id="manual-form" class="d-flex gap-2" novalidate>
            <input type="text" id="manual-token" name="token"
                   placeholder="e.g. 9CB9FA"
                   class="token-input"
                   autocomplete="off"
                   aria-label="Customer short code or token">
            <button type="submit" class="btn-primary" id="manual-btn">Validate</button>
        </form>
        <p class="scan-hint">
            The short code is shown beneath the customer's QR on their screen.
        </p>
    </div>

    <p class="page-back">
        <a href="<?= BASE_URL ?>/staff/dashboard.php">← Back to dashboard</a>
    </p>
</div>

<!-- Script 1: Camera — CDN injected dynamically so a Brave Shields block
     never prevents the manual form below from working. -->
<script>
(function () {
  'use strict';

  var VALIDATE_URL = '<?= BASE_URL ?>/api/validate.php';
  var REDEEM_URL   = '<?= BASE_URL ?>/api/redeem_qr.php';
  var statusEl     = document.getElementById('camera-status');
  var scanning     = false;
  var scanner      = null;

  // Route camera-decoded QR payload to validate (earn) or redeem endpoint
  function validatePayload(jsonPayload) {
    if (scanning) return;
    scanning = true;

    var parsed;
    try {
      parsed = JSON.parse(jsonPayload);
    } catch (err) {
      if (window.Refill && window.Refill.showResult) {
        window.Refill.showResult({ success: false, error: 'Invalid QR code format.' }, 'earn');
      }
      scanning = false;
      return;
    }

    var url, body, action;
    if (parsed.action === 'redeem') {
      url    = REDEEM_URL;
      action = 'redeem';
      body   = JSON.stringify({
        user_id:   parsed.user_id,
        reward_id: parsed.reward_id,
        expires:   parsed.expires,
        sig:       parsed.sig,
      });
    } else {
      url    = VALIDATE_URL;
      action = 'earn';
      body   = JSON.stringify({ token: parsed.token, nonce: parsed.nonce });
    }

    fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: body })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (window.Refill && window.Refill.showResult) {
          window.Refill.showResult(data, action);
        }
        if (data.success && scanner) {
          scanner.stop().catch(function () {});
          statusEl.textContent = 'Scan complete. Refresh the page to scan again.';
        } else {
          scanning = false;
        }
      })
      .catch(function () {
        if (window.Refill && window.Refill.showResult) {
          window.Refill.showResult({ success: false, error: 'Network error. Please try again.' }, action);
        }
        scanning = false;
      });
  }

  // Initialise html5-qrcode once the CDN script has loaded
  function initCamera() {
    if (typeof Html5Qrcode === 'undefined') {
      statusEl.textContent = 'Camera library failed to load. Use manual entry below.';
      return;
    }

    scanner = new Html5Qrcode('camera-view', { verbose: false });

    Html5Qrcode.getCameras()
      .then(function (cameras) {
        if (!cameras || cameras.length === 0) {
          statusEl.textContent = 'No camera found. Use manual entry below.';
          return Promise.reject('no cameras');
        }
        // Prefer rear-facing on mobile; falls back to whatever is available
        return scanner.start(
          { facingMode: 'environment' },
          { fps: 10, qrbox: { width: 250, height: 250 } },
          function (decodedText) { validatePayload(decodedText); },
          function () { /* per-frame misses are normal — ignore */ }
        );
      })
      .then(function () {
        statusEl.textContent = 'Camera active — point at the customer\'s QR code.';
      })
      .catch(function (err) {
        if (err === 'no cameras') return;
        var msg = String(err).toLowerCase();
        if (msg.includes('permission') || msg.includes('notallowed')) {
          statusEl.textContent = 'Camera permission denied. Allow access in your browser settings, or use manual entry below.';
        } else {
          statusEl.textContent = 'Camera unavailable. Use manual entry below.';
        }
      });
  }

  // I load the CDN dynamically rather than with a static <script src> so that
  // if it's blocked (e.g. Brave Shields), the onerror fires here only and
  // never interferes with the manual form script below.
  var script       = document.createElement('script');
  script.src       = 'https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js';
  script.crossOrigin = 'anonymous';
  script.onload    = function () { initCamera(); };
  script.onerror   = function () {
    statusEl.textContent = 'Camera library blocked or unavailable. Use manual entry below.';
  };
  document.head.appendChild(script);

})();
</script>

<!-- Script 2: Manual form — standalone, zero dependency on the camera script.
     window.Refill.showResult is defined here; the camera script calls it too. -->
<script>
(function () {
  'use strict';

  var VALIDATE_URL = '<?= BASE_URL ?>/api/validate.php';
  var resultEl     = document.getElementById('scan-result');
  var form         = document.getElementById('manual-form');
  var input        = document.getElementById('manual-token');
  var btn          = document.getElementById('manual-btn');

  function escHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;')
      .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  // Shared result renderer — also used by the camera script via window.Refill
  window.Refill = window.Refill || {};
  window.Refill.showResult = function (data, action) {
    if (data.success) {
      if (action === 'redeem') {
        resultEl.innerHTML =
          '<div class="alert alert-success">' +
          '✅ Reward redeemed! <strong>' + escHtml(data.reward_name) + '</strong> — ' +
          data.points_used + ' point' + (data.points_used !== 1 ? 's' : '') + ' deducted. ' +
          'New balance: <strong>' + data.new_balance + '</strong>.' +
          '</div>';
      } else {
        resultEl.innerHTML =
          '<div class="alert alert-success">' +
          '✅ <strong>' + escHtml(data.customer_name) + '</strong> — ' +
          '+' + data.points_awarded + ' point awarded! ' +
          'New balance: <strong>' + data.points_balance + '</strong>.' +
          '</div>';
      }
    } else {
      resultEl.innerHTML =
        '<div class="alert alert-error">❌ ' + escHtml(data.error) + '</div>';
    }
  };

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    var raw = input.value.trim();
    if (!raw) { input.focus(); return; }

    // Disable button during the request — prevents double-submits
    btn.disabled    = true;
    btn.textContent = 'Validating…';
    resultEl.innerHTML = '';

    // Short codes (≤8 chars) and full tokens both go to validate.php —
    // the server detects the format and handles each case accordingly.
    fetch(VALIDATE_URL, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ token: raw }),
    })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        window.Refill.showResult(data, 'earn');
        if (data.success) { input.value = ''; }
      })
      .catch(function () {
        window.Refill.showResult(
          { success: false, error: 'Network error. Check your connection and try again.' },
          'earn'
        );
      })
      .finally(function () {
        btn.disabled    = false;
        btn.textContent = 'Validate';
      });
  });

})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
