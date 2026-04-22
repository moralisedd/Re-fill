<?php
/**
 * Re-fill — Shared HTML header partial
 * Include at the top of every customer-facing page.
 * $page_title must be set before including this file.
 */
$page_title = $page_title ?? 'Re-fill';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Re-fill — reusable cup loyalty app for independent cafes.">
    <title><?= htmlspecialchars($page_title) ?> | Re-fill</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
    <!-- Global JS — loaded in <head> so window.Refill is available to inline page scripts -->
    <script src="<?= BASE_URL ?>/assets/js/app.js"></script>
    <!-- QR Code library (local bundle — no CDN dependency) -->
    <?php if (!empty($load_qr_lib)): ?>
    <script src="<?= BASE_URL ?>/assets/js/qrcode.min.js"></script>
    <?php endif; ?>
</head>
<body>

<a href="#main-content" class="skip-link">Skip to content</a>

<header class="site-header" role="banner">
    <div class="container header-inner">
        <a href="<?= BASE_URL ?>/" class="logo" aria-label="Re-fill home">
            <span class="logo-icon" aria-hidden="true">♻️</span>
            <span class="logo-text">Re-fill</span>
        </a>

        <nav class="main-nav" aria-label="Main navigation">
            <?php if (is_logged_in_customer()): ?>
                <a href="<?= BASE_URL ?>/customer/dashboard.php">Dashboard</a>
                <a href="<?= BASE_URL ?>/customer/qr.php">My QR</a>
                <a href="<?= BASE_URL ?>/customer/history.php">History</a>
                <a href="<?= BASE_URL ?>/customer/rewards.php">Rewards</a>
                <a href="<?= BASE_URL ?>/customer/logout.php" class="btn-secondary">Log out</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/customer/login.php">Log in</a>
                <a href="<?= BASE_URL ?>/customer/register.php" class="btn-primary">Sign up</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="container" id="main-content">
