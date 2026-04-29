<?php
/**
 * Re-fill — Staff Login
 *
 * Separate from the customer login so staff aren't confused by a shared form.
 * The same vague error message applies here — I don't confirm whether
 * the email or password was wrong.
 */
require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in_staff()) {
    header('Location: ' . BASE_URL . '/staff/dashboard.php'); exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';

    if (staff_login($email, $password)) {
        header('Location: ' . BASE_URL . '/staff/dashboard.php'); exit;
    }

    $error = 'Incorrect email or password.';
}

$page_title = 'Staff Login';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-form">
    <h1>Staff login</h1>
    <p>Customer? <a href="<?= BASE_URL ?>/customer/login.php">Log in here</a></p>

    <?php if ($error): ?>
        <div class="alert alert-error" role="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" novalidate class="form-body">
        <div class="form-group">
            <label for="email">Staff email</label>
            <input type="email" id="email" name="email" autocomplete="email" required aria-required="true">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" autocomplete="current-password" required aria-required="true">
        </div>
        <button type="submit" class="btn-block">Log in as staff</button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
