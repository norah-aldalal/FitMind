<?php
session_start();
$errorMsg = "";

if (isset($_GET['error'])) {
  $errorMsg = htmlspecialchars($_GET['error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login - FitMind</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f0fdf4;
      margin: 0;
      color: #14532d;
    }
    .page-container {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }
    .login-box {
      margin: auto;
      background: #fff;
      border-radius: 25px;
      width: 100%;
      max-width: 500px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
      margin-top: 50px;
      margin-bottom: 50px;
    }
    .login-header {
      background: #22c55e;
      padding: 35px;
      text-align: center;
      color: white;
    }
    .login-header h2 {
      margin: 0;
      font-size: 30px;
    }
    form {
      padding: 35px;
    }
    .form-group {
      margin-bottom: 22px;
    }
    label {
      display: block;
      margin-bottom: 6px;
      font-weight: bold;
      font-size: 15px;
    }
    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 14px;
      font-size: 16px;
      border: 1.5px solid #22c55e;
      border-radius: 8px;
      outline: none;
      transition: 0.3s ease;
      box-sizing: border-box;
    }
    input[type="email"]:focus,
    input[type="password"]:focus {
      border-color: #14532d;
      background: #dcfce7;
    }
    .login-btn {
      width: 100%;
      padding: 12px;
      border: none;
      background: #22c55e;
      color: white;
      font-size: 16px;
      font-weight: bold;
      border-radius: 8px;
      cursor: pointer;
    }
    .login-btn:hover {
      background: #14532d;
    }
    .error-message {
      color: red;
      text-align: center;
      margin-bottom: 15px;
    }
    .login-footer {
      text-align: center;
      padding: 20px;
      background: #f9fafb;
      border-top: 1px solid #dcfce7;
      font-size: 14px;
    }
    .login-footer a {
      color: #22c55e;
      text-decoration: none;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <header class="header">
    <img class="logo" src="Image/Logo3.png" alt="FitMind Logo">
    <h1>FitMind</h1>
  </header>

  <div class="page-container">
    <div class="login-box">
      <div class="login-header">
        <h2>FitMind Login</h2>
      </div>

      <?php if (!empty($errorMsg)): ?>
        <div class="error-message"><?= $errorMsg ?></div>
      <?php endif; ?>

      <!--  The form sends data to LoginCheck.php -->
      <form method="POST" action="LoginCheck.php">
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="Enter your email" required>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </div>

        <button type="submit" class="login-btn">Login</button>
      </form>

      <div class="login-footer">
        <p>New to FitMind? <a href="Sign_up.html">Create Account</a></p>
      </div>
    </div>
  </div>
</body>
</html>
