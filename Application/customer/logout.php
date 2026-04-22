<?php
/**
 * Re-fill — Customer Logout
 * Destroys the session and redirects to the landing page.
 */
require_once __DIR__ . '/../includes/auth.php';
logout();
header('Location: ' . BASE_URL . '/');
exit;
