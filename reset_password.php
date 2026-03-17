<?php
require_once __DIR__ . '/config.php';

if (empty($_SESSION['reset_email']) || empty($_SESSION['reset_user_id'])) {
    header("Location: forgot_password.php");
    exit();
}

$email = $_SESSION['reset_email'];
$userId = (int) $_SESSION['reset_user_id'];
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = trim($_POST['code'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($code) || empty($newPassword) || empty($confirmPassword)) {
        $error = "Please fill in all fields.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "Passwords do not match.";
    } elseif (strlen($newPassword) < 8) {
        $error = "Password must be at least 8 characters.";
    } else {
        // Verify OTP
        $stmt = $conn->prepare(
            "SELECT id, otp_hash FROM email_otps 
             WHERE user_id = ? AND purpose = 'password_reset' AND used_at IS NULL AND expires_at > NOW()
             ORDER BY created_at DESC LIMIT 1"
        );
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($code, $row['otp_hash'])) {
                // Update Password
                $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                $upd = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $upd->bind_param("si", $hashedPassword, $userId);

                if ($upd->execute()) {
                    // Mark OTP as used
                    $otpId = $row['id'];
                    $conn->query("UPDATE email_otps SET used_at = NOW() WHERE id = $otpId");

                    // Clear session and redirect
                    session_destroy();
                    header("Location: login.php?reset=1");
                    exit();
                } else {
                    $error = "Failed to update password. Please try again.";
                }
                $upd->close();
            } else {
                $error = "Invalid or expired code.";
            }
        } else {
            $error = "Invalid or expired code.";
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
    <title>Reset Password - Healthy Food</title>
    <link rel="stylesheet" href="/app/assets/css/styles.css">
</head>

<body>
    <div class="page-wrapper">
        <div class="illustration-section">
            <div class="illustration-content">
                <h2>New Password</h2>
                <p>Verify your code and choose a strong password.</p>
            </div>
        </div>
        <div class="form-section">
            <div class="login-card">
                <div class="card-header">
                    <h1>Set New Password</h1>
                    <p>Code sent to: <strong>
                            <?php echo htmlspecialchars($email); ?>
                        </strong></p>
                </div>

                <form class="login-form" method="POST" action="reset_password.php">
                    <div class="form-group">
                        <label for="code">6-Digit Code</label>
                        <input type="text" id="code" name="code" maxlength="6" pattern="\d{6}" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" minlength="8" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" minlength="8" required>
                    </div>

                    <?php if ($error): ?>
                        <span class="server-error"
                            style="color:#ff4d4d;text-align:center;display:block;margin-bottom:15px;">
                            <?php echo htmlspecialchars($error); ?>
                        </span>
                    <?php endif; ?>

                    <button type="submit" class="login-btn"><span>Reset Password</span></button>

                    <div class="signup-link">
                        <p><a href="forgot_password.php">Didn't get a code?</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>