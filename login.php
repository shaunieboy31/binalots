<?php
session_start();
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$conn = new mysqli("localhost", "root", "", "binalots");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$error = $success = "";

// Handle resend OTP request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['resend'])) {
    if (isset($_SESSION['email'])) {
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'vergaracristel0@gmail.com'; // Your Gmail
            $mail->Password = 'wmtq ynhw pshk rmiz'; // App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('vergaracristel0@gmail.com', 'Binalots OTP');
            $mail->addAddress($_SESSION['email']);

            $mail->isHTML(true);
            $mail->Subject = 'Your OTP Code';
            $mail->Body    = "Your OTP code is <b>$otp</b>";

            $mail->send();
            $success = "OTP resent to your email.";
        } catch (Exception $e) {
            $error = "Mailer Error: " . $mail->ErrorInfo;
        }
    } else {
        $error = "Session expired. Please login again.";
        unset($_SESSION['otp']);
    }
}
// Handle login and OTP verification
elseif ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['email'], $_POST['password']) && !isset($_POST['otp'])) {
        // Login step: verify credentials and send OTP
        $email = $_POST['email'];
        $password = $_POST['password'];

        $sql = "SELECT * FROM users WHERE email='$email'";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['email'] = $email;
                $_SESSION['username'] = $user['name']; // Store the user's name in the session

                // Generate OTP
                $otp = rand(100000, 999999);
                $_SESSION['otp'] = $otp;

                // Send OTP email
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'vergaracristel0@gmail.com';
                    $mail->Password = 'wmtq ynhw pshk rmiz'; // App Password
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    $mail->setFrom('vergaracristel0@gmail.com', 'Binalots OTP');
                    $mail->addAddress($email);

                    $mail->isHTML(true);
                    $mail->Subject = 'Your OTP Code';
                    $mail->Body    = "Your OTP code is <b>$otp</b>";

                    $mail->send();
                    $success = "OTP sent to your email. Please enter it below.";
                } catch (Exception $e) {
                    $error = "Mailer Error: " . $mail->ErrorInfo;
                }
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "No account found with that email.";
        }
    } elseif (isset($_POST['otp'])) {
        // OTP verification step
        $entered_otp = $_POST['otp'];
        if (isset($_SESSION['otp']) && $entered_otp == $_SESSION['otp']) {
            unset($_SESSION['otp']);
            header("Location: home.php");
            exit();
        } else {
            $error = "Invalid OTP. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Login Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="css/first.css" />
</head>
<body>
<div class="login-container">
    <div class="logo-section">
        <img src="img/logo2.png" alt="Logo" />
    </div>
    <div class="form-section">
        <h2>LOG IN</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php elseif (!empty($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (empty($_SESSION['otp'])): ?>
        <!-- Login form -->
        <form method="POST">
            <div class="mb-3">
                <label>Email</label>
                <input name="email" type="email" class="form-control" required />
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input name="password" id="password" type="password" class="form-control" required />
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" id="showPassword" class="form-check-input" />
                <label for="showPassword" class="form-check-label">Show Password</label>
            </div>
            <button type="submit" class="btn btn-primary w-100">Log In</button>
            <div class="text-center mt-2">
                <a href="register.php">Click to Register!</a>
            </div>
        </form>
        <?php else: ?>
        <!-- OTP form -->
        <form method="POST">
            <div class="mb-3">
                <label>Enter OTP</label>
                <input name="otp" type="text" class="form-control" required />
            </div>
            <button type="submit" class="btn btn-success w-100">Verify OTP</button>
        </form>
        <div class="text-center mt-2">
            <form method="POST" style="display: inline;">
                <input type="hidden" name="resend" value="1" />
                <button type="submit" class="btn btn-link p-0">Resend OTP</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.getElementById('showPassword').addEventListener('change', function () {
        const pwd = document.getElementById('password');
        pwd.type = this.checked ? 'text' : 'password';
    });
</script>
</body>
</html>