<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
            <button onclick="navigateTo('Templates/register.html')">
                <img src="components/icons/register.png" class="icon">Register</button>

        </div>
    </nav>
    
    <main>
        <h2>Login</h2>
        
        <?php if (isset($_GET['error'])): ?>
    <p style="color:red; font-weight:bold;">
        <?php
        if ($_GET['error'] === 'incorrect_id') {
            echo " Incorrect Roll Number";
        } elseif ($_GET['error'] === 'incorrect_password') {
            echo " Incorrect Password";
        } elseif ($_GET['error'] === 'empty_fields') {
            echo " Please fill in all fields";
        }
        ?>
    </p>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
    <p style="color:green; font-weight:bold;">
        <?php
        if ($_GET['success'] === 'password_reset') {
            echo "Password reset successfully. Please login.";
        }
        ?>
    </p>
<?php endif; ?>

        <form id="loginForm" action="backend/login.php" method="POST" autocomplete="off">
            <input name="roll" type="number" placeholder="Roll No." required>
            <input name="password" type="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>

       <p> <a href="backend/forgot-password.php">Forgot Password?</a> </p>
       <p> Login as <img src="components/icons/Admin.png" class="icon"> <a href="Templates/admin-login.html">Admin</a> </p>
    </main>

    <script src="javascript/script.js"></script>
</body>
</html>