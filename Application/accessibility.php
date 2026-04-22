<?php
/**
 * Re-fill — Accessibility Statement
 *
 * I maintain this page to document WCAG 2.1 AA compliance measures and
 * any known limitations, as good practice for a public-facing service.
 */
require_once __DIR__ . '/includes/auth.php';
$page_title = 'Accessibility Statement';
require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width:700px; margin:0 auto;">
    <h1>Accessibility Statement</h1>
    <p style="color:var(--colour-text-muted); font-size:.9rem;">Last updated: April 2026</p>

    <p>Re-fill is committed to making this service accessible to everyone, including people with disabilities. This application aims to conform to the <strong>Web Content Accessibility Guidelines (WCAG) 2.1 Level AA</strong>.</p>

    <h2 style="margin-top:2rem;">Measures taken</h2>
    <ul style="margin-left:1.5rem; margin-bottom:1rem; line-height:1.8;">
        <li>Colour contrast ratio of at least 4.5:1 for all body text</li>
        <li>Visible focus indicators on all interactive elements (3px accent outline)</li>
        <li>Minimum tap target size of 44×44 pixels for all buttons and links</li>
        <li>Skip-to-content link for keyboard navigation</li>
        <li>ARIA live regions for dynamic content updates (QR scan results, countdown timer)</li>
        <li>Semantic HTML5 landmarks: <code>header</code>, <code>main</code>, <code>nav</code>, <code>footer</code></li>
        <li>All images and icons with meaning include descriptive <code>alt</code> or <code>aria-label</code> attributes</li>
        <li>Decorative icons use <code>aria-hidden="true"</code></li>
        <li>Form inputs have associated <code>&lt;label&gt;</code> elements and error messages linked via <code>aria-describedby</code></li>
    </ul>

    <h2 style="margin-top:2rem;">Known limitations</h2>
    <p>The QR code camera scanner uses the <code>html5-qrcode</code> library, which relies on the browser's <code>getUserMedia</code> API. Camera scanning requires the user to grant camera permission in their browser. A manual token entry fallback is provided for all users who prefer not to use the camera or whose browser does not support it.</p>

    <h2 style="margin-top:2rem;">Feedback</h2>
    <p>If you experience any accessibility barrier while using Re-fill, please contact the café where you are registered. We aim to respond to accessibility feedback within 5 working days.</p>

    <p style="margin-top:1.5rem;"><a href="<?= BASE_URL ?>/">← Back to home</a></p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
