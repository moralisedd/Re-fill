<?php
/**
 * Re-fill — Customer Registration
 *
 * I validate everything server-side here because client-side validation
 * can be bypassed. I also re-populate the form fields on error so the user
 * doesn't have to type everything again.
 */
require_once __DIR__ . '/../includes/auth.php';

// Redirect already-logged-in customers straight to their dashboard
if (is_logged_in_customer()) {
    header('Location: ' . BASE_URL . '/customer/dashboard.php'); exit;
}

$errors = [];
$values = ['email' => '', 'full_name' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email     = trim($_POST['email']     ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $phone     = trim($_POST['phone']     ?? '');
    $password  = $_POST['password']       ?? '';
    $confirm   = $_POST['confirm']        ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    if (strlen($full_name) < 2) {
        $errors['full_name'] = 'Please enter your full name.';
    }
    if (!is_strong_password($password)) {
        $errors['password'] = 'Password must be at least 8 characters, with uppercase, lowercase, a number, and a special character.';
    }
    if ($password !== $confirm) {
        $errors['confirm'] = 'Passwords do not match.';
    }

    if (empty($errors)) {
        $pdo = get_db();

        // I check for duplicate email before inserting — the DB has a UNIQUE constraint
        // too, but catching it here gives me a user-friendly error message.
        $stmt = $pdo->prepare('SELECT user_id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors['email'] = 'An account with this email already exists.';
        } else {
            $pdo->prepare(
                'INSERT INTO users (email, password_hash, full_name, phone) VALUES (?, ?, ?, ?)'
            )->execute([$email, hash_password($password), $full_name, $phone ?: null]);

            // Log the user in immediately after registration — no need to make them log in twice
            customer_login($email, $password);
            header('Location: ' . BASE_URL . '/customer/dashboard.php'); exit;
        }
    }

    // Re-populate safe fields so the user doesn't have to retype them
    $values = compact('email', 'full_name', 'phone');
}

$page_title = 'Create account';
require_once __DIR__ . '/../includes/header.php';
?>

<div style="max-width:480px; margin:0 auto;">
    <h1>Create your Re-fill account</h1>
    <p>Already have one? <a href="<?= BASE_URL ?>/customer/login.php">Log in</a></p>

    <form method="post" action="" novalidate style="margin-top:1.5rem;">

        <div class="form-group">
            <label for="full_name">Full name</label>
            <input type="text" id="full_name" name="full_name"
                   value="<?= htmlspecialchars($values['full_name']) ?>"
                   autocomplete="name" required
                   aria-required="true"
                   <?= isset($errors['full_name']) ? 'aria-invalid="true"' : '' ?>>
            <?php if (isset($errors['full_name'])): ?>
                <span class="form-error" role="alert"><?= htmlspecialchars($errors['full_name']) ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email"
                   value="<?= htmlspecialchars($values['email']) ?>"
                   autocomplete="email" required
                   aria-required="true"
                   <?= isset($errors['email']) ? 'aria-invalid="true"' : '' ?>>
            <?php if (isset($errors['email'])): ?>
                <span class="form-error" role="alert"><?= htmlspecialchars($errors['email']) ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="phone">Phone number <span style="font-weight:400; color:var(--colour-text-muted);">(optional)</span></label>
            <input type="tel" id="phone" name="phone"
                   value="<?= htmlspecialchars($values['phone']) ?>"
                   autocomplete="tel">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   autocomplete="new-password" required
                   aria-required="true"
                   aria-describedby="password-hint"
                   <?= isset($errors['password']) ? 'aria-invalid="true"' : '' ?>>
            <span id="password-hint" style="font-size:.8rem; color:var(--colour-text-muted);">
                Min. 8 characters, including uppercase, lowercase, number, and special character.
            </span>
            <?php if (isset($errors['password'])): ?>
                <span class="form-error" role="alert"><?= htmlspecialchars($errors['password']) ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="confirm">Confirm password</label>
            <input type="password" id="confirm" name="confirm"
                   autocomplete="new-password" required
                   aria-required="true"
                   <?= isset($errors['confirm']) ? 'aria-invalid="true"' : '' ?>>
            <?php if (isset($errors['confirm'])): ?>
                <span class="form-error" role="alert"><?= htmlspecialchars($errors['confirm']) ?></span>
            <?php endif; ?>
        </div>

        <button type="submit" style="width:100%;">Create account</button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
