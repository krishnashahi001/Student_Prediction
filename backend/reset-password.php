<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <base href="/sppa/">
    <link rel="stylesheet" href="Design/style.css">
</head>
<body>
    <nav>
     <div class="nav-left">
            <button onclick="navigateTo('Templates/Index.html')"> 
                <img src="components/icons/home.png" class="icon"> Home </button>

        </div>
        <div class="nav-right">
            <button onclick="navigateTo('backend/login-page.php')">
                <img src="components/icons/login.png" class="icon">Login</button>

        </div>
    </nav>
    
    <main>
        <h2>Reset Password</h2>
        
        <?php if (isset($_GET['error'])): ?>
            <p style="color:red; font-weight:bold;">
                <?php
                if ($_GET['error'] === 'password_mismatch') {
                    echo "Passwords do not match.";
                } elseif ($_GET['error'] === 'empty_fields') {
                    echo "Please fill in all fields.";
                } elseif ($_GET['error'] === 'weak_password') {
                    echo "Password must be at least 6 characters.";
                }
                ?>
            </p>
        <?php endif; ?>

        <form id="resetForm" action="backend/reset-password-backend.php" method="POST">
            <input name="new_password" type="password" placeholder="New Password" required>
            <input name="confirm_password" type="password" placeholder="Confirm New Password" required>
            <button type="submit">Reset Password</button>
        </form>
    </main>

    <script src="javascript/script.js"></script>
</body>
</html>