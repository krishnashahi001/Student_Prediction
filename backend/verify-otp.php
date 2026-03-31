<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
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
        <h2>Verify OTP</h2>
        
        <?php if (isset($_GET['error'])): ?>
            <p style="color:red; font-weight:bold;">
                <?php
                if ($_GET['error'] === 'invalid_otp') {
                    echo "Invalid or expired OTP.";
                } elseif ($_GET['error'] === 'empty_fields') {
                    echo "Please enter the OTP.";
                }
                ?>
            </p>
        <?php endif; ?>

        <form id="otpForm" action="backend/verify-otp-backend.php" method="POST">
            <input name="otp" type="text" placeholder="Enter 6-digit OTP" maxlength="6" required>
            <button type="submit">Verify OTP</button>
        </form>
    </main>

    <script src="javascript/script.js"></script>
</body>
</html>