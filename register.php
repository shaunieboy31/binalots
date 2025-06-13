<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

$conn = new mysqli("localhost", "root", "", "Binalots");

session_start();

$error = "";
$success = "";
$step = 1; // 1 = register form, 2 = otp verify form, 3 = success message

// Registration form submitted
if (isset($_POST['register'])) {
    $name = trim(mysqli_real_escape_string($conn, $_POST['name']));
    $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (!preg_match("/^[a-zA-Z0-9._%+-]+@(yahoo|gmail)\.com$/", $email)) {
        $error = "Please fill up the folowing fields correctly.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $error = "The email address is already registered.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $otp = rand(100000, 999999);

            $insert = "INSERT INTO users (name, email, password, otp, is_verified) 
                       VALUES ('$name', '$email', '$hashed_password', '$otp', 0)";
            if ($conn->query($insert) === TRUE) {
                // Send OTP email function
                if (sendOTPEmail($email, $name, $otp)) {
                    $_SESSION['email'] = $email;
                    $step = 2; // show OTP verification form
                } else {
                    $error = "Failed to send OTP email.";
                }
            } else {
                $error = "Database error: " . $conn->error;
            }
        }
    }
}

// OTP verification submitted
if (isset($_POST['verify_otp'])) {
    $otp_input = $_POST['otp'];
    $email = $_SESSION['email'] ?? '';

    if (empty($email)) {
        $error = "Session expired. Please register again.";
        $step = 1;
    } else {
        $sql = "SELECT * FROM users WHERE email='$email' AND otp='$otp_input'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            // Mark verified and clear OTP
            $conn->query("UPDATE users SET is_verified=1, otp=NULL WHERE email='$email'");
            $success = "Your account has been verified! Redirecting to login...";
            session_destroy();
            echo "<script>
            setTimeout(function() {
            window.location.href = 'login.php';
            }, 3000);
            </script>";
            $step = 3; // verification success
        } else {
            $error = "Invalid OTP. Please try again.";
            $step = 2;
        }
    }
}

// Resend OTP submitted
if (isset($_POST['resend_otp'])) {
    $email = $_SESSION['email'] ?? '';

    if (empty($email)) {
        $error = "Session expired. Please register again.";
        $step = 1;
    } else {
        $new_otp = rand(100000, 999999);

        // Update OTP in DB
        if ($conn->query("UPDATE users SET otp='$new_otp' WHERE email='$email'") === TRUE) {
            // Get user's name for email
            $result = $conn->query("SELECT name FROM users WHERE email='$email'");
            $row = $result->fetch_assoc();
            $name = $row['name'];

            if (sendOTPEmail($email, $name, $new_otp)) {
                $success = "A new OTP has been sent to your email.";
                $step = 2;
            } else {
                $error = "Failed to send OTP email.";
            }
        } else {
            $error = "Failed to update OTP in database.";
        }
    }
}

function sendOTPEmail($email, $name, $otp) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'vergaracristel0@gmail.com'; // your Gmail
        $mail->Password = 'wmtq ynhw pshk rmiz'; // your app password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('vergaracristel0@gmail.com', 'Binalots');
        $mail->addAddress($email);
        $mail->Subject = 'Your OTP Code';
        $mail->Body = "Hello $name,\nYour OTP code is: $otp";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Register with OTP Verification</title>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" />
<link rel="stylesheet" href="css/reg.css" />
</head>
<body>

<div class="card-wrapper">
    <div class="brand">
        <img src="img/logo2.png" alt="Logo" />
    </div>
    <div class="card">
        <div class="card-body">

            <?php if ($step == 1): ?>
                <h4 class="card-title">Register</h4>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST" novalidate>
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input id="name" type="text" class="form-control" name="name" required autofocus />
                        <div id="nameError" class="text-danger" style="display:none;"></div>
                    </div>
                    <div class="form-group">
                        <label for="email">E-Mail Address</label>
                        <input id="email" type="email" class="form-control" name="email" required />
                        <div id="emailError" class="text-danger" style="display:none;"></div>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input id="password" type="password" class="form-control" name="password" required />
                        <input type="checkbox" onclick="togglePassword()"> Show Password
                        <div id="passwordError" class="text-danger" style="display:none;"></div>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input id="confirm_password" type="password" class="form-control" name="confirm_password" required />
                        <input type="checkbox" onclick="toggleConfirm()"> Show Password
                        <div id="confirmError" class="text-danger" style="display:none;"></div>
                    </div>
                    <div class="form-group m-0">
                        <button type="submit" name="register" class="btn btn-primary btn-block">Register</button>
                        <br />
                        <center><a href="login.php" class="btn btn-secondary btn-block">Go back to login</a></center>
                    </div>
                </form>

            <?php elseif ($step == 2): ?>
                <h4 class="card-title">Verify OTP</h4>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <form method="POST" novalidate>
                    <div class="form-group">
                        <label for="otp">Enter OTP sent to your email</label>
                        <input id="otp" type="text" class="form-control" name="otp" required autofocus />
                    </div>
                    <div class="form-group m-0">
                        <button type="submit" name="verify_otp" class="btn btn-primary btn-block">Verify</button>
                        <button type="submit" name="resend_otp" class="btn btn-link btn-block">Resend OTP</button>
                    </div>
                </form>

            <?php elseif ($step == 3): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
function togglePassword() {
    const pass = document.getElementById("password");
    pass.type = pass.type === "password" ? "text" : "password";
}
function toggleConfirm() {
    const pass = document.getElementById("confirm_password");
    pass.type = pass.type === "password" ? "text" : "password";
}

// Real-time validation for all fields
function validateEmail(email) {
    return /^[a-zA-Z0-9._%+-]+@(yahoo|gmail)\.com$/.test(email);
}
function validateName(name) {
    return name.trim().length > 0;
}
function validatePassword(password) {
    return password.length >= 6;
}
function validateConfirm(password, confirm) {
    return password === confirm && confirm.length > 0;
}

document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirm_password');

    const nameError = document.getElementById('nameError');
    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');
    const confirmError = document.getElementById('confirmError');

    nameInput.addEventListener('input', function() {
        if (!validateName(this.value)) {
            nameError.textContent = "Name is required.";
            nameError.style.display = "block";
        } else {
            nameError.textContent = "";
            nameError.style.display = "none";
        }
    });

    emailInput.addEventListener('input', function() {
        if (this.value.length > 0 && !validateEmail(this.value)) {
            emailError.textContent = "Please provide a valid email address (e.g., @yahoo.com or @gmail.com).";
            emailError.style.display = "block";
        } else {
            emailError.textContent = "";
            emailError.style.display = "none";
        }
    });

    passwordInput.addEventListener('input', function() {
        if (!validatePassword(this.value)) {
            passwordError.textContent = "Password must be at least 6 characters.";
            passwordError.style.display = "block";
        } else {
            passwordError.textContent = "";
            passwordError.style.display = "none";
        }
        // Also check confirm password
        if (confirmInput.value.length > 0) {
            if (!validateConfirm(this.value, confirmInput.value)) {
                confirmError.textContent = "Passwords do not match.";
                confirmError.style.display = "block";
            } else {
                confirmError.textContent = "";
                confirmError.style.display = "none";
            }
        }
    });

    confirmInput.addEventListener('input', function() {
        if (!validateConfirm(passwordInput.value, this.value)) {
            confirmError.textContent = "Passwords do not match.";
            confirmError.style.display = "block";
        } else {
            confirmError.textContent = "";
            confirmError.style.display = "none";
        }
    });

    // Show errors on submit if any field is invalid
    document.querySelector('form[method="POST"]').addEventListener('submit', function(e) {
        let hasError = false;

        if (!validateName(nameInput.value)) {
            nameError.textContent = "Name is required.";
            nameError.style.display = "block";
            hasError = true;
        }
        if (!validateEmail(emailInput.value)) {
            emailError.textContent = "Please provide a valid email address (e.g., @yahoo.com or @gmail.com).";
            emailError.style.display = "block";
            hasError = true;
        }
        if (!validatePassword(passwordInput.value)) {
            passwordError.textContent = "Password must be at least 6 characters.";
            passwordError.style.display = "block";
            hasError = true;
        }
        if (!validateConfirm(passwordInput.value, confirmInput.value)) {
            confirmError.textContent = "Passwords do not match.";
            confirmError.style.display = "block";
            hasError = true;
        }
        if (hasError) {
            e.preventDefault();
        }
    });
});
</script>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>
</html>