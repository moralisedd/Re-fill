<?php
/**
 * Re-fill — About page
 * Static content page explaining the app's purpose to new visitors.
 */
require_once __DIR__ . '/includes/auth.php';
$page_title = 'About Re-fill';
require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width:700px; margin:0 auto;">
    <h1>About Re-fill</h1>

    <p>Re-fill is a secure, web-based loyalty programme that rewards café customers for bringing reusable cups to a network of independent cafés across Sheffield.</p>

    <h2 style="margin-top:2rem;">How it works</h2>
    <div class="card-grid" style="margin-top:1rem;">
        <div class="card" style="text-align:center;">
            <span style="font-size:2rem;" aria-hidden="true">📱</span>
            <h3 style="margin-top:.75rem;">Show your QR</h3>
            <p style="font-size:.9rem;">Log in and generate your unique QR code — it refreshes every 60 seconds for security.</p>
        </div>
        <div class="card" style="text-align:center;">
            <span style="font-size:2rem;" aria-hidden="true">⭐</span>
            <h3 style="margin-top:.75rem;">Earn points</h3>
            <p style="font-size:.9rem;">Baristas scan your code when you bring a reusable cup. You earn 1 point per visit.</p>
        </div>
        <div class="card" style="text-align:center;">
            <span style="font-size:2rem;" aria-hidden="true">🎁</span>
            <h3 style="margin-top:.75rem;">Redeem rewards</h3>
            <p style="font-size:.9rem;">Swap points for free drinks and more at any participating café in the network.</p>
        </div>
    </div>

    <h2 style="margin-top:2rem;">Our mission</h2>
    <p>Single-use cup waste is a significant environmental problem. Re-fill exists to make sustainable choices the rewarding choice — building habits that benefit customers, cafés, and the planet.</p>

    <p style="margin-top:1.5rem;"><a href="<?= BASE_URL ?>/">← Back to home</a></p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
