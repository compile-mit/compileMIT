<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database configuration
$host = 'localhost';
$db   = 'compilemit';
$user = 'root';
$pass = '';

// Create a new MySQLi connection
$conn = new mysqli($host, $user, $pass, $db);

// Enable exception mode for mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Set character encoding (optional but recommended)
$conn->set_charset("utf8mb4");

// Check if request method is POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize and retrieve inputs
    $name = htmlspecialchars(trim($_POST["name"]));
    $contact = htmlspecialchars(trim($_POST["contact"]));
    $session = htmlspecialchars(trim($_POST["session"]));
    $section = htmlspecialchars(trim($_POST["section"]));
    $message = htmlspecialchars(trim($_POST["message"]));

    // Check for empty fields
    if (empty($name) || empty($contact) || empty($session) || empty($section) || empty($message)) {
        echo "
        <html>
        <head><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head>
        <body>
            <script>
                Swal.fire({
                    title: 'Missing Fields',
                    text: 'All fields are required.',
                    icon: 'warning',
                    confirmButtonText: 'Back'
                }).then(() => {
                    window.history.back();
                });
            </script>
        </body>
        </html>";
        exit;
    }

    try {
        // Prepare the insert query
        $stmt = $conn->prepare("INSERT INTO details (name, contact, session, section, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $contact, $session, $section, $message);
        $stmt->execute();

        // Success response
        echo "
        <html>
        <head><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head>
        <body>
            <script>
                Swal.fire({
                    title: 'Success!',
                    text: 'Form submitted successfully!',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'home';
                });
            </script>
        </body>
        </html>";

    } catch (mysqli_sql_exception $e) {
        $errorCode = $e->getCode();
        $errorMessage = addslashes($e->getMessage());

        // Duplicate entry error
        if ($errorCode == 1062) {
            echo "
            <html>
            <head><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head>
            <body>
                <script>
                    Swal.fire({
                        title: 'Already Submitted',
                        text: 'You have already submitted the form.',
                        icon: 'info',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = 'home';
                    });
                </script>
            </body>
            </html>";
        } else {
            // General database error
            echo "
            <html>
            <head><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head>
            <body>
                <script>
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed: $errorMessage',
                        icon: 'error',
                        confirmButtonText: 'Back'
                    }).then(() => {
                        window.history.back();
                    });
                </script>
            </body>
            </html>";
        }
    }

    // Close statement and connection
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();

} else {
    echo "Invalid request.";
}
?>
