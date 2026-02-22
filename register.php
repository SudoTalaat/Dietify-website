<?php
require 'db_connect.php';

$error = '';
$success = '';
$username = '';
$email = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $terms = isset($_POST['terms']);

    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = "Please fill in all required fields.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    } elseif (!$terms) {
        $error = "You must agree to the terms and conditions.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // hCaptcha Verification
        $data = array(
            'secret' => "ES_1e91e23039e44bab814cb1ed58a8b022",
            'response' => $_POST['h-captcha-response']
        );
        $verify = curl_init();
        curl_setopt($verify, CURLOPT_URL, "https://hcaptcha.com/siteverify");
        curl_setopt($verify, CURLOPT_POST, true);
        curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($verify);
        $response_data = json_decode($response);

        if (!$response_data->success) {
            $error = "Please complete the captcha.";
        } else {
            // Check if email or username already exists using Prepared Statement
            $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
            $checkStmt->bind_param("ss", $email, $username);
            $checkStmt->execute();
            $result = $checkStmt->get_result();

            if ($result->num_rows > 0) {
                $error = "Username or Email already exists.";
            } else {
                // Insert user using Prepared Statement
                // password_hash() with PASSWORD_BCRYPT automatically generates a secure, random salt.
                // The salt is included in the resulting hash string.
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $insertStmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $insertStmt->bind_param("sss", $username, $email, $hashedPassword);

                if ($insertStmt->execute() === TRUE) {
                    // Redirect to login page with success message (or handle here)
                    header("Location: login.php?registered=1");
                    exit();
                } else {
                    $error = "Error: " . $insertStmt->error;
                }
                $insertStmt->close();
            }
            $checkStmt->close();
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Healthy Food</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .server-error {
            color: #ff4d4d;
            font-size: 0.9rem;
            margin-top: 10px;
            text-align: center;
            display: block;
        }
    </style>
    <script src='https://js.hcaptcha.com/1/api.js' async defer></script>
</head>

<body>
    <div class="page-wrapper">
        <div class="illustration-section">
            <div class="illustration-content">
                <h2>Join Us!</h2>
                <p>Start your healthy journey today</p>
            </div>
        </div>
        <div class="form-section">
            <div class="register-card">
                <div class="card-header">
                    <h1>Create Account</h1>
                    <p>Join us and start your healthy journey</p>
                </div>

                <form class="register-form" method="POST" action="register.php">


                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username"
                            value="<?php echo htmlspecialchars($username); ?>" required>
                        <span class="error-message" id="usernameError"></span>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>"
                            required>
                        <span class="error-message" id="emailError"></span>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                        <span class="error-message" id="passwordError"></span>
                    </div>

                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" required>
                        <span class="error-message" id="confirmPasswordError"></span>
                    </div>

                    <?php if ($error): ?>
                        <span class="server-error"><?php echo $error; ?></span>
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="checkbox-container">
                            <input type="checkbox" id="terms" name="terms" required>
                            <span class="checkmark"></span>
                            I agree to the <a href="#" class="terms-link">Terms of Service</a> and <a href="#"
                                class="terms-link">Privacy Policy</a>
                        </label>
                        <span class="error-message" id="termsError"></span>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-container">
                            <input type="checkbox" id="newsletter" name="newsletter">
                            <span class="checkmark"></span>
                            Subscribe to our newsletter for healthy tips and recipes
                        </label>
                    </div>

                    <div class="h-captcha" data-sitekey="c5bca084-e8b0-45cc-afc2-b42e11e2e1c4"></div>


                    <button type="submit" class="register-btn"><span>Create Account</span></button>

                    <div class="login-link">
                        <p>Already have an account? <a href="login.php">Sign in here</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>