<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
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
        <h2>Forgot Password</h2>
        
        <?php if (isset($_GET['error'])): ?>
            <p style="color:red; font-weight:bold;">
                <?php
                if ($_GET['error'] === 'user_not_found') {
                    echo "User not found with the provided details.";
                } elseif ($_GET['error'] === 'send_failed') {
                    echo "Failed to send OTP. Please try again.";
                }
                ?>
            </p>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <p style="color:green; font-weight:bold;">OTP sent successfully. Check your email.</p>
        <?php endif; ?>

        <form id="forgotForm" action="backend/send-otp.php" method="POST">
            <input name="identifier" type="text" placeholder="Roll Number or Email" required>
            <button type="submit">Send OTP</button>
        </form>
    </main>

    <script src="javascript/script.js"></script>
</body>
</html>