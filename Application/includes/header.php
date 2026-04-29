<?php
/**
 * Re-fill — Shared HTML header partial
 * Include at the top of every customer-facing page.
 * $page_title must be set before including this file.
 *
 * I derive the active page from PHP_SELF so I can highlight the
 * current nav link without each page needing to set an extra variable.
 */
$page_title   = $page_title ?? 'Re-fill';
$_current_page = basename($_SERVER['PHP_SELF'] ?? '', '.php');

// Returns the nav-active CSS class if the basename matches
function nav_is_active(string $page_name): string {
    global $_current_page;
    return $_current_page === $page_name ? ' nav-active' : '';
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Re-fill — reusable cup loyalty app for independent cafes.">
    <title><?= htmlspecialchars($page_title) ?> | Re-fill</title>
    <!-- Bootstrap 5 — loaded before custom CSS so my variables take precedence -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
          crossorigin="anonymous">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <!-- Global JS — loaded in <head> so window.Refill is available to inline page scripts -->
    <script src="<?= BASE_URL ?>/assets/js/app.js"></script>
    <!-- QR Code library (local bundle — no CDN dependency) -->
    <?php if (!empty($load_qr_lib)): ?>
    <script src="<?= BASE_URL ?>/assets/js/qrcode.min.js"></script>
    <?php endif; ?>
</head>
<body>

<!-- visually-hidden-focusable hides the link via clip until it receives keyboard focus -->
<a href="#main-content" class="skip-link visually-hidden-focusable">Skip to content</a>

<header class="site-header" role="banner">
    <div class="container header-inner">
        <a href="<?= BASE_URL ?>/" class="logo" aria-label="Re-fill home">
            <span class="logo-icon" aria-hidden="true">♻️</span>
            <span class="logo-text">Re-fill</span>
        </a>

        <nav class="main-nav" aria-label="Main navigation">
            <?php if (is_logged_in_customer()): ?>
                <a href="<?= BASE_URL ?>/customer/dashboard.php" class="<?= nav_is_active('dashboard') ?>">Dashboard</a>
                <a href="<?= BASE_URL ?>/customer/qr.php"        class="<?= nav_is_active('qr') ?>">My QR</a>
                <a href="<?= BASE_URL ?>/customer/history.php"   class="<?= nav_is_active('history') ?>">History</a>
                <a href="<?= BASE_URL ?>/customer/rewards.php"   class="<?= nav_is_active('rewards') ?>">Rewards</a>
                <a href="<?= BASE_URL ?>/customer/logout.php" class="btn-secondary">Log out</a>
            <?php elseif (is_logged_in_staff()): ?>
                <a href="<?= BASE_URL ?>/staff/dashboard.php" class="<?= nav_is_active('dashboard') ?>">Dashboard</a>
                <a href="<?= BASE_URL ?>/staff/scan.php"      class="<?= nav_is_active('scan') ?>">Scan QR</a>
                <a href="<?= BASE_URL ?>/staff/logout.php" class="btn-secondary">Log out</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/customer/login.php"    class="<?= nav_is_active('login') ?>">Log in</a>
                <a href="<?= BASE_URL ?>/customer/register.php" class="btn-primary<?= nav_is_active('register') ?>">Sign up</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<!-- tabindex="-1" lets the skip link move keyboard focus here without
     inserting <main> into the natural tab order -->
<main class="container" id="main-content" tabindex="-1">
