<?php
session_start();
require 'db_connect.php';

if (empty($_SESSION['2fa_user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = (int) $_SESSION['2fa_user_id'];
$method = $_GET['method'] ?? 'email';
$error = '';

$userStmt = $conn->prepare(
    "SELECT u.id, u.username, u.email, u.twofa_method, t.totp_secret 
     FROM users u
     LEFT JOIN user_totp t ON u.id = t.user_id
     WHERE u.id = ?"
);
$userStmt->bind_param('i', $userId);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();
$userStmt->close();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');

    if ($method === 'email') {
        $otpStmt = $conn->prepare(
            "SELECT id, otp_hash FROM email_otps
             WHERE user_id = ?
               AND purpose  = '2fa'
               AND used_at  IS NULL
               AND expires_at > NOW()
             ORDER BY created_at DESC
             LIMIT 1"
        );
        $otpStmt->bind_param('i', $userId);
        $otpStmt->execute();
        $otpRow = $otpStmt->get_result()->fetch_assoc();
        $otpStmt->close();

        if ($otpRow && password_verify($code, $otpRow['otp_hash'])) {
            $upd = $conn->prepare("UPDATE email_otps SET used_at = NOW() WHERE id = ?");
            $upd->bind_param('i', $otpRow['id']);
            $upd->execute();
            $upd->close();

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            unset($_SESSION['2fa_user_id']);
            header("Location: profile.php");
            exit();
        } else {
            $error = "Invalid or expired code. Please try again.";
        }

    } elseif ($method === 'totp') {
        require_once 'vendor/autoload.php';
        $google2fa = new \PragmaRX\Google2FA\Google2FA();

        if ($google2fa->verifyKey($user['totp_secret'], $code)) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            unset($_SESSION['2fa_user_id']);
            header("Location: profile.php");
            exit();
        } else {
            $error = "Invalid authenticator code. Please try again.";
        }
    }
}

if ($method === 'email' && isset($_GET['resend'])) {
    require_once 'send_otp_email.php';
    try {
        sendOtpEmail($conn, $userId, $user['email'], $user['username']);
        $resent = true;
    } catch (Exception $e) {
        $error = "Could not resend code. Please go back and try logging in again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Identity – Healthy Food</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="page-wrapper">
        <div class="illustration-section">
            <div class="illustration-content">
                <h2>Almost there!</h2>
                <p>Confirm your identity to continue</p>
            </div>
        </div>

        <div class="form-section">
            <div class="login-card">
                <div class="card-header">
                    <?php if ($method === 'email'): ?>
                        <h1>📧 Email Verification</h1>
                        <p>We sent a 6-digit code to <strong>
                                <?php echo htmlspecialchars($user['email']); ?>
                            </strong></p>
                    <?php else: ?>
                        <h1>🔐 Authenticator Code</h1>
                        <p>Open your authenticator app and enter the current code</p>
                    <?php endif; ?>
                </div>

                <?php if (!empty($resent)): ?>
                    <span class="server-success" style="color:#28a745;display:block;text-align:center;margin-bottom:12px;">
                        A new code has been sent to your email.
                    </span>
                <?php endif; ?>

                <form class="login-form" method="POST"
                    action="verify_2fa.php?method=<?php echo htmlspecialchars($method); ?>">

                    <div class="form-group">
                        <label for="code">Verification Code</label>
                        <input type="text" id="code" name="code" class="otp-input" inputmode="numeric" pattern="\d{6}"
                            maxlength="6" autocomplete="one-time-code" required autofocus>
                    </div>

                    <?php if ($error): ?>
                        <span class="server-error"
                            style="color:#ff4d4d;display:block;text-align:center;margin-bottom:10px;">
                            <?php echo htmlspecialchars($error); ?>
                        </span>
                    <?php endif; ?>

                    <button type="submit" class="login-btn"><span>Verify</span></button>
                </form>

                <div style="text-align:center;margin-top:16px;font-size:.9rem;color:#94a3b8">
                    <?php if ($method === 'email'): ?>
                        Didn't receive it?
                        <a href="verify_2fa.php?method=email&resend=1" style="color:#6366f1">Resend code</a>
                        &nbsp;·&nbsp;
                    <?php endif; ?>
                    <a href="login.php" style="color:#6366f1">Back to login</a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>