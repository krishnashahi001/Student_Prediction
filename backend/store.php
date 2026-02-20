<?php
// Allow only POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request");
}

// Error handler function
function displayErrorPage($title, $errorMsg, $backLink) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title><?php echo htmlspecialchars($title); ?></title>
        <base href="/sppa/">
        <link rel="stylesheet" href="Design/style.css">
    </head>
    <body>
        <div class="message-container">
            <div class="message-box error-message">
                <h2>❌ <?php echo htmlspecialchars($title); ?></h2>
                <p><strong>Error Details:</strong></p>
                <div class="error-details">
                    <?php echo htmlspecialchars($errorMsg); ?>
                </div>
                <p style="margin-top: 20px; color: #666; font-size: 14px;">
                    Please check your information and try again.
                </p>
                <a href="<?php echo htmlspecialchars($backLink); ?>">Go Back to Registration</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Success handler function
function displaySuccessPage() {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Registration Successful</title>
        <base href="/sppa/">
        <link rel="stylesheet" href="Design/style.css">
    </head>
    <body>
        <div class="message-container">
            <div class="message-box success-message">
                <h2>✅ Registration Successful!</h2>
                <p>Your account has been created successfully.</p>
               
                    You can now login with your credentials.
                </p>
                <a href="backend/login-page.php">Proceed to Login</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Database connection
$conn = new mysqli("localhost", "root", "", "studentdb");

// Check connection
if ($conn->connect_error) {
    displayErrorPage(
        "Connection Failed",
        "Unable to connect to database: " . $conn->connect_error,
        "Templates/register.html"
    );
}

// Get form data
$rollno = $_POST['roll'] ?? '';
$fullname = $_POST['fullname'] ?? '';
$email = $_POST['email'] ?? '';
$password_plain = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$contactno = $_POST['contact'] ?? '';
$stream = $_POST['stream'] ?? '';

// Empty field validation
if (empty($rollno) || empty($fullname) || empty($email) ||
    empty($password_plain) || empty($confirm_password) ||
    empty($contactno) || empty($stream)) {
    displayErrorPage(
        "Registration Failed",
        "All fields are required. Please fill in all the fields.",
        "Templates/register.html"
    );
}

// Password match check
if ($password_plain !== $confirm_password) {
    displayErrorPage(
        "Registration Failed",
        "Passwords do not match. Please ensure both passwords are identical.",
        "Templates/register.html"
    );
}

// Email validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    displayErrorPage(
        "Registration Failed",
        "Invalid email format. Please enter a valid email address.",
        "Templates/register.html"
    );
}

// Contact number validation (must be exactly 10 digits)
if (!preg_match('/^[0-9]{10}$/', $contactno)) {
    displayErrorPage(
        "Registration Failed",
        "Contact number must be exactly 10 digits.",
        "Templates/register.html"
    );
}

// Hash password
$password_hash = password_hash($password_plain, PASSWORD_DEFAULT);

// Prepare SQL
$stmt = $conn->prepare(
    "INSERT INTO studentdata (rollno, fullname, email, password, contactno, stream)
     VALUES (?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    displayErrorPage(
        "Registration Failed",
        "Database error: " . $conn->error,
        "Templates/register.html"
    );
}

// Bind parameters (use strings for rollno and contactno to preserve formatting)
$stmt->bind_param(
    "ssssss",
    $rollno,
    $fullname,
    $email,
    $password_hash,
    $contactno,
    $stream
);

// Execute with try-catch for database errors
try {
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        displaySuccessPage();
    } else {
        throw new Exception($stmt->error);
    }
} catch (Exception $e) {
    $errorMsg = $e->getMessage();
    
    // Handle specific error messages
    if (strpos($errorMsg, 'Duplicate entry') !== false) {
        $errorMsg = "This Roll Number or Email already exists. Please use a different one.";
    } elseif (strpos($errorMsg, 'PRIMARY') !== false) {
        $errorMsg = "This Roll Number is already registered in the system.";
    }
    
    error_log("Insert failed: " . $e->getMessage());
    $stmt->close();
    $conn->close();
    
    displayErrorPage(
        "Registration Failed",
        $errorMsg,
        "Templates/register.html"
    );
}
?>

