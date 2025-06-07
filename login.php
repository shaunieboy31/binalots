<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "binalots";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Password is correct, set session and redirect to home.php
            session_start();
            $_SESSION['username'] = $row['name']; // Store the user's name in session
            header("Location: home.php");
            exit();
        } else {
            // Password is incorrect
            $error = "Invalid password.";
        }
    } else {
        // Email not found
        $error = "No account found with that email.";
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/first.css">
</head>
<body>
    <div class="login-container">
        <!-- Left Logo Section -->
        <div class="logo-section">
            <img src="img/logo2.png" alt="Logo">
        </div>

        <!-- Right Form Section -->
        <div class="form-section">
            <h2>LOG IN</h2>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            <form id="loginForm" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="text" id="email" name="email" class="form-control" placeholder="Enter your Email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="form-check">
                        <input type="checkbox" id="showPassword" class="form-check-input">
                        <label for="showPassword" class="form-check-label">Show Password</label>
                    </div>
                    <a href="register.php" class="register" style="color: inherit;">Click to Register!</a>
                </div>
                <button type="submit" class="btn btn-login w-100 mt-3">
                    Log In
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const showPasswordCheckbox = document.getElementById('showPassword');
        const passwordInput = document.getElementById('password');

        showPasswordCheckbox.addEventListener('change', () => {
            passwordInput.type = showPasswordCheckbox.checked ? 'text' : 'password';
        });
    </script>
</body>
</html>