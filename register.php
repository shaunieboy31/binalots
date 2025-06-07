<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Binalots";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$success = "";
$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Email and password validation
    if (!preg_match("/^[a-zA-Z0-9._%+-]+@(yahoo|gmail)\.com$/", $email)) {
        $error = "Please provide a valid email address (e.g., @yahoo.com or @gmail.com).";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email already exists in the database
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            // If the email is found, show an error
            $error = "The email address is already registered. Please use a different email.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user into database
            $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$hashed_password')";

            if ($conn->query($sql) === TRUE) {
                $success = "New record created successfully";
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'login.php';
                    }, 2000);
                </script>";
            } else {
                $error = "Error: " . $sql . "<br>" . $conn->error;
            }
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="author" content="Your Name">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/reg.css">
</head>
<body>
    <div class="card-wrapper">
        <div class="brand">
            <img src="img/logo2.png" alt="Logo">
        </div>
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Register</h4>
                
                <!-- Display success or error messages -->
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $success; ?>
                    </div>
                <?php elseif (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form id="registrationForm" method="POST" class="my-login-validation" novalidate="">

                    <div class="form-group">
                        <label for="name">Name</label>
                        <input id="name" type="text" class="form-control" name="name" required autofocus>
                        <div class="invalid-feedback">
                            What's your name?
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">E-Mail Address</label>
                        <input id="email" type="email" class="form-control" name="email" required>
                        <div class="invalid-feedback">
                            Please provide a valid email address (e.g., @yahoo.com or @gmail.com).
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input id="password" type="password" class="form-control" name="password" required>
                        <div class="invalid-feedback">
                            Password is required
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input id="confirm_password" type="password" class="form-control" name="confirm_password" required>
                        <div class="invalid-feedback">
                            Passwords do not match
                        </div>
                    </div>

                    <div class="form-group m-0">
                        <button type="submit" class="btn btn-primary btn-block">
                            Register
                        </button>
                        <br>
                        <center><a href="login.php" class="btn btn-secondary btn-block">Go back to login</a></center>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script>
        const emailInput = document.getElementById("email");
        const passwordInput = document.getElementById("password");
        const confirmPasswordInput = document.getElementById("confirm_password");
        const emailPattern = /^[a-zA-Z0-9._%+-]+@(yahoo|gmail)\.com$/;

        // Real-time email validation
        emailInput.addEventListener("keyup", function () {
            if (!emailPattern.test(emailInput.value)) {
                emailInput.classList.add("is-invalid");
                emailInput.nextElementSibling.textContent = "Please provide a valid email address (e.g., @yahoo.com or @gmail.com).";
            } else {
                emailInput.classList.remove("is-invalid");
                emailInput.nextElementSibling.textContent = "";
            }
        });

        // Real-time password match check
        confirmPasswordInput.addEventListener("input", function () {
            if (passwordInput.value !== confirmPasswordInput.value) {
                confirmPasswordInput.classList.add("is-invalid");
            } else {
                confirmPasswordInput.classList.remove("is-invalid");
            }
        });

        // Final check on form submission
        document.getElementById("registrationForm").addEventListener("submit", function (event) {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;

            // Email check
            if (!emailPattern.test(emailInput.value)) {
                emailInput.classList.add("is-invalid");
                event.preventDefault();
            }

            // Password match check
            if (password !== confirmPassword) {
                confirmPasswordInput.classList.add("is-invalid");
                event.preventDefault();
            } else {
                confirmPasswordInput.classList.remove("is-invalid");
            }
        });
    </script>
</body>
</html>
