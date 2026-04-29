<?php
/**
 * Re-fill — Customer Login
 *
 * I use a single generic error message for both bad email and bad password
 * so attackers can't use the error to enumerate registered email addresses.
 */
require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in_customer()) {
    header('Location: ' . BASE_URL . '/customer/dashboard.php'); exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';

    if (customer_login($email, $password)) {
        header('Location: ' . BASE_URL . '/customer/dashboard.php'); exit;
    }

    // Deliberately vague — I don't tell the user which of email/password was wrong
    $error = 'Incorrect email or password. Please try again.';
}

$page_title = 'Log in';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-form">
    <h1>Log in to Re-fill</h1>
    <p>New here? <a href="<?= BASE_URL ?>/customer/register.php">Create a free account</a></p>

    <?php if ($error): ?>
        <div class="alert alert-error" role="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" action="" novalidate class="form-body">

        <div class="form-group">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email"
                   autocomplete="email" required aria-required="true">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   autocomplete="current-password" required aria-required="true">
        </div>

        <button type="submit" class="btn-block">Log in</button>

        <p class="form-hint">
            Forgot your password? Contact your café directly to reset your account.
        </p>
    </form>

    <hr class="section-divider">
    <p class="footer-note">
        Are you cafe staff? <a href="<?= BASE_URL ?>/staff/login.php">Staff login →</a>
    </p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
