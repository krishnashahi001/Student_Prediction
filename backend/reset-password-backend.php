<?php
session_start();

require_once __DIR__ . '/../classes/OtpHandler.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: forgot-password.php");
    exit;
}

// Check if OTP was verified
if (!isset($_SESSION['otp_verified']) || !$_SESSION['otp_verified']) {
    header("Location: forgot-password.php");
    exit;
}

$newPassword = $_POST['new_password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if (empty($newPassword) || empty($confirmPassword)) {
    header("Location: reset-password.php?error=empty_fields");
    exit;
}

if ($newPassword !== $confirmPassword) {
    header("Location: reset-password.php?error=password_mismatch");
    exit;
}

if (strlen($newPassword) < 6) {
    header("Location: reset-password.php?error=weak_password");
    exit;
}

$otpHandler = new OtpHandler();
$userId = $_SESSION['reset_user_id'];

if ($otpHandler->updatePassword($userId, $newPassword)) {
    // Clear session
    unset($_SESSION['reset_user_id']);
    unset($_SESSION['reset_otp']);
    unset($_SESSION['reset_expiry']);
    unset($_SESSION['otp_verified']);

    header("Location: login-page.php?success=password_reset");
    exit;
} else {
    header("Location: reset-password.php?error=update_failed");
    exit;
}
?>