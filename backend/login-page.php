<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../Design/style.css">
</head>
<body>
    <nav>
     <div class="nav-left">
            <button onclick="navigateTo('../templates/Index.html')"> 
                <img src="../components/icons/home.png" class="icon"> Home </button>

        </div>
        <div class="nav-right">
            <button onclick="navigateTo('../templates/register.html')">
                <img src="../components/icons/register.png" class="icon">Register</button>

        </div>
    </nav>
    
    <main>
        <h2>Login</h2>
        
        <?php if (isset($_GET['error'])): ?>
    <p style="color:red; font-weight:bold;">
        <?php
        if ($_GET['error'] === 'invalid_login') {
            echo "❌ Invalid Roll Number or Password";
        } elseif ($_GET['error'] === 'empty_fields') {
            echo "❌ Please fill in all fields";
        }
        ?>
    </p>
<?php endif; ?>

        <form id="loginForm" action="../Backend/login.php" method="POST" autocomplete="off">
            <input name="roll" type="Number" placeholder="Roll No." required>
            <input name="password" type="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>

       <p> Login as <img src="../components/icons/Admin.png" class="icon"> <a href="../templates/admin-login.html">Admin</a> </p>
    </main>

    <script src="../JavaScript/script.js"></script>
</body>
</html>