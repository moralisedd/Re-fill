<?php
/**
 * Re-fill — Staff Logout
 * Destroys the session and returns to the staff login page.
 */
require_once __DIR__ . '/../includes/auth.php';
logout();
header('Location: ' . BASE_URL . '/staff/login.php');
exit;
