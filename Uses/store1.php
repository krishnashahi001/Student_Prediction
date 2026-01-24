<?php
echo "<h1>Welcome to Store1.php</h1>";
// Database connection
$conn = new mysqli("localhost", "root", "", "studentdb");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$roll = $_POST['roll'];
$fullname = $_POST['fullname'];
$email = $_POST['email'];
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$contact = $_POST['contact'];
$stream = $_POST['stream'];

// Password check
if ($password !== $confirm_password) {
    die("Passwords do not match");
}

// Hash password (IMPORTANT)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert data using prepared statement
$stmt = $conn->prepare(
    "INSERT INTO studentdatas (roll, fullname, email, password, contact, stream)
     VALUES (?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param("isssss", $roll, $fullname, $email, $hashed_password, $contact, $stream);

if ($stmt->execute()) {
    echo "<h2>Registration Successful</h2>";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();

// Fetch and display all stored data
$result = $conn->query("SELECT roll,fullname,email,password FROM studentdata");
echo "<h2>Registered Students</h2>";
echo "<table border='1' cellpadding='10'>
        <tr>
            <th>Roll</th>
            <th>Name</th>
            <th>Email</th>
            <th>Contact</th>
            <th>Stream</th>
        </tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>{$row['roll']}</td>
            <td>{$row['fullname']}</td>
            <td>{$row['email']}</td>
            <td>{$row['contact']}</td>
            <td>{$row['stream']}</td>
          </tr>";
}

echo "</table>";

$conn->close();
?>
