<?php
session_start();

// Protect page (only logged-in users)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Prefer profile data stored in session (set at login). Use DB only as fallback.
$fullname  = $_SESSION['fullname']  ?? null;
$rollno    = $_SESSION['rollno']    ?? null;
$email     = $_SESSION['email']     ?? null;
$contactno = $_SESSION['contactno'] ?? null;
$stream    = $_SESSION['stream']    ?? null;

// If any key is missing, fetch from DB to fill missing details
$need_db = $fullname === null || $rollno === null || $email === null || $contactno === null || $stream === null;

if ($need_db) {
    $conn = new mysqli("localhost", "root", "", "studentdb");
    if ($conn->connect_error) {
        die("Database connection failed");
    }

    $user_id = (int)$_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT id, rollno, fullname, email, contactno, stream FROM studentdata WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                $student = $result->fetch_assoc();
                $fullname  = $student['fullname']  ?? 'Not provided';
                $rollno    = $student['rollno']    ?? 'Not provided';
                $email     = $student['email']     ?? 'Not provided';
                $contactno = $student['contactno'] ?? 'Not provided';
                $stream    = $student['stream']    ?? 'Not provided';

                // Update session so subsequent requests avoid DB
                $_SESSION['fullname']  = $fullname;
                $_SESSION['rollno']    = $rollno;
                $_SESSION['email']     = $email;
                $_SESSION['contactno'] = $contactno;
                $_SESSION['stream']    = $stream;
            }
        }
        $stmt->close();
    }
    $conn->close();
}

// Ensure we have display-safe defaults
$fullname  = $fullname  !== null && $fullname  !== '' ? $fullname  : 'Not provided';
$rollno    = $rollno    !== null && $rollno    !== '' ? $rollno    : 'Not provided';
$email     = $email     !== null && $email     !== '' ? $email     : 'Not provided';
$contactno = $contactno !== null && $contactno !== '' ? $contactno : 'Not provided';
$stream    = $stream    !== null && $stream    !== '' ? $stream    : 'Not provided';

// For password display we never store password in session. Show masked value.
$display_password = '******';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <base href="/sppa/">
    <link rel="stylesheet" href="Design/style.css">
</head>
<body>

<nav>
    <div class="nav-left">
        <button onclick="navigateTo('Templates/Index.html')">
            <img src="components/icons/home.png" class="icon"> Home
        </button>
    </div>

    <div class="nav-right">
        <button onclick="navigateTo('backend/logout.php')">
            <img src="components/icons/signout.png" class="icon"> Logout </button>
    </div>
</nav>

<main>
    <!-- Simple Profile Card -->
    <div class="simple-profile-card">
        <div class="profile-header-simple">
            <h2><?php echo htmlspecialchars($fullname); ?></h2>
            <p class="student-subtitle">Student Profile</p>
        </div>

        <div class="profile-info-simple">
            <div class="info-row">
                <span class="info-label">Roll Number:</span>
                <span class="info-value"><?php echo htmlspecialchars($rollno); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Full Name:</span>
                <span class="info-value"><?php echo htmlspecialchars($fullname); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value"><?php echo htmlspecialchars($email); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Contact Number:</span>
                <span class="info-value"><?php echo htmlspecialchars($contactno); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Stream:</span>
                <span class="info-value">
                    <?php 
                    $stream_map = [
                        'bsc_it' => 'B.Sc. IT',
                        'bsc_cs' => 'B.Sc. CS',
                        'bcom' => 'B.Com',
                        'bms' => 'BMS'
                    ];
                    $stream_display = isset($stream_map[$stream]) ? $stream_map[$stream] : $stream;
                    echo htmlspecialchars($stream_display); 
                    ?>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Password:</span>
                <span class="info-value"><?php echo htmlspecialchars($display_password); ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Status:</span>
                <span class="info-value"><span class="badge-active"> ● Active</span></span>
            </div>
        </div>

        <div class="action-buttons">
        
            <button class="btn-action btn-predict" onclick="navigateTo('Templates/prediction-inputs.html')">Performance Prediction</button>
        </div>
    </div>
</main>

<script src="javascript/script.js"></script>
</body>
</html>
