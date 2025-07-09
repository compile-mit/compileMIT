<?php
// Start the session
session_start();

// DB connection
$host = 'localhost';
$user = 'your_user';
$password = 'your_password';
$dbname = 'compilemit';
$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$msg = '';
$email = '';
$step = 'request';

// Handle send OTP
if (isset($_POST['request_otp'])) {
    $email = trim($_POST['email']);
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $otp = rand(100000, 999999);
        $expires = date("Y-m-d H:i:s", strtotime("+10 minutes"));

        $update = $conn->prepare("UPDATE users SET otp = ?, otp_expires_at = ? WHERE email = ?");
        $update->bind_param("sss", $otp, $expires, $email);
        $update->execute();

        // Send OTP via email
        $subject = "Your OTP Code";
        $body = "Your OTP is: $otp\nThis will expire in 10 minutes.";
        $headers = "From: CompileMIT <no-reply@compilemit.com>";
        mail($email, $subject, $body, $headers);

        $step = 'verify';
        $msg = "OTP sent to your email.";
    } else {
        $msg = "Email not found.";
    }
}

// Handle verify OTP
if (isset($_POST['verify_otp'])) {
    $email = $_POST['email'];
    $otp = $_POST['otp'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("SELECT otp, otp_expires_at FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($db_otp, $expires_at);
    $stmt->fetch();
    $stmt->close();

    if ($otp === $db_otp && strtotime($expires_at) > time()) {
        $update = $conn->prepare("UPDATE users SET password = ?, otp = NULL, otp_expires_at = NULL WHERE email = ?");
        $update->bind_param("ss", $new_password, $email);
        $update->execute();
        $msg = "✅ Password reset successful! <a href='login.html'>Login</a>";
        $step = 'done';
    } else {
        $msg = "❌ Invalid or expired OTP.";
        $step = 'verify';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password</title>
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
      border-radius: 10px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
      max-width: 400px;
      width: 100%;
    }
    h2 {
      text-align: center;
      color: #1e40af;
    }
    label {
      display: block;
      margin: 1rem 0 0.5rem;
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
    a { color: #1e40af; text-decoration: none; }
  </style>
</head>
<body>
  <div class="box">
    <h2>Forgot Password</h2>

    <?php if ($step === 'request'): ?>
    <form method="POST">
      <label>Email</label>
      <input type="email" name="email" required>
      <button type="submit" name="request_otp">Send OTP</button>
    </form>

    <?php elseif ($step === 'verify'): ?>
    <form method="POST">
      <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
      <label>Enter OTP</label>
      <input type="text" name="otp" required>
      <label>New Password</label>
      <input type="password" name="new_password" required>
      <button type="submit" name="verify_otp">Reset Password</button>
    </form>

    <?php elseif ($step === 'done'): ?>
    <p style="text-align:center"><?= $msg ?></p>
    <?php endif; ?>

    <?php if (!empty($msg) && $step !== 'done'): ?>
      <div class="msg"><?= $msg ?></div>
    <?php endif; ?>
  </div>
</body>
</html>
