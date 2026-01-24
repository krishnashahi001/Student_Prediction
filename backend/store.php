<?php
// Allow only POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request");
}

// Database connection
$conn = new mysqli("localhost", "root", "", "studentdb");

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Get form data
$rollno = $_POST['roll'];
$fullname = $_POST['fullname'];
$email = $_POST['email'];
$password_plain = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$contactno = $_POST['contact'];
$stream = $_POST['stream'];

// Empty field validation
if (
    empty($rollno) || empty($fullname) || empty($email) ||
    empty($password_plain) || empty($confirm_password) ||
    empty($contactno) || empty($stream)
) {
    header('Location: ../Templates/register.html?error=empty_fields');
    exit;
}

// Password match check
if ($password_plain !== $confirm_password) {
    header('Location: ../Templates/register.html?error=password_mismatch');
    exit;
}

// Email validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../Templates/register.html?error=invalid_email');
    exit;
}

// Hash password
$password_hash = password_hash($password_plain, PASSWORD_DEFAULT);

// Prepare SQL
$stmt = $conn->prepare(
    "INSERT INTO studentdata (rollno, fullname, email, password, contactno, stream)
     VALUES (?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    header('Location: ../Templates/register.html?error=server_error');
    exit;
}

// Bind parameters
$stmt->bind_param(
    "isssss",
    $rollno,
    $fullname,
    $email,
    $password_hash,
    $contactno,
    $stream
);

// Execute
// Execute
if ($stmt->execute()) {
    $stmt->close();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Registration Successful</title>
        <link rel="stylesheet" href="../Design/style.css">
    </head>
    <body>

    <div class="success-message">
        <h2>✅ Registration Successful</h2>
        <p>Your account has been created successfully.</p>
        <a href="../Backend/login-page.php"> Click here to Login</a>
    </div>

    </body>
    </html>
    <?php
    exit;

} else {
    $errorMsg = htmlspecialchars($stmt->error);
    error_log("Insert failed: " . $stmt->error);
    $stmt->close();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Registration Failed</title>
        <link rel="stylesheet" href="../Design/style.css">
    </head>
    <body>

    <div class="success-message">
        <h2 >❌ Registration Failed</h2>
        <p><strong>Reason:</strong></p>
        <p ><?php echo $errorMsg; ?></p>
        <a href="../Templates/register.html" >Go back to Registration</a>
    </div>

    </body>
    </html>
    <?php
    exit;
}

// Close connection
$conn->close();
?>
