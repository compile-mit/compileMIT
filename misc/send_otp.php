<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // PHPMailer

$host = 'localhost';
$user = 'your_user';
$pass = 'your_pass';
$db = 'compilemit';

$conn = new mysqli($host, $user, $pass, $db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

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

        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.yourhost.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'your_email@domain.com';
            $mail->Password = 'your_email_password';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('your_email@domain.com', 'CompileMIT');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Your OTP Code';
            $mail->Body = "<h3>Your OTP is: <strong>$otp</strong></h3><p>This will expire in 10 minutes.</p>";

            $mail->send();
            header("Location: verify_otp.html?email=$email");
            exit();
        } catch (Exception $e) {
            echo "OTP could not be sent. Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "Email not found.";
    }
}
?>
