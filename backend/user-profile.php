<?php
session_start();

// Protect page (only logged-in users)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile</title>
    <link rel="stylesheet" href="../Design/style.css">
</head>
<body>

<nav>
    <div class="nav-left">
        <button onclick="location.href='../Templates/Index.html'">
            <img src="../components/icons/home.png" class="icon"> Home
        </button>
    </div>

    <div class="nav-right">
        <button onclick="location.href='../backend/logout.php'"> Logout  </button>
    </div>
</nav>

<main>
    <h2>Student Profile</h2>

    <div class="profile-card">
        <p><strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['fullname']); ?></p>
        <p><strong>Roll No:</strong> <?php echo htmlspecialchars($_SESSION['rollno']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?></p>
       <div> 
        <button class="nav button" onclick="location.href='edit-profile.php'">Edit Profile</button> <br><br>
        <button class="nav button" onclick="location.href='../templates/prediction-inputs.html'">Predict </button>
       </div>
    </div>
</main>

</body>
</html>
