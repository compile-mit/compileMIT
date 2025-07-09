<?php
// PHP logic
$showAlert = true; // Change this based on your condition
?>

<!DOCTYPE html>
<html>
<head>
    <title>SweetAlert in PHP</title>
    <!-- Include SweetAlert from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<h2>Welcome to the Page</h2>

<?php if ($showAlert): ?>
    <script>
        // Show SweetAlert when page loads
        Swal.fire({
            title: 'Success!',
            text: 'This is a SweetAlert box from PHP!',
            icon: 'success',
            confirmButtonText: 'Cool'
        });
    </script>
<?php endif; ?>

</body>
</html>
