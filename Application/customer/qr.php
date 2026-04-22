<?php
/**
 * Re-fill — Customer QR Code Page
 * Generates a dynamic 60-second token using the CPM model.
 * The QR code encodes a JSON payload: { token, nonce } which
 * the staff scanning page sends to the API for validation.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/qr.php';
require_customer();

$token_data = generate_qr_token((int)$_SESSION['user_id']);

// Build the QR payload — the staff app will POST this to /api/validate.php
$qr_payload = json_encode([
    'token' => $token_data['token_value'],
    'nonce' => $token_data['nonce'],
], JSON_THROW_ON_ERROR);

// Convert MySQL datetime to ISO 8601 so new Date() parses correctly in all browsers
$expires_iso = gmdate('Y-m-d\TH:i:s\Z', strtotime($token_data['expires_at']));

$page_title  = 'My QR Code';
$load_qr_lib = true;
require_once __DIR__ . '/../includes/header.php';
?>

<div style="max-width:480px; margin:0 auto; text-align:center;">
    <h1>Your Re-fill QR Code</h1>
    <p>Show this to the barista when you order with your reusable cup.</p>

    <div class="card qr-wrapper" style="margin-top:1.5rem;">
        <!-- QR code rendered here by qrcodejs -->
        <div id="qr-container" aria-label="Your QR code for Re-fill loyalty scan" role="img"></div>

        <div class="qr-countdown" id="qr-countdown" aria-live="off" aria-atomic="true">
            <?= QR_TOKEN_TTL_SECONDS ?>s
        </div>

        <p style="color:var(--colour-text-muted); font-size:.875rem;">
            This code refreshes every 60 seconds to keep your account secure.
        </p>

        <button id="refresh-qr" class="btn-secondary" onclick="location.reload()">
            ↻ Refresh now
        </button>
    </div>

    <!-- Demo helper: lets me copy the token value for manual entry on the staff scan page -->
    <details style="margin-top:1rem; text-align:left; background:var(--colour-bg-alt,#1f2937); border:1px solid var(--colour-border); border-radius:var(--radius); padding:.75rem 1rem;">
        <summary style="cursor:pointer; font-size:.85rem; color:var(--colour-text-muted);">
            🔧 Demo: show token for manual entry
        </summary>
        <p style="font-size:.8rem; color:var(--colour-text-muted); margin:.5rem 0 .25rem;">
            Staff can paste this token into the "Or enter the code manually" box on the scan page.
        </p>
        <div style="display:flex; gap:.5rem; align-items:center; margin-top:.5rem;">
            <input id="demo-token-display" type="text" readonly
                   style="flex:1; font-family:var(--font-mono,monospace); font-size:.75rem; padding:.4rem;"
                   value="<?= htmlspecialchars($token_data['token_value']) ?>"
                   aria-label="Token value for manual entry">
            <button class="btn-secondary" style="padding:.4rem .75rem; font-size:.8rem;"
                    onclick="var el=document.getElementById('demo-token-display');el.select();document.execCommand('copy');this.textContent='Copied!';setTimeout(()=>this.textContent='Copy',1500);">
                Copy
            </button>
        </div>
    </details>
</div>

<script>
(function () {
  const payload   = <?= json_encode($qr_payload) ?>;
  const expiresAt = '<?= $expires_iso ?>';  // ISO 8601 — parses correctly in all browsers
  const container = document.getElementById('qr-container');

  // I log these so I can debug via F12 Console if the QR doesn't render
  console.log('[Re-fill QR] payload length:', payload.length);
  console.log('[Re-fill QR] expiresAt:', expiresAt);
  console.log('[Re-fill QR] QRCode available:', typeof QRCode !== 'undefined');

  function showError(msg) {
    container.innerHTML =
      '<p style="color:var(--colour-error,#ef4444);padding:1rem;">' + msg + '</p>';
  }

  if (typeof QRCode === 'undefined') {
    showError('QR library failed to load. Copy ' +
      '<code>assets/js/qrcode.min.js</code> to ' +
      '<code>C:\\xampp\\htdocs\\refill\\assets\\js\\</code> and refresh.');
  } else {
    try {
      new QRCode(container, {
        text:         payload,
        width:        280,
        height:       280,
        colorDark:    '#111827',
        colorLight:   '#ffffff',
        correctLevel: QRCode.CorrectLevel.M
      });
      console.log('[Re-fill QR] QR code rendered successfully.');
    } catch (err) {
      // I wrap this in try/catch because qrcodejs throws on empty/null text
      console.error('[Re-fill QR] QRCode constructor threw:', err);
      showError('QR code generation failed: ' + err.message);
    }
  }

  // Start countdown — runs regardless of whether QR rendered
  window.Refill.QrTimer.init(<?= QR_TOKEN_TTL_SECONDS ?>, expiresAt);
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
