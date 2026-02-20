<?php
session_start();

// Allow only POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: /sppa/backend/login-page.php");
    exit;
}

// Database connection
$conn = new mysqli("localhost", "root", "", "studentdb");

if ($conn->connect_error) {
    die("Database connection failed");
}

// Get and sanitize input
$rollno   = trim($_POST['roll'] ?? '');
$password = trim($_POST['password'] ?? '');

// Empty check
if ($rollno === '' || $password === '') {
    header("Location: /sppa/backend/login-page.php?error=empty_fields");
    exit;
}

// Prepare SQL
$stmt = $conn->prepare(
    "SELECT id, rollno, fullname, password, email, contactno, stream 
     FROM studentdata 
     WHERE rollno = ?"
);

if (!$stmt) {
    die("SQL prepare failed");
}

// IMPORTANT: bind as STRING
$stmt->bind_param("s", $rollno);
$stmt->execute();

$result = $stmt->get_result();

// Check if user exists
if ($result && $result->num_rows === 1) {

    $user = $result->fetch_assoc();

    // Verify password
    if (password_verify($password, $user['password'])) {

        // 🔐 Login success → create session with profile fields
        $_SESSION['user_id']   = (int)$user['id'];   // PRIMARY KEY
        $_SESSION['rollno']    = $user['rollno'];
        $_SESSION['fullname']  = $user['fullname'];
        // store additional profile fields for immediate use in profile page
        $_SESSION['email']     = $user['email'] ?? '';
        $_SESSION['contactno'] = $user['contactno'] ?? '';
        $_SESSION['stream']    = $user['stream'] ?? '';

        header("Location: /sppa/backend/user-profile.php");
        exit;
    } else {
        // Password incorrect
        header("Location: /sppa/backend/login-page.php?error=incorrect_password");
        exit;
    }
}

// Roll number not found
header("Location: /sppa/backend/login-page.php?error=incorrect_id");
exit;
?>
