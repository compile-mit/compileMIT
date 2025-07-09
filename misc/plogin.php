<?php
session_start();

$host = 'localhost';
$user = 'your_db_user';
$password = 'your_db_pass';
$database = 'compilemit';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$psw = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($email) || empty($psw)) {
    die("Email and password are required.");
}

$stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    if (password_verify($psw, $row['password'])) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['email'] = $email;
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Invalid password.";
    }
} else {
    echo "No account found with that email.";
}

$stmt->close();
$conn->close();
?>
