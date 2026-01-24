<?php
session_start();

// Allow only POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request");
}

// Database connection
$conn = new mysqli("localhost", "root", "", "studentdb");

if ($conn->connect_error) {
    die("Database connection failed");
}

// Get form data
$rollno = $_POST['roll'];
$password = $_POST['password'];

// Empty check
if (empty($rollno) || empty($password)) {
    header("Location: ../Templates/login.html?error=empty_fields");
    exit;
}

// Prepare SQL
$stmt = $conn->prepare(
    "SELECT id, rollno, fullname, password FROM studentdata WHERE rollno = ?"
);

$stmt->bind_param("i", $rollno);
$stmt->execute();

$result = $stmt->get_result();

// Check if user exists
if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Verify password
    if (password_verify($password, $user['password'])) {

        // Login success → create session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['rollno'] = $user['rollno'];
        $_SESSION['fullname'] = $user['fullname'];

        header("Location: ../backend/user-profile.php");
        exit;
    }
}

// Login failed
header("Location: ../Templates/login.html?error=invalid_login");
exit;
?>
