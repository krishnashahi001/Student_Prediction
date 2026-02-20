<?php
session_start();
// Simple admin login handler with static credentials.
// Username: Admin  Password: Admin123
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /sppa/Templates/admin-login.html');
    exit;
}

$user = $_POST['username'] ?? '';
$pass = $_POST['password'] ?? '';

// Normalize
$user = trim($user);

if ($user === 'Admin' && $pass === 'Admin123') {
    // Successful login
    $_SESSION['is_admin'] = true;
    $_SESSION['admin_user'] = 'Admin';
    header('Location: admin.php');
    exit;
} else {
    // Failed - redirect back with error
    header('Location: /sppa/Templates/admin-login.html?error=1');
    exit;
}

?>
