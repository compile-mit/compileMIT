<?php
session_start();

// DB connection
$conn = new mysqli('localhost', 'root', '', 'compilemit');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$msg = '';
$page = $_GET['page'] ?? 'login'; // 'login', 'forgot', 'reset'

// Handle Login
if (isset($_POST['login'])) {
  $email = trim($_POST['email']);
  $password = $_POST['password'];

  $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows === 1) {
    $stmt->bind_result($hashed);
    $stmt->fetch();
    if (password_verify($password, $hashed)) {
      $msg = "✅ Login successful!";
      $page = 'login';
    } else {
      $msg = "❌ Incorrect password.";
    }
  } else {
    $msg = "❌ Email not found.";
  }
}

// Send OTP
if (isset($_POST['send_otp'])) {
  $email = trim($_POST['email']);
  $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows === 1) {
    $otp = rand(100000, 999999);
    $expires = date("Y-m-d H:i:s", strtotime("+10 minutes"));

    $update = $conn->prepare("UPDATE users SET otp = ?, otp_expires_at = ? WHERE email = ?");
    $update->bind_param("sss", $otp, $expires, $email);
    $update->execute();

    // Send email using PHP's mail()
    $subject = "Your OTP Code";
    $message = "Your OTP is: $otp. It will expire in 10 minutes.";
    $headers = "From: no-reply@compilemit.com";

    if (mail($email, $subject, $message, $headers)) {
      $msg = "OTP sent to your email.";
      $page = 'reset';
    } else {
      $msg = "❌ Failed to send email.";
    }
  } else {
    $msg = "❌ Email not registered.";
  }
}

// Reset password
if (isset($_POST['reset_pass'])) {
  $email = $_POST['email'];
  $otp = $_POST['otp'];
  $new_pass = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

  $stmt = $conn->prepare("SELECT otp, otp_expires_at FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $stmt->bind_result($db_otp, $expires);
  $stmt->fetch();
  $stmt->close();

  if ($otp === $db_otp && strtotime($expires) > time()) {
    $update = $conn->prepare("UPDATE users SET password = ?, otp = NULL, otp_expires_at = NULL WHERE email = ?");
    $update->bind_param("ss", $new_pass, $email);
    $update->execute();
    $msg = "✅ Password reset successful. You can now login.";
    $page = 'login';
  } else {
    $msg = "❌ Invalid or expired OTP.";
    $page = 'reset';
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Auth System</title>
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: #f3f4f6;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .box {
      background: white;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 400px;
    }
    h2 {
      text-align: center;
      color: #1e40af;
    }
    label {
      display: block;
      margin-top: 1rem;
      color: #374151;
    }
    input {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      background: #f9fafb;
    }
    button {
      margin-top: 1.5rem;
      width: 100%;
      padding: 0.75rem;
      background: #3b82f6;
      color: white;
      border: none;
      border-radius: 8px;
      font-weight: bold;
      cursor: pointer;
    }
    .msg {
      margin-top: 1rem;
      text-align: center;
      color: #dc2626;
    }
    .switch-link {
      text-align: center;
      margin-top: 1rem;
    }
    .switch-link a {
      color: #1e40af;
      text-decoration: none;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <div class="box">
    <?php if ($page === 'login'): ?>
      <h2>Login</h2>
      <form method="POST">
        <label>Email</label>
        <input type="email" name="email" autofill required />
        <label>Password</label>
        <input type="password" name="password" required />
        <button type="submit" name="login">Login</button>
      </form>
      <div class="switch-link">
        <!-- <a href="?page=forgot">Forgot Password?</a> -->
      </div>

    <?php elseif ($page === 'forgot'): ?>
      <h2>Forgot Password</h2>
      <form method="POST">
        <label>Enter your email</label>
        <input type="email" name="email" required />
        <button type="submit" name="send_otp">Send OTP</button>
      </form>
      <div class="switch-link">
        <a href="?page=login">← Back to Login</a>
      </div>

    <?php elseif ($page === 'reset'): ?>
      <h2>Reset Password</h2>
      <form method="POST">
        <input type="hidden" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" />
        <label>OTP</label>
        <input type="text" name="otp" required />
        <label>New Password</label>
        <input type="password" name="new_password" required />
        <button type="submit" name="reset_pass">Reset Password</button>
      </form>
      <div class="switch-link">
        <a href="?page=login">← Back to Login</a>
      </div>
    <?php endif; ?>

    <?php if ($msg): ?>
      <div class="msg"><?= $msg ?></div>
    <?php endif; ?>
  </div>
</body>
</html>
