<?php
session_start();
require 'db_connect.php';
require 'RateLimiter.php';

$error = '';
$success = '';
$username = '';

if (!check_rate_limit($_SERVER['REMOTE_ADDR'])) {
    http_response_code(429);
    exit("Too many login attempts. Please try again later.");
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

if (isset($_GET['registered']) && $_GET['registered'] == 1) {
    $success = "Registration successful! Please sign in.";
}

if (isset($_GET['reset']) && $_GET['reset'] == 1) {
    $success = "Password successfully reset! Please sign in.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare(
            "SELECT id, username, email, password, twofa_method
             FROM users WHERE username = ?"
        );
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            if (password_verify($password, $row['password'])) {

                // ── 2FA check ──────────────────────────────────────────────
                if ($row['twofa_method'] === 'email') {
                    // Generate & email OTP
                    require_once 'send_otp_email.php';
                    try {
                        sendOtpEmail($conn, (int) $row['id'], $row['email'], $row['username']);
                        $_SESSION['2fa_user_id'] = $row['id'];
                        header("Location: verify_2fa.php?method=email");
                        exit();
                    } catch (Exception $e) {
                        $error = "Failed to send OTP email. Please try again.";
                    }

                } elseif ($row['twofa_method'] === 'totp') {
                    $_SESSION['2fa_user_id'] = $row['id'];
                    header("Location: verify_2fa.php?method=totp");
                    exit();

                } else {
                    // No 2FA – log in directly
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['username'] = $row['username'];
                    header("Location: profile.php");
                    exit();
                }
                // ──────────────────────────────────────────────────────────

            } else {
                $error = "Invalid password.";

                // Track failure for this username
                $failCount = record_login_failure($username);
                if ($failCount === 25) {
                    require_once 'send_otp_email.php';
                    sendSecurityAlertEmail($row['email'], $row['username']);
                }
            }
        } else {
            $error = "User not found.";
            // Note: We don't have an email to send to if user doesn't exist,
            // but we could still record the failure if we want to track attempts against non-existent users.
            record_login_failure($username);
        }
        $stmt->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Healthy Food</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .server-error {
            color: #ff4d4d;
            font-size: 0.9rem;
            margin-top: 10px;
            text-align: center;
            display: block;
        }

        .server-success {
            color: #28a745;
            font-size: 0.9rem;
            margin-top: 10px;
            text-align: center;
            display: block;
        }
    </style>
</head>

<body>
    <div class="page-wrapper">
        <div class="illustration-section">
            <div class="illustration-content">
                <h2>Welcome Back!</h2>
                <p>Sign in to continue your healthy journey</p>
            </div>
        </div>
        <div class="form-section">
            <div class="login-card">
                <div class="card-header">
                    <h1>Sign In</h1>
                    <p>Sign in to your account</p>
                </div>

                <form class="login-form" method="POST" action="login.php">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username"
                            value="<?php echo htmlspecialchars($username); ?>" required>
                        <span class="error-message" id="usernameError"></span>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                        <span class="error-message" id="passwordError"></span>
                    </div>

                    <?php if ($success): ?>
                        <span class="server-success"><?php echo $success; ?></span>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <span class="server-error"><?php echo htmlspecialchars($error); ?></span>
                    <?php endif; ?>

                    <div class="form-options">
                        <label class="checkbox-container">
                            <input type="checkbox" id="rememberMe" name="rememberMe">
                            <span class="checkmark"></span>
                            Remember me
                        </label>
                        <a href="forgot_password.php" class="forgot-password">Forgot password?</a>
                    </div>

                    <button type="submit" class="login-btn"><span>Sign In</span></button>

                    <div class="signup-link">
                        <p>Don't have an account? <a href="register.php">Sign up here</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>