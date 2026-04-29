<?php
/**
 * Re-fill — Rewards Catalogue
 *
 * Cards flip on tap/click (CSS 3D transform) to reveal a QR code on the back.
 * Staff scan that QR to trigger the redemption flow — the payload is HMAC-signed
 * server-side so a customer cannot forge or tamper with the token.
 *
 * I use Bootstrap 5's row/col grid for the card layout and keep custom CSS only
 * for the 3D flip mechanics, which Bootstrap doesn't provide natively.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';
require_customer();

$pdo = get_db();
$uid = (int)$_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT user_id, points_balance, full_name FROM users WHERE user_id = ?');
$stmt->execute([$uid]);
$user    = $stmt->fetch();
$balance = (int)$user['points_balance'];

// Only show active rewards, cheapest first
$rewards = $pdo->query('SELECT * FROM rewards WHERE is_active = 1 ORDER BY points_required ASC')->fetchAll();

// Pre-compute HMAC-signed payloads for each redeemable reward.
// The expiry is 10 minutes — long enough for the customer to show the QR and
// the barista to scan it, but short enough to limit replay risk.
$redemption_ttl = 600; // 10 minutes in seconds
$signed_payloads = [];
foreach ($rewards as $r) {
    if ($balance >= (int)$r['points_required']) {
        $expires = time() + $redemption_ttl;
        $sig     = hash_hmac(
            'sha256',
            $uid . '|' . (int)$r['reward_id'] . '|' . $expires,
            REDEMPTION_SECRET
        );
        $signed_payloads[$r['reward_id']] = json_encode([
            'action'    => 'redeem',
            'user_id'   => $uid,
            'reward_id' => (int)$r['reward_id'],
            'expires'   => $expires,
            'sig'       => $sig,
        ], JSON_THROW_ON_ERROR);
    }
}

$page_title  = 'Rewards';
$load_qr_lib = true;
require_once __DIR__ . '/../includes/header.php';
?>

<h1 class="rewards-heading">Rewards</h1>
<p class="rewards-balance">
    You have <strong><?= $balance ?> point<?= $balance !== 1 ? 's' : '' ?></strong> to spend.
    Tap a card to see its redemption QR code.
</p>

<?php if (empty($rewards)): ?>
    <div class="alert alert-info">No rewards available right now — check back soon!</div>
<?php else: ?>
    <!-- Bootstrap responsive grid: 1 col mobile → 2 col tablet → 3 col desktop -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
        <?php foreach ($rewards as $r): ?>
            <?php
            $can_redeem = $balance >= (int)$r['points_required'];
            $rid        = (int)$r['reward_id'];
            $payload    = $signed_payloads[$rid] ?? null;
            ?>
            <div class="col">
                <div class="flip-wrapper"
                     id="wrap-<?= $rid ?>"
                     onclick="toggleFlip(<?= $rid ?>)"
                     role="button"
                     tabindex="0"
                     aria-label="<?= htmlspecialchars($r['name']) ?> — tap to flip"
                     onkeydown="if(event.key==='Enter'||event.key===' ')toggleFlip(<?= $rid ?>)"
                     <?php if ($payload): ?>
                     data-payload="<?= htmlspecialchars($payload) ?>"
                     <?php endif; ?>>

                    <!-- .flip-inner is the element that actually rotates — the wrapper
                         just provides the 3D perspective context. Without this div the
                         CSS transform target (.flip-wrapper.flipped .flip-inner) has
                         nothing to apply rotateY to, so the card never flips. -->
                    <div class="flip-inner">

                    <!-- FRONT -->
                    <div class="flip-face front<?= $can_redeem ? ' can-redeem' : '' ?>">
                        <h2 class="reward-name"><?= htmlspecialchars($r['name']) ?></h2>
                        <p class="reward-desc"><?= htmlspecialchars($r['description'] ?? '') ?></p>
                        <div class="reward-footer">
                            <?php if ($can_redeem): ?>
                                <span class="badge badge-green badge-lg">
                                    <?= (int)$r['points_required'] ?> pt<?= $r['points_required'] != 1 ? 's' : '' ?>
                                </span>
                                <span class="redeem-ready">Ready to redeem ✓</span>
                            <?php else: ?>
                                <span class="badge badge-red badge-lg">
                                    <?= (int)$r['points_required'] ?> pt<?= $r['points_required'] != 1 ? 's' : '' ?>
                                </span>
                                <span class="redeem-locked">
                                    Need <?= (int)$r['points_required'] - $balance ?> more
                                </span>
                            <?php endif; ?>
                        </div>
                        <p class="reward-hint">Tap to <?= $can_redeem ? 'see QR →' : 'view →' ?></p>
                    </div>

                    <!-- BACK -->
                    <div class="flip-face back">
                        <?php if ($can_redeem && $payload): ?>
                            <p class="redeem-label">Show to staff to redeem</p>
                            <div class="qr-box" id="qr-<?= $rid ?>"></div>
                            <p class="redeem-name"><?= htmlspecialchars($r['name']) ?></p>
                            <p class="redeem-label">
                                <?= (int)$r['points_required'] ?> point<?= $r['points_required'] != 1 ? 's' : '' ?>
                            </p>
                        <?php else: ?>
                            <p class="redeem-locked-text">
                                You need <?= (int)$r['points_required'] - $balance ?>
                                more point<?= ((int)$r['points_required'] - $balance) !== 1 ? 's' : '' ?>
                                to unlock this reward.
                            </p>
                            <a href="<?= BASE_URL ?>/customer/qr.php" class="btn-primary btn-earn">
                                Earn points →
                            </a>
                        <?php endif; ?>
                        <p class="reward-hint">Tap to flip back</p>
                    </div>

                    </div><!-- /.flip-inner -->
                </div><!-- /.flip-wrapper -->
            </div><!-- /.col -->
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
(function () {
  // I render QR codes lazily — only when the card is first flipped to the back.
  // This avoids generating 10+ QR canvases on page load for rewards the user
  // might never open.
  const rendered = new Set();

  window.toggleFlip = function (rewardId) {
    const wrapper = document.getElementById('wrap-' + rewardId);
    if (!wrapper) return;

    wrapper.classList.toggle('flipped');

    // Only render QR the first time this card shows its back face
    if (wrapper.classList.contains('flipped') && !rendered.has(rewardId)) {
      const container = document.getElementById('qr-' + rewardId);
      if (!container) return; // locked reward — no QR container

      // The signed payload is stored as a data attribute so I don't have to
      // embed it inline in the JS, which keeps the HTML cleaner.
      const payload = wrapper.dataset.payload;
      if (!payload) return;

      if (typeof QRCode === 'undefined') {
        container.textContent = 'QR library unavailable';
        return;
      }

      try {
        new QRCode(container, {
          // 118px keeps the rendered image inside the 280px card after padding
          text:         payload,
          width:        118,
          height:       118,
          colorDark:    '#111827',
          colorLight:   '#ffffff',
          correctLevel: QRCode.CorrectLevel.M,
        });
        rendered.add(rewardId);
      } catch (err) {
        container.textContent = 'QR error: ' + err.message;
      }
    }
  };
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
