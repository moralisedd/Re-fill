<?php
/**
 * Re-fill — Privacy Policy
 *
 * I include this page to satisfy GDPR Article 13 — users must be informed
 * about what data is collected and why before or at the point of collection.
 */
require_once __DIR__ . '/includes/auth.php';
$page_title = 'Privacy Policy';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-prose">
    <h1>Privacy Policy</h1>
    <p class="page-meta">Last updated: April 2026</p>

    <h2 class="section-heading">What data we collect</h2>
    <p>Re-fill collects the following personal data when you register:</p>
    <ul class="prose-list">
        <li>Full name</li>
        <li>Email address</li>
        <li>Phone number (optional)</li>
        <li>Transaction history (points earned and redeemed, café visited, date/time)</li>
    </ul>

    <h2 class="section-heading">Why we collect it</h2>
    <p>Your data is used solely to operate the Re-fill loyalty programme, authenticating you, recording your points, and enabling reward redemption. We do not sell, share, or use your data for advertising.</p>

    <h2 class="section-heading">How we protect it</h2>
    <p>Passwords are hashed using bcrypt (cost factor 12) and never stored in plain text. All database queries use parameterised statements to prevent SQL injection. Session tokens are HTTP-only and regenerated on login.</p>

    <h2 class="section-heading">Your rights (UK GDPR)</h2>
    <p>Under the UK General Data Protection Regulation, you have the right to access, correct, or delete your personal data at any time. To exercise these rights, contact us via the café you are registered with.</p>

    <h2 class="section-heading">Data retention</h2>
    <p>Your account data is retained for as long as your account is active. Transaction records are kept for up to 2 years for audit purposes, after which they are permanently deleted.</p>

    <p class="page-back"><a href="<?= BASE_URL ?>/">← Back to home</a></p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
