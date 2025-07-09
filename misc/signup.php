<?php
$host = 'localhost';
$db   = 'compilemit';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

$msg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST["name"]);
    $email = htmlspecialchars($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $msg = "❌ Email already exists. Please <a href='login.php'>log in</a> instead.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $password);

        if ($stmt->execute()) {
            $msg = "✅ Registered successfully! <a href='login.php'>Login here</a>";
        } else {
            $msg = "❌ Error: " . $stmt->error;
        }

        $stmt->close();
    }
    $check->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Sign Up - CompileMIT</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f4f6f9;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .container {
      background: white;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 0 20px rgba(0,0,0,0.1);
      width: 350px;
    }
    h2 {
      text-align: center;
      margin-bottom: 1.5rem;
      color: #333;
    }
    input[type="text"], input[type="email"], input[type="password"] {
      width: 100%;
      padding: 10px;
      margin: 0.5rem 0;
      border: 1px solid #ccc;
      border-radius: 8px;
    }
    button {
      width: 100%;
      padding: 10px;
      margin-top: 1rem;
      border: none;
      background-color: #4CAF50;
      color: white;
      font-size: 16px;
      border-radius: 8px;
      cursor: pointer;
    }
    button:hover {
      background-color: #45a049;
    }
    .message {
      text-align: center;
      margin-top: 1rem;
      color: #d00;
    }
    .message a {
      color: #007BFF;
      text-decoration: none;
    }
    .message a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Create Account</h2>
    <form method="POST">
      <input type="text" name="name" placeholder="Full Name" required />
      <input type="email" name="email" placeholder="Email" required />
      <input type="password" name="password" placeholder="Password" required minlength="6" />
      <button type="submit">Sign Up</button>
    </form>
    <?php if (!empty($msg)): ?>
      <div class="message"><?= $msg ?></div>
    <?php endif; ?>
  </div>
</body>
</html>
