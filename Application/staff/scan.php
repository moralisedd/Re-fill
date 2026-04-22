<?php
/**
 * Re-fill — Staff QR Scan Page
 * Camera scanning uses html5-qrcode (mebjas/html5-qrcode), which works via
 * getUserMedia + canvas internally — no BarcodeDetector or browser flags needed.
 * Manual token entry remains as a fallback and for the video demo.
 */
require_once __DIR__ . '/../includes/auth.php';
require_staff();

$page_title = 'Scan QR Code';
require_once __DIR__ . '/../includes/header.php';
?>

<div style="max-width:540px; margin:0 auto;">
    <h1>Scan customer QR</h1>
    <p>Point the camera at the customer's Re-fill QR code, or type the code manually.</p>

    <!-- Success/error message area — JS populates this after API response -->
    <div id="scan-result" role="alert" aria-live="assertive" style="min-height:2rem; margin:1rem 0;"></div>

    <!-- html5-qrcode mounts its own video element inside this div -->
    <div class="card" style="padding:1rem; text-align:center;">
        <div id="camera-view"
             style="width:100%; max-width:400px; margin:0 auto;"
             aria-label="Camera viewfinder for scanning QR codes"></div>
        <p id="camera-status" style="margin-top:.5rem; font-size:.85rem; color:var(--colour-text-muted);">
            Starting camera…
        </p>
    </div>

    <hr style="margin:1.5rem 0; border:none; border-top:1px solid var(--colour-border);">

    <h2 style="font-size:1rem;">Or enter the code manually</h2>
    <form id="manual-form" style="display:flex; gap:.75rem; margin-top:.75rem;" novalidate>
        <input type="text" id="manual-token" name="token"
               placeholder="Paste token value here"
               style="flex:1;"
               aria-label="Token value from QR code">
        <button type="submit" class="btn-primary">Validate</button>
    </form>

    <p style="margin-top:1.5rem;"><a href="<?= BASE_URL ?>/staff/dashboard.php">← Back to dashboard</a></p>
</div>

<!--
    html5-qrcode CDN — works on Chrome, Brave, Firefox, Safari and Edge without
    experimental flags. Uses getUserMedia + canvas, not BarcodeDetector.
-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
(function () {
  const API_URL  = '<?= BASE_URL ?>/api/validate.php';
  const resultEl = document.getElementById('scan-result');
  const statusEl = document.getElementById('camera-status');
  let   scanning = false;
  let   scanner  = null;

  // ── Helpers ────────────────────────────────────────────────────────────────
  function escHtml(str) {
    return String(str)
      .replace(/&/g,'&amp;').replace(/</g,'&lt;')
      .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  function showResult(data) {
    if (data.success) {
      resultEl.innerHTML =
        `<div class="alert alert-success">
           ✅ <strong>${escHtml(data.customer_name)}</strong> —
           +${data.points_awarded} point awarded!
           New balance: <strong>${data.points_balance}</strong>.
         </div>`;
      // Stop the scanner after a valid scan so the staff member sees the result
      if (scanner) scanner.stop().catch(() => {});
      statusEl.textContent = 'Scan complete. Refresh the page to scan again.';
    } else {
      resultEl.innerHTML =
        `<div class="alert alert-error">❌ ${escHtml(data.error)}</div>`;
      // Allow another attempt after a failed validation
      scanning = false;
    }
  }

  // ── Validate token via API ──────────────────────────────────────────────────
  async function validatePayload(jsonPayload) {
    if (scanning) return;
    scanning = true;

    let parsed;
    try {
      parsed = JSON.parse(jsonPayload);
    } catch {
      showResult({ success: false, error: 'Invalid QR code format.' });
      return;
    }

    try {
      const res  = await fetch(API_URL, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ token: parsed.token, nonce: parsed.nonce }),
      });
      const data = await res.json();
      showResult(data);
    } catch {
      showResult({ success: false, error: 'Network error. Please try again.' });
    }
  }

  // ── Manual form ─────────────────────────────────────────────────────────────
  document.getElementById('manual-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const raw = document.getElementById('manual-token').value.trim();
    if (!raw) return;
    // Manual entry — nonce is omitted; the server looks it up from the DB
    try {
      const res  = await fetch(API_URL, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ token: raw }),
      });
      const data = await res.json();
      showResult(data);
    } catch {
      showResult({ success: false, error: 'Network error. Please try again.' });
    }
  });

  // ── Camera scanning via html5-qrcode ────────────────────────────────────────
  // I switched from BarcodeDetector (Chromium-only, needs a flag on Windows) to
  // html5-qrcode because it works on Chrome, Brave, Firefox and Safari with no
  // config changes — it uses getUserMedia + canvas decoding internally.
  function startCamera() {
    if (typeof Html5Qrcode === 'undefined') {
      statusEl.textContent = 'QR scanner library failed to load — check your internet connection. Use manual entry below.';
      return;
    }

    scanner = new Html5Qrcode('camera-view', { verbose: false });

    Html5Qrcode.getCameras()
      .then(function (cameras) {
        if (!cameras || cameras.length === 0) {
          statusEl.textContent = 'No camera found. Use manual entry below.';
          return Promise.reject('no cameras');
        }

        // Prefer rear-facing camera on phones; on a laptop the only camera is used
        return scanner.start(
          { facingMode: 'environment' },
          {
            fps:   10,
            qrbox: { width: 250, height: 250 }
          },
          function (decodedText) {
            // Called every time a QR is decoded — scanning flag prevents
            // duplicate API calls while awaiting the server response
            validatePayload(decodedText);
          },
          function () {
            // Per-frame errors fire when no QR is in frame — this is normal, ignore
          }
        );
      })
      .then(function () {
        statusEl.textContent = 'Camera active — point at the QR code.';
      })
      .catch(function (err) {
        if (err === 'no cameras') return; // already handled above
        var msg = String(err);
        if (msg.toLowerCase().includes('permission') || msg.toLowerCase().includes('notallowed')) {
          statusEl.textContent = 'Camera permission denied — allow camera access in browser settings, then refresh.';
        } else {
          statusEl.textContent = 'Camera unavailable (' + msg + '). Use manual entry below.';
        }
      });
  }

  startCamera();
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
