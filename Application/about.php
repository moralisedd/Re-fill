<?php
/**
 * Re-fill — About page
 * Static content page explaining the app's purpose to new visitors.
 */
require_once __DIR__ . '/includes/auth.php';
$page_title = 'About Re-fill';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-prose">
    <h1>About Re-fill</h1>

    <p>Re-fill is a secure, web-based loyalty programme that rewards café customers for bringing reusable cups to a network of independent cafés across Sheffield.</p>

    <h2 class="section-heading">How it works</h2>
    <div class="card-grid card-grid--sm">
        <div class="card card--centered">
            <span class="feature-icon-sm" aria-hidden="true">📱</span>
            <h3>Show your QR</h3>
            <p>Log in and generate your unique QR code — it refreshes every 60 seconds for security.</p>
        </div>
        <div class="card card--centered">
            <span class="feature-icon-sm" aria-hidden="true">⭐</span>
            <h3>Earn points</h3>
            <p>Baristas scan your code when you bring a reusable cup. You earn 1 point per visit.</p>
        </div>
        <div class="card card--centered">
            <span class="feature-icon-sm" aria-hidden="true">🎁</span>
            <h3>Redeem rewards</h3>
            <p>Swap points for free drinks and more at any participating café in the network.</p>
        </div>
    </div>

    <h2 class="section-heading">Our mission</h2>
    <p>Single-use cup waste is a significant environmental problem. Re-fill exists to make sustainable choices the rewarding choice, building habits that benefit customers, cafés, and the planet.</p>

    <p class="page-back"><a href="<?= BASE_URL ?>/">← Back to home</a></p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
