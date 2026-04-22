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

<div style="max-width:420px; margin:0 auto;">
    <h1>Log in to Re-fill</h1>
    <p>New here? <a href="<?= BASE_URL ?>/customer/register.php">Create a free account</a></p>

    <?php if ($error): ?>
        <div class="alert alert-error" role="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" action="" novalidate style="margin-top:1.5rem;">

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

        <button type="submit" style="width:100%;">Log in</button>

        <p style="margin-top:1rem; font-size:.9rem; color:var(--colour-text-muted);">
            Forgot your password? Contact your café directly to reset your account.
        </p>
    </form>

    <hr style="margin:2rem 0; border:none; border-top:1px solid var(--colour-border);">
    <p style="font-size:.85rem; color:var(--colour-text-muted);">
        Are you cafe staff? <a href="<?= BASE_URL ?>/staff/login.php">Staff login →</a>
    </p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
