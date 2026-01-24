<?php
// Database connection details (XAMPP defaults)
$servername = "localhost";
$username = "root";
$password = ""; // No password by default in XAMPP
$dbname = "studentdb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $rollno = $_POST['roll'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $contact = $_POST['contact'];
    $stream = $_POST['stream'];

    // Validate password match
    if ($password !== $confirm_password) {
        echo "Error: Passwords do not match.<br>";
        exit;
    }

    // Check if rollno already exists
    $check_sql = "SELECT rollno FROM users WHERE rollno = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s", $rollno);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Rollno exists
        echo "Already Exist Login<br>";
    } 

    else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new record
        $insert_sql = "INSERT INTO users (rollno, fullname, email, password, contact_no, stream) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("ssssss", $rollno, $fullname, $email, $hashed_password, $contact, $stream);

        if ($stmt->execute()) {
            echo "Registration successful!<br>";
        } 
        else {
            echo "Error: " . $stmt->error . "<br>";
        }
    }
    $stmt->close();
}

// Display all data from the database
echo "<h2>All Registered Users</h2>";
$sql = "SELECT rollno, fullname, email, contact, stream FROM studentdata,password"; // Exclude password for security
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<table border='1'>
            <tr>
                <th>Roll No</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Contact No</th>
                <th>Stream</th>
            </tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>" . $row["rollno"] . "</td>
                <td>" . $row["fullname"] . "</td>
                <td>" . $row["email"] . "</td>
                <td>" . $row["contact_no"] . "</td>
                <td>" . $row["stream"] . "</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "No data found.";
}

$conn->close();
?>