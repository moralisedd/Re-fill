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

<div class="page-qr">
    <h1>Your Re-fill QR Code</h1>
    <p>Show this to the barista when you order with your reusable cup.</p>

    <div class="card qr-wrapper qr-card">
        <!-- QR code rendered here by qrcodejs -->
        <div id="qr-container" aria-label="Your QR code for Re-fill loyalty scan" role="img"></div>

        <div class="qr-countdown" id="qr-countdown" aria-live="off" aria-atomic="true">
            <?= QR_TOKEN_TTL_SECONDS ?>s
        </div>

        <p class="qr-refresh-text">
            This code refreshes every 60 seconds to keep your account secure.
        </p>

        <button id="refresh-qr" class="btn-secondary" onclick="location.reload()">
            ↻ Refresh now
        </button>
    </div>

    <!-- Short code for manual staff entry — much easier to type than the full token -->
    <div class="short-code-section">
        <p class="short-code-label">Or give staff this code to enter manually:</p>
        <div class="short-code-box">
            <span id="short-code-display"
                  class="short-code-value"
                  aria-label="Short entry code">
                <?= htmlspecialchars($token_data['short_code']) ?>
            </span>
            <button class="btn-secondary btn-copy"
                    aria-label="Copy short code"
                    onclick="navigator.clipboard.writeText('<?= htmlspecialchars($token_data['short_code']) ?>').then(()=>{this.textContent='Copied!';setTimeout(()=>this.textContent='Copy',1500)}).catch(()=>{this.textContent='Copy failed'});">
                Copy
            </button>
        </div>
    </div>
</div>

<script>
(function () {
  const payload   = <?= json_encode($qr_payload) ?>;
  const expiresAt = '<?= $expires_iso ?>';  // ISO 8601 — parses correctly in all browsers
  const container = document.getElementById('qr-container');

  function showError(msg) {
    container.innerHTML = '<p class="qr-error">' + msg + '</p>';
  }

  if (typeof QRCode === 'undefined') {
    showError('QR code could not be generated. Please refresh the page or contact support.');
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
    } catch (err) {
      // qrcodejs throws if text is empty or null, so I wrap the constructor
      showError('QR code generation failed. Please refresh the page.');
    }
  }

  // Start countdown — runs regardless of whether QR rendered
  window.Refill.QrTimer.init(<?= QR_TOKEN_TTL_SECONDS ?>, expiresAt);
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
