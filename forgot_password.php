<?php
require 'db_connect.php';
require_once 'send_otp_email.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $error = "Please enter your email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            try {
                sendOtpEmail($conn, (int) $row['id'], $email, $row['username'], 'password_reset');
                session_start();
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_user_id'] = $row['id'];
                header("Location: reset_password.php");
                exit();
            } catch (Exception $e) {
                $error = "Failed to send reset email. Please try again.";
            }
        } else {
            // For security, don't reveal if the email exists or not
            $error = "If that email matches an account, you'll receive a code shortly.";
            // But actually we want to be helpful here if it's a private app or the user prefers it.
            // Let's stick to the secure way or a slightly more helpful one.
            $success = "If that email is in our system, we've sent a 6-digit code.";
            $error = '';
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
    <title>Forgot Password - Healthy Food</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="page-wrapper">
        <div class="illustration-section">
            <div class="illustration-content">
                <h2>Forgot Password?</h2>
                <p>No worries, it happens to the best of us.</p>
            </div>
        </div>
        <div class="form-section">
            <div class="login-card">
                <div class="card-header">
                    <h1>Reset Password</h1>
                    <p>Enter your email to receive a reset code</p>
                </div>

                <form class="login-form" method="POST" action="forgot_password.php">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required autofocus>
                    </div>

                    <?php if ($success): ?>
                        <span class="server-success"
                            style="color:#28a745;text-align:center;display:block;margin-bottom:15px;">
                            <?php echo $success; ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <span class="server-error"
                            style="color:#ff4d4d;text-align:center;display:block;margin-bottom:15px;">
                            <?php echo htmlspecialchars($error); ?>
                        </span>
                    <?php endif; ?>

                    <button type="submit" class="login-btn"><span>Send Reset Code</span></button>

                    <div class="signup-link">
                        <p><a href="login.php">Back to Sign In</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>