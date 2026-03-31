<?php
session_start();

require_once __DIR__ . '/../classes/OtpHandler.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: forgot-password.php");
    exit;
}

$identifier = trim($_POST['identifier'] ?? '');

if (empty($identifier)) {
    header("Location: forgot-password.php?error=empty_fields");
    exit;
}

$otpHandler = new OtpHandler();

// Find user by roll or email
$user = $otpHandler->findUser($identifier);

if (!$user) {
    header("Location: forgot-password.php?error=user_not_found");
    exit;
}

// Generate and send OTP
$otp = $otpHandler->generateOtp();
$sent = $otpHandler->sendOtp($user['email'], $otp);

if ($sent) {
    // Store OTP and user info in session
    $_SESSION['reset_user_id'] = $user['id'];
    $_SESSION['reset_otp'] = $otp;
    $_SESSION['reset_expiry'] = time() + 300; // 5 minutes

    header("Location: verify-otp.php");
    exit;
} else {
    header("Location: forgot-password.php?error=send_failed");
    exit;
}
?>