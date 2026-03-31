<?php
session_start();

require_once __DIR__ . '/../classes/OtpHandler.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: forgot-password.php");
    exit;
}

$otp = trim($_POST['otp'] ?? '');

if (empty($otp)) {
    header("Location: verify-otp.php?error=empty_fields");
    exit;
}

// Check if session has reset data
if (!isset($_SESSION['reset_user_id']) || !isset($_SESSION['reset_otp']) || !isset($_SESSION['reset_expiry'])) {
    header("Location: forgot-password.php?error=session_expired");
    exit;
}

$otpHandler = new OtpHandler();

if ($otpHandler->verifyOtp($otp, $_SESSION['reset_otp'], $_SESSION['reset_expiry'])) {
    // OTP valid, proceed to reset password
    $_SESSION['otp_verified'] = true;
    header("Location: reset-password.php");
    exit;
} else {
    header("Location: verify-otp.php?error=invalid_otp");
    exit;
}
?>