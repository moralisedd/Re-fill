<?php
/**
 * Re-fill — Landing page / route dispatcher
 * Redirects logged-in users straight to their dashboard.
 */
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in_customer()) {
    header('Location: ' . BASE_URL . '/customer/dashboard.php');
    exit;
}

if (is_logged_in_staff()) {
    header('Location: ' . BASE_URL . '/staff/dashboard.php');
    exit;
}

$page_title = 'Welcome';
require_once __DIR__ . '/includes/header.php';
?>

<section class="hero" style="text-align:center; padding: 4rem 1rem;">
    <span style="font-size:4rem;" aria-hidden="true">♻️</span>
    <h1 style="margin-top:1rem;">Bring your cup. Earn rewards.</h1>
    <p style="font-size:1.15rem; color:var(--colour-text-muted); max-width:520px; margin:1rem auto 2rem;">
        Re-fill rewards customers who bring reusable cups to independent cafes across the city.
        Every visit earns points. Every point gets you closer to a free drink.
    </p>
    <div style="display:flex; gap:1rem; justify-content:center; flex-wrap:wrap;">
        <a href="<?= BASE_URL ?>/customer/register.php" class="btn-primary">Get started — it's free</a>
        <a href="<?= BASE_URL ?>/customer/login.php"    class="btn-secondary">Log in</a>
    </div>
</section>

<section class="card-grid" style="margin-top:3rem;">
    <div class="card" style="text-align:center;">
        <span style="font-size:2.5rem;" aria-hidden="true">📱</span>
        <h2 style="margin-top:.75rem;">Show your QR</h2>
        <p>Open the app, tap <em>My QR</em>, and let the barista scan it.</p>
    </div>
    <div class="card" style="text-align:center;">
        <span style="font-size:2.5rem;" aria-hidden="true">⭐</span>
        <h2 style="margin-top:.75rem;">Earn points</h2>
        <p>Get 1 point per visit. Rack them up across any cafe in the network.</p>
    </div>
    <div class="card" style="text-align:center;">
        <span style="font-size:2.5rem;" aria-hidden="true">🎁</span>
        <h2 style="margin-top:.75rem;">Redeem rewards</h2>
        <p>Swap points for free drinks, cake, and more — any participating cafe.</p>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
